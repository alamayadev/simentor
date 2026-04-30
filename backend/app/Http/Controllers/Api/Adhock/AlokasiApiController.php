<?php

namespace App\Http\Controllers\Api\Adhock;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Alokasi;
use App\Models\CekScan;
use App\Models\CekGeoref;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class AlokasiApiController extends BaseApiController
{

    /**
     * List alokasi records (paginated) with optional free-text `q` search and Spatie filters.
     * Also returns operator names and progress counts.
     * Response follows BaseApiController format with automatic pagination metadata.
     * Fixed to prevent double data wrapper issue.
     *
     * @group Alokasi
     *
     * @queryParam per_page int Items per page. Example: 20
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam q string Search term. Example: sls
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data fetched successfully",
     *   "data": [
     *     {
     *       "idsls": "1234",
     *       "nmkec": "Kecamatan A",
     *       "nmdesa": "Desa B",
     *       "nmsls": "SLS C"
     *     }
     *   ],
     *   "pagination": {
     *     "total": 100,
     *     "per_page": 20,
     *     "current_page": 1,
     *     "last_page": 5
     *   },
     *   "operator_names": ["Op1", "Op2"],
     *   "progressScan": 10,
     *   "progressGeoref": 5,
     *   "target": 100
     * }
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->query('per_page', 20);

        $term = trim((string) $request->query('q', ''));

        $query = QueryBuilder::for(Alokasi::class)
            ->allowedFilters([
                AllowedFilter::partial('alokasi_scan'),
                AllowedFilter::partial('alokasi_georef'),
                AllowedFilter::partial('nmkec'),
                AllowedFilter::partial('nmdesa'),
                AllowedFilter::partial('nmsls'),
                AllowedFilter::partial('kdkec'),
                AllowedFilter::partial('kddesa'),
            ])
            ->allowedSorts(['kdkec', 'kddesa', 'kdsls', 'nmkec', 'nmdesa', 'nmsls']);

        // Apply simple free-text search across selected columns when q is present
        if ($term !== '') {
            $query->where(function ($q) use ($term) {
                $like = "%{$term}%";
                $q->where('idsls', 'like', $like)
                    ->orWhere('kdkec', 'like', $like)
                    ->orWhere('nmdesa', 'like', $like);
            });
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $query->count();

        $paginator = $query->fastPaginate($perPage);

        // operator names (unique alokasi_scan values considering the same allowed filter key)
        $operatorNames = QueryBuilder::for(Alokasi::class)
            ->allowedFilters([
                AllowedFilter::partial('alokasi_scan'),
            ])
            ->whereNotNull('alokasi_scan')
            ->distinct()
            ->orderBy('alokasi_scan')
            ->pluck('alokasi_scan')
            ->values();

        // PERFORMANCE OPTIMIZATION: Use database-level distinct count instead of pulling all records into memory
        // progress counts
        $progressScan = CekScan::where('jenis', 'WS')
            ->whereNotNull('kode')
            ->where('kode', '!=', '')
            ->distinct()
            ->count('kode');

        $progressGeoref = CekGeoref::where('jenis', 'WS')
            ->whereNotNull('kode')
            ->where('kode', '!=', '')
            ->distinct()
            ->count('kode');

        $target = Alokasi::select('idsls')->distinct()->whereNotNull('idsls')->count();

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;

        return $this->success($paginator, 'Data fetched successfully', 200, [
            'operator_names' => $operatorNames,
            'progressScan' => $progressScan,
            'progressGeoref' => $progressGeoref,
            'target' => $target,
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ],
        ]);
    }

    /**
     * Mengembalikan daftar baris alokasi beserta nilai cekScan.kode terkait
     * (menggunakan 'Belum Scan' bila nilai null).
     * Response menggunakan standard format dengan automatic pagination.
     * Diperbaiki untuk menghilangkan double data wrapper issue.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function progressScan(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);

        // Determine if caller wants CSV export for the whole matching dataset.
        $accept = $request->header('Accept', '');
        $isCsvAccept = is_string($accept) && stripos($accept, 'text/csv') !== false;
        $perPageAll = strtolower($request->query('per_page', '')) === 'all';
        $exportAll = $request->query('export_all') === 'true';

    $wantCsv = $isCsvAccept || $perPageAll || $exportAll;

        // Use Spatie QueryBuilder to allow filtering by alokasi_scan via filter[alokasi_scan]
        // also allow sorting by related cek_scans.kode (aliased as kode)
        $query = QueryBuilder::for(Alokasi::class)
            ->allowedFilters([
                AllowedFilter::partial('alokasi_scan'),
                // Permit filtering by locality fields used by UI/tests
                AllowedFilter::partial('nmkec'),
                AllowedFilter::partial('nmdesa'),
                AllowedFilter::partial('nmsls'),
                AllowedFilter::partial('kdkec'),
                AllowedFilter::partial('kddesa'),
            ])
            ->select([
                'alokasis.idsls',
                'alokasis.kdkec',
                'alokasis.kddesa',
                'alokasis.kdsls',
                'alokasis.nmkec',
                'alokasis.nmdesa',
                'alokasis.nmsls',
                'alokasis.iddesa',
                'alokasis.alokasi_scan',
                // include related kode for sorting
                'cek_scans.kode as kode',
            ])
            ->leftJoin('cek_scans', 'alokasis.idsls', '=', 'cek_scans.kode')
            ->with(['cekScan' => function ($q) {
                $q->select('id', 'kode');
            }])
            ->allowedSorts([
                'kdkec', 'kddesa', 'kdsls', 'nmkec', 'nmdesa', 'nmsls', 'alokasi_scan', 'kode'
            ]);

        // If the client asked for CSV export, ignore pagination and stream the full result set.
        if ($wantCsv) {
            // Only allow full exports when explicit export_all/per_page=all is set or when
            // the request includes at least one filter or a q search term. This prevents
            // accidental full-dataset downloads.
            // Use the parsed query parameters as the canonical source (this handles
            // bracketed params like filter[nmkec]=... in the test environment). Exclude
            // pagination and explicit export flags from the filter check.
            $parsedQuery = $request->query();
            $ignoredKeys = ['page', 'per_page', 'export_all'];
            $nonEmptyParams = array_filter($parsedQuery, function ($v, $k) use ($ignoredKeys) {
                return !in_array($k, $ignoredKeys, true) && $v !== null && $v !== '';
            }, ARRAY_FILTER_USE_BOTH);

            $hasFilters = count($nonEmptyParams) > 0;

            // Fallback: if raw query string exists at all, treat it as intent to export
            if (!$hasFilters && trim((string) $request->getQueryString()) !== '') {
                $hasFilters = true;
            }

            $hasQ = !empty($request->query('q'));

            $hasQ = !empty($request->query('q'));

            if (!($exportAll || $perPageAll || $hasFilters || $hasQ)) {
                return $this->error('Export denied: must specify filters/q or export_all=true to export entire dataset', null, 400);
            }

            $results = $query->cursor();
            $filename = 'progress-scan-export-' . date('Ymd_His') . '.csv';
            $headerRow = ['idsls','kdkec','kddesa','kdsls','nmkec','nmdesa','nmsls','iddesa','alokasi_scan','kode'];

            return $this->csvResponseFromCursor($request, $results, $headerRow, $filename, function ($a) {
                $kode = null;
                if (isset($a->kode) && $a->kode !== null) {
                    $kode = trim((string) $a->kode);
                }

                if ($kode === null || $kode === '') {
                    $kode = 'Belum Scan';
                }

                return [
                    $a->idsls ?? '',
                    $a->kdkec ?? '',
                    $a->kddesa ?? '',
                    $a->kdsls ?? '',
                    $a->nmkec ?? '',
                    $a->nmdesa ?? '',
                    $a->nmsls ?? '',
                    $a->iddesa ?? '',
                    $a->alokasi_scan ?? '',
                    $kode,
                ];
            });
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $query->count();

        $paginator = $query->fastPaginate($perPage);

        // map items to include kode (or 'Belum Scan') without changing pagination meta
        $paginator->getCollection()->transform(function (Alokasi $a) {
            $kode = null;
            if ($a->cekScan && isset($a->cekScan->kode)) {
                $kode = trim((string) $a->cekScan->kode);
            }

            if ($kode === null || $kode === '') {
                $kode = 'Belum Scan';
            }

            return [
                'idsls' => $a->idsls,
                'kdkec' => $a->kdkec,
                'kddesa' => $a->kddesa,
                'kdsls' => $a->kdsls,
                'nmkec' => $a->nmkec,
                'nmdesa' => $a->nmdesa,
                'nmsls' => $a->nmsls,
                'iddesa' => $a->iddesa,
                'alokasi_scan' => $a->alokasi_scan,
                'kode' => $kode,
            ];
        });

        // operator: unique alokasi_scan values considering the same filters
        $operator = QueryBuilder::for(Alokasi::class)
            ->allowedFilters([
                AllowedFilter::partial('alokasi_scan'),
            ])
            ->whereNotNull('alokasi_scan')
            ->distinct()
            ->orderBy('alokasi_scan')
            ->pluck('alokasi_scan')
            ->values();

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        
        return $this->success($paginator, 'Data fetched successfully', 200, [
            'operator' => $operator,
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ],
        ]);
    }

    /**
     * Mengembalikan daftar baris alokasi yang dipaginasi beserta nilai cekGeoref.kode terkait
     * (menggunakan 'Belum Georef' bila nilai null).
     * Response menggunakan standard format dengan automatic pagination.
     * Diperbaiki untuk menghilangkan double data wrapper issue.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function progressGeoref(Request $request)
    {
        $perPage = (int) $request->query('per_page', 50);

        // Use Spatie QueryBuilder to allow filtering by alokasi_georef via filter[alokasi_georef]
        // also allow sorting by related cek_georefs.kode (aliased as kode)
        // Mirror progressScan allowed filters so export and filter detection behave similarly.
        $perPage = (int) $request->query('per_page', 50);

        $accept = $request->header('Accept', '');
        $isCsvAccept = is_string($accept) && stripos($accept, 'text/csv') !== false;
        $perPageAll = strtolower($request->query('per_page', '')) === 'all';
        $exportAll = $request->query('export_all') === 'true';

        $wantCsv = $isCsvAccept || $perPageAll || $exportAll;

        $query = QueryBuilder::for(Alokasi::class)
            ->allowedFilters([
                AllowedFilter::partial('alokasi_georef'),
                // Permit filtering by locality fields used by UI/tests
                AllowedFilter::partial('nmkec'),
                AllowedFilter::partial('nmdesa'),
                AllowedFilter::partial('nmsls'),
                AllowedFilter::partial('kdkec'),
                AllowedFilter::partial('kddesa'),
            ])
            ->select([
                'alokasis.idsls',
                'alokasis.kdkec',
                'alokasis.kddesa',
                'alokasis.kdsls',
                'alokasis.nmkec',
                'alokasis.nmdesa',
                'alokasis.nmsls',
                'alokasis.iddesa',
                'alokasis.alokasi_georef',
                // include related kode for sorting
                'cek_georefs.kode as kode',
            ])
            ->leftJoin('cek_georefs', 'alokasis.idsls', '=', 'cek_georefs.kode')
            ->with(['cekGeoref' => function ($q) {
                $q->select('id', 'kode');
            }])
            ->allowedSorts([
                'kdkec', 'kddesa', 'kdsls', 'nmkec', 'nmdesa', 'nmsls', 'alokasi_georef', 'kode'
            ]);

        // If CSV requested, handle streaming/in-memory and safety checks similar to progressScan
        if ($wantCsv) {
            $parsedQuery = $request->query();
            $ignoredKeys = ['page', 'per_page', 'export_all'];
            $nonEmptyParams = array_filter($parsedQuery, function ($v, $k) use ($ignoredKeys) {
                return !in_array($k, $ignoredKeys, true) && $v !== null && $v !== '';
            }, ARRAY_FILTER_USE_BOTH);

            $hasFilters = count($nonEmptyParams) > 0;
            if (!$hasFilters && trim((string) $request->getQueryString()) !== '') {
                $hasFilters = true;
            }

            $hasQ = !empty($request->query('q'));
            if (!($exportAll || $perPageAll || $hasFilters || $hasQ)) {
            if (!($exportAll || $perPageAll || $hasFilters || $hasQ)) {
                return $this->error('Export denied: must specify filters/q or export_all=true to export entire dataset', null, 400);
            }
            }

            $results = $query->cursor();
            $filename = 'progress-georef-export-' . date('Ymd_His') . '.csv';
            $headerRow = ['idsls','kdkec','kddesa','kdsls','nmkec','nmdesa','nmsls','iddesa','alokasi_georef','kode'];

            return $this->csvResponseFromCursor($request, $results, $headerRow, $filename, function ($a) {
                $kode = null;
                if (isset($a->kode) && $a->kode !== null) {
                    $kode = trim((string) $a->kode);
                }

                if ($kode === null || $kode === '') {
                    $kode = 'Belum Georef';
                }

                return [
                    $a->idsls ?? '',
                    $a->kdkec ?? '',
                    $a->kddesa ?? '',
                    $a->kdsls ?? '',
                    $a->nmkec ?? '',
                    $a->nmdesa ?? '',
                    $a->nmsls ?? '',
                    $a->iddesa ?? '',
                    $a->alokasi_georef ?? '',
                    $kode,
                ];
            });
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $query->count();

        $paginator = $query->fastPaginate($perPage);

        $paginator->getCollection()->transform(function (Alokasi $a) {
            $kode = null;
            if ($a->cekGeoref && isset($a->cekGeoref->kode)) {
                $kode = trim((string) $a->cekGeoref->kode);
            }

            if ($kode === null || $kode === '') {
                $kode = 'Belum Georef';
            }

            return [
                'idsls' => $a->idsls,
                'kdkec' => $a->kdkec,
                'kddesa' => $a->kddesa,
                'kdsls' => $a->kdsls,
                'nmkec' => $a->nmkec,
                'nmdesa' => $a->nmdesa,
                'nmsls' => $a->nmsls,
                'iddesa' => $a->iddesa,
                'alokasi_georef' => $a->alokasi_georef,
                'kode' => $kode,
            ];
        });

        // operator: unique alokasi_georef values considering the same filters
        $operator = QueryBuilder::for(Alokasi::class)
            ->allowedFilters([
                AllowedFilter::partial('alokasi_georef'),
            ])
            ->whereNotNull('alokasi_georef')
            ->distinct()
            ->orderBy('alokasi_georef')
            ->pluck('alokasi_georef')
            ->values();

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        
        return $this->success($paginator, 'Data fetched successfully', 200, [
            'operator' => $operator,
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ],
        ]);
    }

    /**
     * Dedicated CSV export endpoint for progress-scan.
     * Respects the same filters and sorting as `/alokasi/progress-scan`.
     * Requires either filters/q or explicit export_all/per_page=all to allow full exports.
     */
    public function progressScanExport(Request $request)
    {
        $perPageAll = strtolower($request->query('per_page', '')) === 'all';
        $exportAll = $request->query('export_all') === 'true';

        // Build same query as progressScan
        $query = QueryBuilder::for(Alokasi::class)
            ->allowedFilters([
                AllowedFilter::partial('alokasi_scan'),
                AllowedFilter::partial('nmkec'),
                AllowedFilter::partial('nmdesa'),
                AllowedFilter::partial('nmsls'),
                AllowedFilter::partial('kdkec'),
                AllowedFilter::partial('kddesa'),
            ])
            ->select([
                'alokasis.idsls',
                'alokasis.kdkec',
                'alokasis.kddesa',
                'alokasis.kdsls',
                'alokasis.nmkec',
                'alokasis.nmdesa',
                'alokasis.nmsls',
                'alokasis.iddesa',
                'alokasis.alokasi_scan',
                'cek_scans.kode as kode',
            ])
            ->leftJoin('cek_scans', 'alokasis.idsls', '=', 'cek_scans.kode')
            ->with(['cekScan' => function ($q) {
                $q->select('id', 'kode');
            }])
            ->allowedSorts([
                'kdkec', 'kddesa', 'kdsls', 'nmkec', 'nmdesa', 'nmsls', 'alokasi_scan', 'kode'
            ]);

        // Require explicit export flag or filters/q for full export
        $parsedQuery = $request->query();
        $ignoredKeys = ['page', 'per_page', 'export_all'];
        $nonEmptyParams = array_filter($parsedQuery, function ($v, $k) use ($ignoredKeys) {
            return !in_array($k, $ignoredKeys, true) && $v !== null && $v !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $hasFilters = count($nonEmptyParams) > 0;
        if (!$hasFilters && trim((string) $request->getQueryString()) !== '') {
            $hasFilters = true;
        }

        $hasQ = !empty($request->query('q'));
        if (!($exportAll || $perPageAll || $hasFilters || $hasQ)) {
            return $this->error('Export denied: must specify filters/q or export_all=true to export entire dataset', null, 400);
        }

        $results = $query->cursor();
        $filename = 'progress-scan-export-' . date('Ymd_His') . '.csv';
        $headerRow = ['idsls','kdkec','kddesa','kdsls','nmkec','nmdesa','nmsls','iddesa','alokasi_scan','kode'];

        return $this->csvResponseFromCursor($request, $results, $headerRow, $filename, function ($a) {
            $kode = null;
            if (isset($a->kode) && $a->kode !== null) {
                $kode = trim((string) $a->kode);
            }

            if ($kode === null || $kode === '') {
                $kode = 'Belum Scan';
            }

            return [
                $a->idsls ?? '',
                $a->kdkec ?? '',
                $a->kddesa ?? '',
                $a->kdsls ?? '',
                $a->nmkec ?? '',
                $a->nmdesa ?? '',
                $a->nmsls ?? '',
                $a->iddesa ?? '',
                $a->alokasi_scan ?? '',
                $kode,
            ];
        });
    }

    /**
     * Dedicated CSV export endpoint for progress-georef.
     * Respects same filters and sorting as `/alokasi/progress-georef`.
     * Requires either filters/q or explicit export_all/per_page=all to allow full exports.
     */
    public function progressGeorefExport(Request $request)
    {
        $perPageAll = strtolower($request->query('per_page', '')) === 'all';
        $exportAll = $request->query('export_all') === 'true';

        // Build same query as progressGeoref
        $query = QueryBuilder::for(Alokasi::class)
            ->allowedFilters([
                AllowedFilter::partial('alokasi_georef'),
                AllowedFilter::partial('nmkec'),
                AllowedFilter::partial('nmdesa'),
                AllowedFilter::partial('nmsls'),
                AllowedFilter::partial('kdkec'),
                AllowedFilter::partial('kddesa'),
            ])
            ->select([
                'alokasis.idsls',
                'alokasis.kdkec',
                'alokasis.kddesa',
                'alokasis.kdsls',
                'alokasis.nmkec',
                'alokasis.nmdesa',
                'alokasis.nmsls',
                'alokasis.iddesa',
                'alokasis.alokasi_georef',
                'cek_georefs.kode as kode',
            ])
            ->leftJoin('cek_georefs', 'alokasis.idsls', '=', 'cek_georefs.kode')
            ->with(['cekGeoref' => function ($q) {
                $q->select('id', 'kode');
            }])
            ->allowedSorts([
                'kdkec', 'kddesa', 'kdsls', 'nmkec', 'nmdesa', 'nmsls', 'alokasi_georef', 'kode'
            ]);

        // Require explicit export flag or filters/q for full export
        $parsedQuery = $request->query();
        $ignoredKeys = ['page', 'per_page', 'export_all'];
        $nonEmptyParams = array_filter($parsedQuery, function ($v, $k) use ($ignoredKeys) {
            return !in_array($k, $ignoredKeys, true) && $v !== null && $v !== '';
        }, ARRAY_FILTER_USE_BOTH);

        $hasFilters = count($nonEmptyParams) > 0;
        if (!$hasFilters && trim((string) $request->getQueryString()) !== '') {
            $hasFilters = true;
        }

        $hasQ = !empty($request->query('q'));
        if (!($exportAll || $perPageAll || $hasFilters || $hasQ)) {
            return $this->error('Export denied: must specify filters/q or export_all=true to export entire dataset', null, 400);
        }

        $results = $query->cursor();
        $filename = 'progress-georef-export-' . date('Ymd_His') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        if (app()->runningUnitTests()) {
            $out = fopen('php://temp', 'r+');
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['idsls','kdkec','kddesa','kdsls','nmkec','nmdesa','nmsls','iddesa','alokasi_georef','kode']);

            foreach ($results as $a) {
                $kode = null;
                if (isset($a->kode) && $a->kode !== null) {
                    $kode = trim((string) $a->kode);
                }

                if ($kode === null || $kode === '') {
                    $kode = 'Belum Georef';
                }

                fputcsv($out, [
                    $a->idsls ?? '',
                    $a->kdkec ?? '',
                    $a->kddesa ?? '',
                    $a->kdsls ?? '',
                    $a->nmkec ?? '',
                    $a->nmdesa ?? '',
                    $a->nmsls ?? '',
                    $a->iddesa ?? '',
                    $a->alokasi_georef ?? '',
                    $kode,
                ]);
            }

            rewind($out);
            $content = stream_get_contents($out);
            fclose($out);

            return response($content, 200, $headers);
        }

        return response()->stream(function () use ($results) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, ['idsls','kdkec','kddesa','kdsls','nmkec','nmdesa','nmsls','iddesa','alokasi_georef','kode']);

            foreach ($results as $a) {
                $kode = null;
                if (isset($a->kode) && $a->kode !== null) {
                    $kode = trim((string) $a->kode);
                }

                if ($kode === null || $kode === '') {
                    $kode = 'Belum Georef';
                }

                fputcsv($out, [
                    $a->idsls ?? '',
                    $a->kdkec ?? '',
                    $a->kddesa ?? '',
                    $a->kdsls ?? '',
                    $a->nmkec ?? '',
                    $a->nmdesa ?? '',
                    $a->nmsls ?? '',
                    $a->iddesa ?? '',
                    $a->alokasi_georef ?? '',
                    $kode,
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }

    /**
     * Helper to build a CSV response from a cursor (iterable). Accepts a callback to map
     * each item into a flat array of column values. Writes a UTF-8 BOM and supports
     * in-memory assembly for unit tests and streaming for production.
     *
     * @param Request $request
     * @param iterable $results
     * @param array $headerRow
     * @param string $filename
     * @param callable $rowMapper (fn($item): array)
     * @return \Illuminate\Http\Response
     */
    protected function csvResponseFromCursor(Request $request, iterable $results, array $headerRow, string $filename, callable $rowMapper)
    {
        $headers = [
            'Content-Type' => 'text/csv; charset=utf-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        if (app()->runningUnitTests()) {
            $out = fopen('php://temp', 'r+');
            // write UTF-8 BOM to help Excel
            fwrite($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $headerRow);

            foreach ($results as $a) {
                $row = $rowMapper($a);
                fputcsv($out, $row);
            }

            rewind($out);
            $content = stream_get_contents($out);
            fclose($out);

            return response($content, 200, $headers);
        }

        return response()->stream(function () use ($results, $headerRow, $rowMapper) {
            $out = fopen('php://output', 'w');
            // write UTF-8 BOM to help Excel
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));
            fputcsv($out, $headerRow);

            foreach ($results as $a) {
                $row = $rowMapper($a);
                fputcsv($out, $row);
            }

            fclose($out);
        }, 200, $headers);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $alokasi = Alokasi::find($id);

        if (!$alokasi) {
            return $this->error('Alokasi not found', null, 404);
        }

        return $this->success($alokasi);
    }

    /**
     * Return dropdown options for `keterangan` field.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function keteranganOptions()
    {
        $options = [
            'PETA TIDAK ADA',
            'PETA RUSAK',
            'PETA TIDAK BISA DIBACA',
            'LAINNYA',
        ];

        return $this->success($options);
    }

    /**
     * Update the keterangan (text) column for an Alokasi record.
     * Accepts free text; for UX, callers may use one of the provided options.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateKeterangan(Request $request, $id)
    {
        $this->validate($request, [
            'keterangan' => ['required', 'string', 'max:1000'],
        ]);

        $alokasi = Alokasi::find($id);

        if (!$alokasi) {
            return $this->error('Alokasi not found', null, 404);
        }

        $alokasi->keterangan = $request->input('keterangan');
        $alokasi->save();

        return $this->success($alokasi, 'Keterangan updated');
    }
}
