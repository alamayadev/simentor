<?php

namespace App\Http\Controllers\Api\Adhock;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Models\SlsSipw;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Carbon;

class SlsSipwApiController extends BaseApiController
{
    /**
     * Mengembalikan daftar data SLS SIPW dengan filter dan pagination.
     * Response menggunakan standard BaseApiController format dengan automatic pagination.
     * Diperbaiki untuk menghilangkan double data wrapper issue.
     *
     * @group IPDS - Pengolahan Wilkerstat
     * @unauthenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[nmprov] string Filter berdasarkan nama provinsi. Contoh: Jawa Barat
     * @queryParam filter[nmkab] string Filter berdasarkan nama kabupaten. Contoh: Bandung
     * @queryParam filter[nmkec] string Filter berdasarkan nama kecamatan. Contoh: Coblong
     * @queryParam filter[nmdesa] string Filter berdasarkan nama desa. Contoh: Sukajadi
     * @queryParam filter[nama_sls] string Filter berdasarkan nama SLS. Contoh: SLS 01
     * @queryParam filter[operator] string Filter berdasarkan operator. Contoh: Op1
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Columns returned to the client (mirror the database structure)
        $selectColumns = [
            'id', 'idfrs', 'idsls', 'kdprov', 'kdkab', 'kdkec', 'kddesa', 'kdsls',
            'klas', 'nmprov', 'nmkab', 'nmkec', 'nmdesa', 'nama_sls', 'jenis_sls',
            'ketua_sls', 'j_subsls', 'muatan_dominan', 'flag_perubahan_sls',
            'status_olah_peta', 'shapes_comparation', 'location', 'status_sls',
            'peta_banding_rs', 'operator', 'created_at', 'updated_at'
        ];
        // Build QueryBuilder and allow operator as a filter. Operator matching is
        // implemented as a case-insensitive partial match against the stored
        // JSON payload in alokasi.alokasi_geojson. This keeps operator as a
        // first-class filter while allowing composition with other filters.
        $builder = QueryBuilder::for(SlsSipw::class)
            ->allowedFilters([
                AllowedFilter::partial('operator'),
                AllowedFilter::exact('kdprov'),
                AllowedFilter::exact('kdkab'),
                AllowedFilter::exact('kdkec'),
                AllowedFilter::exact('kddesa'),
                AllowedFilter::exact('idsls'),
                AllowedFilter::partial('nama_sls'),
                AllowedFilter::exact('status_olah_peta'),
                AllowedFilter::exact('peta_banding_rs'),
                AllowedFilter::exact('flag_perubahan_sls'),
            ])
            ->select($selectColumns);

        // Sorting and pagination
        $sortBy = $request->input('sort_by', 'id');
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage = (int) $request->input('per_page', 50);

        if (! in_array($sortBy, $selectColumns, true)) {
            $sortBy = 'id';
        }

        // Clone builder to compute operators and kecamatan/desa lists without pagination
        $cloneForLists = clone $builder;

        // If kdkec filter is present, compute desa list for that kdkec; otherwise desa remains null
        $kdkecFilter = $request->input('filter.kdkec') ?? $request->input('kdkec');

        // Build kecamatan list (unique kdkec / nmkec from entire table)
        // Return a short object sorted by kdkec (value) ascending
        $kecamatans = SlsSipw::select('kdkec', 'nmkec')
            ->distinct()
            ->orderBy('kdkec')
            ->get()
            ->map(function ($r) {
                return [
                    'value' => $r->kdkec,
                    'label' => $r->nmkec,
                ];
            })->values();

        $desas = null;
        if ($kdkecFilter) {
            $desas = SlsSipw::where('kdkec', $kdkecFilter)
                ->select('kddesa', 'nmdesa')
                ->distinct()
                ->orderBy('nmdesa')
                ->get()
                ->map(function ($r) {
                    return [
                        'value' => $r->kddesa,
                        'label' => $r->nmdesa,
                    ];
                })->values();
        }

        // Operators: gather distinct operator values from the filtered SlsSipw set
        $operators = (clone $cloneForLists)
            ->select('operator')
            ->whereNotNull('operator')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator')
            ->map(function ($v) {
                return trim($v);
            })
            ->filter()
            ->values();

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $builder->count();

        // Final paginated results
        $results = $builder->orderBy($sortBy, $sortDir)->fastPaginate($perPage);

        // Read cached statistics (if any).
        $statistics = Cache::get('sls_sipw:statistics');

        $needsRecompute = false;
        if (! is_array($statistics) || empty($statistics['computed_at'])) {
            $needsRecompute = true;
        } else {
            try {
                $computed = Carbon::parse($statistics['computed_at']);
                // If cached value is older than 6 hours, recompute synchronously
                if ($computed->addHours(6)->lessThanOrEqualTo(Carbon::now())) {
                    $needsRecompute = true;
                }
            } catch (\Exception $e) {
                $needsRecompute = true;
            }
        }

        if ($needsRecompute) {
            // Wait up to 10 seconds to acquire the lock to avoid thundering herd.
            // If we get the lock, recompute and store; otherwise try to read the updated cache.
            try {
                $lock = Cache::lock('sls_sipw:statistics:lock', 30);
                $lock->block(10, function () use (&$statistics) {
                    $statistics = [
                        'target' => SlsSipw::whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_ok' => SlsSipw::where('status_olah_peta', 'SUDAH')->whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_tidak_match' => SlsSipw::where('peta_banding_rs', 'TIDAK_MATCH')->whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_belum_olah' => SlsSipw::where(function ($q) {
                            $q->whereNull('status_olah_peta')
                                ->orWhere('status_olah_peta', '!=', 'SUDAH');
                        })->whereNotNull('idsls')->distinct()->count('idsls'),
                        'computed_at' => Carbon::now()->toDateTimeString(),
                    ];
                    Cache::put('sls_sipw:statistics', $statistics, now()->addHours(7));
                });
                // After block returns, attempt reading cache to ensure we have the value
                $statistics = Cache::get('sls_sipw:statistics');
                if (! is_array($statistics)) {
                    // Last-resort compute if cache still missing
                    $statistics = [
                        'target' => SlsSipw::whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_ok' => SlsSipw::where('status_olah_peta', 'SUDAH')->whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_tidak_match' => SlsSipw::where('peta_banding_rs', 'TIDAK_MATCH')->whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_belum_olah' => SlsSipw::where(function ($q) {
                            $q->whereNull('status_olah_peta')
                                ->orWhere('status_olah_peta', '!=', 'SUDAH');
                        })->whereNotNull('idsls')->distinct()->count('idsls'),
                        'computed_at' => Carbon::now()->toDateTimeString(),
                    ];
                }
            } catch (\Throwable $e) {
                // If lock acquisition failed or other errors occurred, try to read cache and fall back to local compute.
                $statistics = Cache::get('sls_sipw:statistics');
                if (! is_array($statistics)) {
                    $statistics = [
                        'target' => SlsSipw::whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_ok' => SlsSipw::where('status_olah_peta', 'SUDAH')->whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_tidak_match' => SlsSipw::where('peta_banding_rs', 'TIDAK_MATCH')->whereNotNull('idsls')->distinct()->count('idsls'),
                        'progres_belum_olah' => SlsSipw::where(function ($q) {
                            $q->whereNull('status_olah_peta')
                                ->orWhere('status_olah_peta', '!=', 'SUDAH');
                        })->whereNotNull('idsls')->distinct()->count('idsls'),
                        'computed_at' => Carbon::now()->toDateTimeString(),
                    ];
                }
            }
        }

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        
        return $this->success($results, 'Data retrieved successfully', 200, [
            'kecamatan' => $kecamatans,
            'desa' => $desas,
            'operators' => $operators,
            'statistics' => $statistics,
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ],
        ]);
    }

    /**
     * Force recompute statistics (development/local only).
     */
    public function computeStatistics(Request $request)
    {
        if (! app()->environment('local')) {
            return $this->error('Forbidden', null, 403);
        }

        // Acquire lock and recompute
        $lock = Cache::lock('sls_sipw:statistics:lock', 30);
        if ($lock->get()) {
            try {
                $statistics = [
                    'target' => SlsSipw::whereNotNull('idsls')->distinct()->count('idsls'),
                    'progres_ok' => SlsSipw::where('status_olah_peta', 'SUDAH')->whereNotNull('idsls')->distinct()->count('idsls'),
                    'progres_tidak_match' => SlsSipw::where('peta_banding_rs', 'TIDAK_MATCH')->whereNotNull('idsls')->distinct()->count('idsls'),
                    'progres_belum_olah' => SlsSipw::where(function ($q) {
                        $q->whereNull('status_olah_peta')
                            ->orWhere('status_olah_peta', '!=', 'SUDAH');
                    })->whereNotNull('idsls')->distinct()->count('idsls'),
                    'computed_at' => Carbon::now()->toDateTimeString(),
                ];
                Cache::put('sls_sipw:statistics', $statistics, now()->addHours(7));
                return $this->success($statistics);
            } finally {
                $lock->release();
            }
        }

        return $this->error('Another process is recomputing. Try again shortly.', null, 423);
    }

