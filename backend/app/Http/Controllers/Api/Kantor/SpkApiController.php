<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Kantor\Spk\BulkUpdateBastRequest;
use App\Http\Requests\Kantor\Spk\BulkUpdateSpkRequest;
use App\Http\Requests\Kantor\Spk\IndexSpkRequest;
use App\Http\Requests\Kantor\Spk\MonitoringSpkRequest;
use App\Http\Requests\Kantor\Spk\UpdateSpkRequest;
use App\Services\SpkService;

/**
 * @group Kantor - SPK & BAST
 */
class SpkApiController extends BaseApiController
{
    protected SpkService $spkService;

    public function __construct(SpkService $spkService)
    {
        $this->spkService = $spkService;
    }

    /**
     * Menampilkan daftar SPK dengan pagination dan filtering.
     *
     * Endpoint ini mengembalikan daftar SPK yang dikelompokkan berdasarkan mitra_id dan bln_bayar.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 10). Example: 10
     * @queryParam page int Halaman yang ditampilkan (default: 1). Example: 1
     * @queryParam selectedbln date Filter berdasarkan bulan bayar (format: YYYY-MM atau YYYY-MM-DD). Example: 2024-01-01
     * @queryParam sort_by string Kolom untuk sorting (created_at, bln_bayar, total, jml_tugas, id). Example: created_at
     * @queryParam sort_dir string Arah sorting ASC/DESC (default: DESC). Example: DESC
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "mitra_id": 1,
     *       "bln_bayar": "2024-01-01",
     *       "no_sk": "SK-001",
     *       "tgl_sk": "2024-01-01",
     *       "no_bast": "BAST-001",
     *       "tgl_bast": "2024-01-15",
     *       "total": 5000000,
     *       "jml_tugas": 3,
     *       "mitra": {
     *         "id": 1,
     *         "nama_lengkap": "Budi Santoso",
     *         "nik": "1234567890123456",
     *         "alamat_detail": "Jl. Contoh No. 123"
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "listbln": ["2024-01-01", "2023-12-01"],
     *     "limit_nilai": 4000000,
     *     "pagination_info": {
     *       "total_page": 1,
     *       "total_records": 10
     *     }
     *   }
     * }
     */
    public function index(IndexSpkRequest $request)
    {
        $result = $this->spkService->listSpk($request->validated());

        return $this->success(
            $result['data'],
            'Data retrieved successfully',
            200,
            [
                'listbln'         => $result['listbln'],
                'limit_nilai'     => $result['limit_nilai'],
                'pagination_info' => $result['pagination_info'],
            ]
        );
    }

