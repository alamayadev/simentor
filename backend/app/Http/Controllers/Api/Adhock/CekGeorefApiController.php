<?php

namespace App\Http\Controllers\Api\Adhock;

use App\Http\Controllers\Api\BaseApiController;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CekGeoref;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

/**
 * CekGeoref API Controller
 *
 * Controller API untuk memeriksa data georeference dan mengembalikan daftar filter terkait.
 * Endpoint ini mengembalikan record georeference serta filter untuk lokasi, nama file,
 * dan operator.
 *
 * @group IPDS - Pengolahan Wilkerstat
 * @unauthenticated
 */
class CekGeorefApiController extends BaseApiController
{
    /**
     * Mengembalikan daftar data georeference dengan filter dan pagination.
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
        $query = QueryBuilder::for(CekGeoref::class)
            ->allowedFilters([
                AllowedFilter::partial('kec'),
                AllowedFilter::partial('desa'),
                AllowedFilter::partial('lokasi'),
                AllowedFilter::partial('kodename'),
                AllowedFilter::callback('alokasi_georef', function ($query, $value) {
                    $query->whereHas('alokasi', function ($q) use ($value) {
                        $q->where('alokasi_georef', 'like', "%{$value}%");
                    });
                }),
            ])
            ->allowedSorts([
                'kec', 'desa', 'kode',
            ])
            ->where(function ($query) {
                $query->where('filename', 'like', '%.tif')
                      ->orWhere('filename', 'like', '%.tiff')
                      ->orWhere('filename', 'like', '%.png')
                      ->orWhere('filename', 'like', '%.jpg')
                      ->orWhere('filename', 'like', '%.jpeg');
            });

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

        $cekGeorefs = $query->with('alokasi')->fastPaginate(20);

        // Only load filters if specifically requested (to improve performance)
        $loadFilters = $request->boolean('load_filters', true); // Default to true for backward compatibility
        $filters = [];

        if ($loadFilters) {
            // Optimized filter queries with proper indexing
            $lokasi = CekGeoref::select('lokasi')
                ->distinct()
                ->whereNotNull('lokasi')
                ->where('lokasi', '!=', '')
                ->orderBy('lokasi')
                ->pluck('lokasi');

            $kodename = CekGeoref::select('kodename')
                ->distinct()
                ->whereNotNull('kodename')
                ->where('kodename', '!=', '')
                ->orderBy('kodename')
                ->pluck('kodename');

            $kecList = CekGeoref::select('kec')
                ->distinct()
                ->whereNotNull('kec')
                ->where('kec', '!=', '')
                ->orderBy('kec')
                ->pluck('kec');

            // Optimized operator names query using JOIN instead of N+1
            $operatorNames = CekGeoref::join('alokasis', 'cek_georefs.kode', '=', 'alokasis.idsls')
                ->select('alokasis.alokasi_georef')
                ->distinct()
                ->whereNotNull('alokasis.alokasi_georef')
                ->where('alokasis.alokasi_georef', '!=', '')
                ->orderBy('alokasis.alokasi_georef')
                ->pluck('alokasis.alokasi_georef');

            $filters = [
                'lokasi' => $lokasi,
                'kodename' => $kodename,
                'operator' => $operatorNames,
                'kec' => $kecList,
            ];
        }

        // Add pagination extras including total_page
        $totalPages = 20 > 0 ? ceil($totalRecords / 20) : 1;
        
        return $this->success($cekGeorefs, 'Data retrieved successfully', 200, [
            'filters' => $filters,
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ],
        ]);
    }

    public function show($id)
    {
        $item = CekGeoref::with('alokasi')->find($id);

        if (!$item) {
            return $this->error('CekGeoref not found', null, 404);
        }

        return $this->success($item);
    }
}