    /**
     * Return list of kecamatan (unique kdkec/nmkec)
     */
    public function kecamatan(Request $request)
    {
        // Return a short object sorted by kdkec (value) ascending
        $list = SlsSipw::select('kdkec', 'nmkec')
            ->distinct()
            ->orderBy('kdkec')
            ->get()
            ->map(function ($r) {
                return ['value' => $r->kdkec, 'label' => $r->nmkec];
            })->values();

        return $this->success($list);
    }

    /**
     * Return list of desa for a given kdkec
     */
    public function desa(Request $request)
    {
        $kdkec = $request->input('kdkec');
        if (! $kdkec) {
            return $this->success([]);
        }

        $list = SlsSipw::where('kdkec', $kdkec)
            ->select('kddesa', 'nmdesa')
            ->distinct()
            ->orderBy('nmdesa')
            ->get()
            ->map(function ($r) {
                return ['value' => $r->kddesa, 'label' => $r->nmdesa];
            })->values();

        return $this->success($list);
    }

    /**
     * Return unique operators from related Alokasi JSON for the filtered SlsSipw set.
     * Optimizes using DB JSON functions when available, otherwise falls back to PHP parsing.
     */
    public function operators(Request $request)
    {
        // Allow filtering by kdkec/kddesa to narrow the operator list
        $query = SlsSipw::query();
        if ($request->filled('kdkec')) {
            $query->where('kdkec', $request->input('kdkec'));
        }
        if ($request->filled('kddesa')) {
            $query->where('kddesa', $request->input('kddesa'));
        }

        $operators = $query
            ->whereNotNull('operator')
            ->select('operator')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator')
            ->map(static function ($value) {
                return trim($value);
            })
            ->filter()
            ->values();

        return $this->success($operators);
    }
}