    /**
     * Menampilkan detail SPK untuk pengeditan.
     *
     * Endpoint ini mengembalikan detail SPK berdasarkan mitra_id dan bulan bayar.
     *
     * @authenticated
     *
     * @urlParam mitra_id int required ID Mitra. Example: 1
     * @urlParam bln_bayar date required Bulan bayar (format: YYYY-MM-DD). Example: 2024-01-01
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "mitra_id": 1,
     *     "bln_bayar": "2024-01-01",
     *     "no_sk": "SK-001",
     *     "tgl_sk": "2024-01-01",
     *     "no_bast": "BAST-001",
     *     "tgl_bast": "2024-01-15",
     *     "items": [
     *       {
     *         "id": 1,
     *         "kegiatan_id": 101,
     *         "nilai": 1500000
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "SPK record not found"
     * }
     */
    public function show($mitra_id, $bln_bayar)
    {
        try {
            $data = $this->spkService->showSpk((int) $mitra_id, $bln_bayar);
            return $this->success($data, 'Data retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('SPK record not found', $e->getMessage(), 404);
        }
    }

    /**
     * Memperbarui data SPK.
     *
     * Endpoint ini memperbarui nomor SK, tanggal SK, nomor BAST, dan tanggal BAST untuk SPK tertentu.
     *
     * @authenticated
     *
     * @urlParam mitra_id int required ID Mitra. Example: 1
     * @urlParam bln_bayar date required Bulan bayar (format: YYYY-MM-DD). Example: 2024-01-01
     *
     * @bodyParam no_sk string Nomor SK. Example: SK-001-REV
     * @bodyParam tgl_sk date Tanggal SK (format: YYYY-MM-DD). Example: 2024-01-02
     * @bodyParam no_bast string Nomor BAST. Example: BAST-001-REV
     * @bodyParam tgl_bast date Tanggal BAST (format: YYYY-MM-DD). Example: 2024-01-16
     *
     * @response 200 {
     *   "success": true,
     *   "message": "SPK record updated successfully"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "No SPK records found to update"
     * }
     */
    public function update(UpdateSpkRequest $request, $mitra_id, $bln_bayar)
    {
        try {
            $updated = $this->spkService->updateSpk((int) $mitra_id, $bln_bayar, $request->validated());

            if ($updated === 0) {
                return $this->error('No SPK records found to update', null, 404);
            }

            return $this->success(null, 'SPK record updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update SPK record', $e->getMessage(), 500);
        }
    }

    /**
     * Update Tanggal SPK Massal.
     *
     * Endpoint ini digunakan untuk memperbarui tanggal SK dan bulan bayar secara massal untuk beberapa mitra.
     *
     * @authenticated
     *
     * @bodyParam mitra_ids int[] required List ID Mitra. Example: [1, 2, 3]
     * @bodyParam tgl_sk date required Tanggal SK baru (format: YYYY-MM-DD). Example: 2024-01-01
     * @bodyParam bln_bayar date required Bulan bayar target (format: YYYY-MM). Example: 2024-01
     *
     * @response 200 {
     *   "success": true,
     *   "message": "SPK records updated successfully",
     *   "data": {
     *     "updated_count": 5
     *   }
     * }
     */
    public function bulkUpdateSpk(BulkUpdateSpkRequest $request)
    {
        $count = $this->spkService->bulkUpdateSpk(
            $request->input('mitra_ids'),
            $request->input('tgl_sk'),
            $request->input('bln_bayar')
        );

        return $this->success(['updated_count' => $count], 'SPK records updated successfully');
    }

    /**
     * Update Tanggal BAST Massal.
     *
     * Endpoint ini digunakan untuk memperbarui tanggal BAST dan bulan bayar secara massal untuk beberapa mitra.
     *
     * @authenticated
     *
     * @bodyParam mitra_ids int[] required List ID Mitra. Example: [1, 2, 3]
     * @bodyParam tgl_bast date required Tanggal BAST baru (format: YYYY-MM-DD). Example: 2024-01-15
     * @bodyParam bln_bayar date required Bulan bayar target (format: YYYY-MM). Example: 2024-01
     *
     * @response 200 {
     *   "success": true,
     *   "message": "BAST records updated successfully",
     *   "data": {
     *     "updated_count": 5
     *   }
     * }
     */
    public function bulkUpdateBast(BulkUpdateBastRequest $request)
    {
        $count = $this->spkService->bulkUpdateBast(
            $request->input('mitra_ids'),
            $request->input('tgl_bast'),
            $request->input('bln_bayar')
        );

        return $this->success(['updated_count' => $count], 'BAST records updated successfully');
    }

    /**
     * Monitoring Status SPK/BAST.
     *
     * Endpoint untuk memantau kelengkapan data SPK/BAST (misalnya: tanpa SPK, tanpa BAST, atau nilai di atas 4 juta).
     *
     * @authenticated
     *
     * @queryParam type string required Jenis monitoring (tanpa_spk, tanpa_bast, diatas_4jt). Example: tanpa_spk
     * @queryParam per_page int Jumlah item per halaman (default: 10). Example: 10
     * @queryParam page int Halaman yang ditampilkan (default: 1). Example: 1
     * @queryParam selectedbln date Filter berdasarkan bulan bayar (format: YYYY-MM atau YYYY-MM-DD). Example: 2024-01-01
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Monitoring data retrieved",
     *   "data": [
     *     {
     *       "mitra_id": 1,
     *       "nama_mitra": "Budi Santoso",
     *       "status_issue": "Belum ada No SK"
     *     }
     *   ],
     *   "meta": {
     *     "listbln": ["2024-01-01", "2023-12-01"],
     *     "pagination_info": {
     *       "total_page": 1,
     *       "total_records": 5
     *     }
     *   }
     * }
     */
    public function monitoring(MonitoringSpkRequest $request)
    {
        $result = $this->spkService->monitoringSpk($request->validated());

        return $this->success(
            $result['data'],
            'Monitoring data retrieved',
            200,
            [
                'listbln'         => $result['listbln'],
                'pagination_info' => $result['pagination_info'],
            ]
        );
    }
}
