<?php

namespace App\Http\Controllers\Api\Adhock;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\PetaSls;
use App\Models\TargetPeta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class PetaSlsApiController extends BaseApiController
{
    /**
     * Mengembalikan daftar data Peta SLS dengan filter dan pagination.
     * Response menggunakan standard BaseApiController format dengan automatic pagination.
     * Diperbaiki untuk menghilangkan double data wrapper issue.
     *
     * @group IPDS - Pengolahan Wilkerstat
     * @unauthenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[kdkec] string Filter berdasarkan kode kecamatan. Contoh: 3201
     * @queryParam filter[kddesa] string Filter berdasarkan kode desa. Contoh: 3201010001
     * @queryParam filter[nmkec] string Filter berdasarkan nama kecamatan. Contoh: Bandung
     * @queryParam filter[nmdesa] string Filter berdasarkan nama desa. Contoh: Sukajadi
     * @queryParam filter[operator] string Filter berdasarkan operator. Contoh: Op1
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $selectColumns = [
            'id',
            'kdkec',
            'kddesa',
            'nmkec',
            'nmdesa',
            'filename',
            'operator',
            'jml',
            'created_at',
            'updated_at',
        ];

        $perPage = (int) $request->input('per_page', 50);
        if ($perPage <= 0) {
            $perPage = 50;
        }
        $perPage = min($perPage, 200);

        $allowedSorts = ['nmkec', 'nmdesa', 'operator', 'jml', 'kdkec', 'kddesa', 'filename', 'created_at', 'updated_at', 'id'];
        $sortBy = $request->input('sort_by', 'filename');
        if (! in_array($sortBy, $allowedSorts, true)) {
            $sortBy = 'filename';
        }
        $sortDir = strtolower($request->input('sort_dir', 'asc')) === 'desc' ? 'desc' : 'asc';

        $builder = QueryBuilder::for(PetaSls::with('targetPeta'))
            ->allowedFilters([
                AllowedFilter::exact('kdkec'),
                AllowedFilter::exact('kddesa'),
                AllowedFilter::partial('nmkec'),
                AllowedFilter::partial('nmdesa'),
                AllowedFilter::partial('operator'),
                AllowedFilter::partial('filename'),
            ])
            ->select($selectColumns);

        $search = trim((string) $request->input('q', ''));
        if ($search !== '') {
            $escaped = $this->escapeLike($search);
            $builder->where(function ($query) use ($escaped) {
                $like = '%' . $escaped . '%';
                $query->where('nmkec', 'like', $like)
                    ->orWhere('nmdesa', 'like', $like)
                    ->orWhere('operator', 'like', $like)
                    ->orWhere('filename', 'like', $like);
            });
        }

        $cloneForStats = clone $builder;

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $builder->count();

        $results = $builder
            ->orderBy($sortBy, $sortDir)
            ->fastPaginate($perPage);

        // Add targetPeta keterangan to each result
        $results->getCollection()->transform(function ($item) {
            $item->keterangan = $item->targetPeta ? $item->targetPeta->keterangan : null;
            unset($item->targetPeta);
            return $item;
        });

        $summaryQuery = clone $cloneForStats;
        $totalRows = (clone $summaryQuery)->count();
        $totalJml = (int) ((clone $summaryQuery)->sum('jml') ?? 0);

        $operatorExpression = $this->operatorLabelExpression();

        $operatorBreakdown = (clone $cloneForStats)
            ->selectRaw("{$operatorExpression} as operator_label, COUNT(*) as total_rows, SUM(COALESCE(jml, 0)) as total_jml")
            ->groupBy(DB::raw($operatorExpression))
            ->orderBy('operator_label')
            ->get()
            ->map(function ($row) {
                $label = $row->operator_label;
                $operatorValue = $label === '(Tanpa Operator)' ? null : $label;

                return [
                    'operator' => $operatorValue,
                    'display' => $label,
                    'total_rows' => (int) $row->total_rows,
                    'total_jml' => (int) $row->total_jml,
                ];
            })
            ->values();

        $operators = $operatorBreakdown
            ->pluck('operator')
            ->filter()
            ->unique()
            ->values();

        $kecamatans = PetaSls::select('kdkec', 'nmkec')
            ->whereNotNull('kdkec')
            ->whereNotNull('nmkec')
            ->distinct()
            ->orderBy('kdkec')
            ->get()
            ->map(function ($row) {
                return [
                    'value' => $row->kdkec,
                    'label' => $row->nmkec,
                ];
            })
            ->values();

        $kdkecFilter = $request->input('filter.kdkec') ?? $request->input('kdkec');

        $desas = null;
        if ($kdkecFilter) {
            $desas = PetaSls::where('kdkec', $kdkecFilter)
                ->select('kddesa', 'nmdesa')
                ->whereNotNull('kddesa')
                ->whereNotNull('nmdesa')
                ->distinct()
                ->orderBy('nmdesa')
                ->get()
                ->map(function ($row) {
                    return [
                        'value' => $row->kddesa,
                        'label' => $row->nmdesa,
                    ];
                })
                ->values();
        }

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        
        return $this->success($results, 'Data retrieved successfully', 200, [
            'summary' => [
                'total_rows' => $totalRows,
                'total_jml' => $totalJml,
            ],
            'operators' => $operators,
            'operator_breakdown' => $operatorBreakdown,
            'kecamatan' => $kecamatans,
            'desa' => $desas,
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ],
        ]);
    }

    public function kecamatan()
    {
        $list = PetaSls::select('kdkec', 'nmkec')
            ->whereNotNull('kdkec')
            ->whereNotNull('nmkec')
            ->distinct()
            ->orderBy('kdkec')
            ->get()
            ->map(function ($row) {
                return [
                    'value' => $row->kdkec,
                    'label' => $row->nmkec,
                ];
            })
            ->values();

        return $this->success($list);
    }

    public function desa(Request $request)
    {
        $kdkec = $request->input('kdkec');
        if (! $kdkec) {
            return $this->success([]);
        }

        $list = PetaSls::where('kdkec', $kdkec)
            ->select('kddesa', 'nmdesa')
            ->whereNotNull('kddesa')
            ->whereNotNull('nmdesa')
            ->distinct()
            ->orderBy('nmdesa')
            ->get()
            ->map(function ($row) {
                return [
                    'value' => $row->kddesa,
                    'label' => $row->nmdesa,
                ];
            })
            ->values();

        return $this->success($list);
    }

    public function operators(Request $request)
    {
        $query = PetaSls::query();

        if ($request->filled('kdkec')) {
            $query->where('kdkec', $request->input('kdkec'));
        }

        if ($request->filled('kddesa')) {
            $query->where('kddesa', $request->input('kddesa'));
        }

        $withCounts = filter_var($request->input('with_counts'), FILTER_VALIDATE_BOOLEAN);

        if ($withCounts) {
            $expression = $this->operatorLabelExpression();

            $rows = $query
                ->selectRaw("{$expression} as operator_label, COUNT(*) as total_rows, SUM(COALESCE(jml, 0)) as total_jml")
                ->groupBy(DB::raw($expression))
                ->orderBy('operator_label')
                ->get()
                ->map(function ($row) {
                    $label = $row->operator_label;
                    $operatorValue = $label === '(Tanpa Operator)' ? null : $label;

                    return [
                        'operator' => $operatorValue,
                        'display' => $label,
                        'total_rows' => (int) $row->total_rows,
                        'total_jml' => (int) $row->total_jml,
                    ];
                })
                ->values();

            return $this->success($rows);
        }

        $operators = $query
            ->whereNotNull('operator')
            ->select('operator')
            ->distinct()
            ->orderBy('operator')
            ->pluck('operator')
            ->map(function ($value) {
                return trim($value);
            })
            ->filter()
            ->unique()
            ->values();

        return $this->success($operators);
    }

    public function update(Request $request, PetaSls $petaSls)
    {
        $data = $request->validate([
            'keterangan' => ['present', 'nullable', 'string', 'max:1000'],
        ]);

        $value = $data['keterangan'];
        if (is_string($value)) {
            $value = trim($value);
        }

        if ($value === '') {
            $value = null;
        }

        // Update or create the TargetPeta record with the keterangan
        $targetPeta = TargetPeta::updateOrCreate(
            ['filename' => $petaSls->filename],
            ['keterangan' => $value]
        );

        return $this->success(array_merge($petaSls->toArray(), ['keterangan' => $targetPeta->keterangan]), 'Keterangan updated');
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function operatorLabelExpression(): string
    {
        return "COALESCE(NULLIF(TRIM(operator), ''), '(Tanpa Operator)')";
    }
}
