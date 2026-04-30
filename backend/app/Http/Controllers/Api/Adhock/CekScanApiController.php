<?php

namespace App\Http\Controllers\Api\Adhock;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CekScan;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * CekScan API Controller
 *
 * Controller API untuk memeriksa data scan dan mengembalikan daftar filter terkait.
 * Gunakan endpoint ini untuk mengambil daftar record scan dan opsi filter seperti lokasi,
 * nama file, dan operator.
 *
 * @group IPDS - Pengolahan Wilkerstat
 * @unauthenticated
 */
class CekScanApiController extends BaseApiController
{
    /**
     * Mengembalikan daftar data scan dengan filter dan pagination.
     * Response menggunakan standard BaseApiController format dengan automatic pagination.
     * Diperbaiki untuk menghilangkan double data wrapper issue.
     *
     * @group IPDS - Pengolahan Wilkerstat
     * @unauthenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[kec] string Filter berdasarkan kecamatan. Contoh: Bandung
     * @queryParam filter[desa] string Filter berdasarkan desa. Contoh: Sukajadi
     * @queryParam filter[lokasi] string Filter berdasarkan lokasi. Contoh: RT 01
     * @queryParam filter[kodename] string Filter berdasarkan kode. Contoh: 3201010001
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        // Build the base query with allowed filters (except global) and sorts
        $query = QueryBuilder::for(CekScan::class)
            ->allowedFilters([
                AllowedFilter::partial('kec'),
                AllowedFilter::partial('desa'),
                AllowedFilter::partial('lokasi'),
                AllowedFilter::partial('kodename'),
                AllowedFilter::callback('alokasi_scan', function ($query, $value) {
                    $query->whereHas('alokasi', function ($q) use ($value) {
                        $q->where('alokasi_scan', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'kec',
                'desa',
                'kode',
            ]);

        // Ordinary global search (not a Spatie filter). Use query param `global`.
        $global = $request->input('global');
        if (!empty($global)) {
            $query->where(function ($q) use ($global) {
                $q->where('kode', 'like', "%{$global}%")
                  ->orWhere('kec', 'like', "%{$global}%")
                  ->orWhere('desa', 'like', "%{$global}%");
            });
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $query->count();

        $cekScans = $query->with('alokasi')->fastPaginate(20);

        // Only load filters if specifically requested (to improve performance)
        $loadFilters = $request->boolean('load_filters', true); // Default to true for backward compatibility
        $filters = [];

        if ($loadFilters) {
            // Optimized filter queries with proper indexing
            $lokasi = CekScan::select('lokasi')
                ->distinct()
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->orderBy('lokasi')
                ->pluck('lokasi');

            $kodename = CekScan::select('kodename')
                ->distinct()
                ->whereNotNull('kodename')
                ->where('kodename', '!=', '')
                ->orderBy('kodename')
                ->pluck('kodename');

            $kecList = CekScan::select('kec')
                ->distinct()
                ->whereNotNull('kec')
                ->where('kec', '!=', '')
                ->orderBy('kec')
                ->pluck('kec');

            // Optimized operator names query using JOIN instead of N+1
            $operatorNames = CekScan::join('alokasis', 'cek_scans.kode', '=', 'alokasis.idsls')
                ->select('alokasis.alokasi_scan')
                ->distinct()
                ->whereNotNull('alokasis.alokasi_scan')
                ->where('alokasis.alokasi_scan', '!=', '')
                ->orderBy('alokasis.alokasi_scan')
                ->pluck('alokasis.alokasi_scan');

            $filters = [
                'lokasi' => $lokasi,
                'kodename' => $kodename,
                'operator' => $operatorNames,
                'kec' => $kecList,
            ];
        }

        // Add pagination extras including total_page
        $totalPages = 20 > 0 ? ceil($totalRecords / 20) : 1;
        
        return $this->success($cekScans, 'Data retrieved successfully', 200, [
            'filters' => $filters,
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ],
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $cekScan = CekScan::with('alokasi')->find($id);

        if (!$cekScan) {
            return $this->error('CekScan not found', null, 404);
        }

        return $this->success($cekScan);
    }
}
