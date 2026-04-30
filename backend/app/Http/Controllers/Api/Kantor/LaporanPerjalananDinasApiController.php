<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Kantor\Laperdin\StoreLaperdinDetailRequest;
use App\Http\Requests\Kantor\Laperdin\StoreLaperdinDokumentasiRequest;
use App\Http\Requests\Kantor\Laperdin\StoreLaperdinRequest;
use App\Http\Requests\Kantor\Laperdin\UpdateLaperdinDetailRequest;
use App\Http\Requests\Kantor\Laperdin\UpdateLaperdinDokumentasiRequest;
use App\Http\Requests\Kantor\Laperdin\UpdateLaperdinRequest;
use App\Models\LaporanPerjalananDinas;
use App\Models\LaporanPerjalananDinasDetail;
use App\Models\LaporanPerjalananDinasDokumentasi;
use App\Services\LaporanPerjalananDinasService;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * @group Kantor - Laperdin
 *
 * APIs for managing Official Travel Reports (Laporan Perjalanan Dinas).
 */
class LaporanPerjalananDinasApiController extends BaseApiController
{
    protected LaporanPerjalananDinasService $laperdinService;

    public function __construct(LaporanPerjalananDinasService $laperdinService)
    {
        $this->laperdinService = $laperdinService;
    }

    /**
     * List Laporan
     *
     * Retrieve a list of travel reports with pagination.
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int The number of items per page. Example: 20
     *
     * @response {
     *  "success": true,
     *  "message": "Laporan perjalanan dinas list retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "nama_traveler": "John Doe",
     *          "tujuan": "Jakarta",
     *          "lama_tanggal": "3 days",
     *          "dalam_rangka": "Meeting",
     *          "pembebanan": "DIPA",
     *          "kode_keg": 1,
     *          "status": "draft",
     *          "created_at": "2023-01-01T00:00:00.000000Z",
     *          "updated_at": "2023-01-01T00:00:00.000000Z"
     *      }
     *  ],
     *  "meta": {
     *       "pagination_info": {
     *           "total": 50,
     *           "per_page": 20,
     *           "current_page": 1,
     *           "last_page": 3
     *       }
     *  }
     * }
     */
    public function index()
    {
        $perPage = request()->get('per_page', 20);
        $laporan = $this->laperdinService->listLaporan($perPage);

        return $this->success($laporan, 'Laporan perjalanan dinas list retrieved successfully');
    }

    /**
     * Create Laporan
     *
     * Create a new travel report.
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Laporan perjalanan dinas created successfully",
     *  "data": {
     *      "id": 1,
     *      "nama_traveler": "John Doe",
     *      "tujuan": "Jakarta",
     *      "lama_tanggal": "3 days",
     *      "dalam_rangka": "Meeting",
     *      "pembebanan": "DIPA",
     *      "kode_keg": 1,
     *      "status": "draft",
     *      "created_at": "2023-01-01T00:00:00.000000Z",
     *      "updated_at": "2023-01-01T00:00:00.000000Z"
     *  }
     * }
     */
    public function store(StoreLaperdinRequest $request)
    {
        $laporan = $this->laperdinService->createLaporan(
            $request->validated(),
            $request->user()->id ?? null
        );

        return $this->success($laporan, 'Laporan perjalanan dinas created successfully', 201);
    }

    /**
     * Show Laporan
     *
     * Retrieve details of a specific travel report.
     *
     * @urlParam id int required The ID of the report. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Laporan perjalanan dinas details retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "nama_traveler": "John Doe",
     *      "tujuan": "Jakarta",
     *      "lama_tanggal": "3 days",
     *      "dalam_rangka": "Meeting",
     *      "pembebanan": "DIPA",
     *      "kode_keg": 1,
     *      "status": "draft",
     *      "details": [
     *          {
     *              "id": 1,
     *              "laporan_perjalanan_dinas_id": 1,
     *              "tanggal": "2023-01-01",
     *              "uraian_lhp": "Meeting with client",
     *              "kendala": "None",
     *              "solusi": "N/A"
     *          }
     *      ],
     *      "dokumentasi": [
     *          {
     *              "id": 1,
     *              "laporan_perjalanan_dinas_id": 1,
     *              "file_path": "path/to/file.jpg",
     *              "deskripsi": "Meeting photo"
     *          }
     *      ]
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Laporan perjalanan dinas not found",
     *  "data": null
     * }
     */
    public function show($id)
    {
        $laporan = Cache::remember("laperdin_detail_{$id}", 3600, function () use ($id) {
            return LaporanPerjalananDinas::with(['details', 'dokumentasi'])->find($id);
        });

        if (! $laporan) {
            return $this->error('Laporan perjalanan dinas not found', null, 404);
        }

        return $this->success($laporan, 'Laporan perjalanan dinas details retrieved successfully');
    }

    /**
     * Update Laporan
     *
     * Update an existing travel report.
     *
     * @urlParam id int required The ID of the report. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Laporan perjalanan dinas updated successfully",
     *  "data": {
     *      "id": 1,
     *      "nama_traveler": "John Doe",
     *      "tujuan": "Bandung",
     *      "lama_tanggal": "2 days",
     *      "status": "submitted"
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Laporan perjalanan dinas not found",
     *  "data": null
     * }
     */
    public function update(UpdateLaperdinRequest $request, $id)
    {
        $laporan = LaporanPerjalananDinas::find($id);

        if (! $laporan) {
            return $this->error('Laporan perjalanan dinas not found', null, 404);
        }

        $this->laperdinService->updateLaporan($laporan, $request->validated());

        return $this->success($laporan, 'Laporan perjalanan dinas updated successfully');
    }

    /**
     * Delete Laporan
     *
     * Delete a travel report.
     *
     * @urlParam id int required The ID of the report. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Laporan perjalanan dinas deleted successfully",
     *  "data": null
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Laporan perjalanan dinas not found",
     *  "data": null
     * }
     */
    public function destroy($id)
    {
        $laporan = LaporanPerjalananDinas::with('dokumentasi')->find($id);

        if (! $laporan) {
            return $this->error('Laporan perjalanan dinas not found', null, 404);
        }

        $this->laperdinService->deleteLaporan($laporan);

        return $this->success(null, 'Laporan perjalanan dinas deleted successfully');
    }

    /**
     * Create Detail
     *
     * Add a detail entry to a travel report.
     *
     * @urlParam id int required The ID of the report involved. Example: 1
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Detail laporan perjalanan dinas created successfully",
     *  "data": {
     *      "id": 1,
     *      "laporan_perjalanan_dinas_id": 1,
     *      "tanggal": "2023-01-01",
     *      "uraian_lhp": "Meeting with client",
     *      "kendala": "None",
     *      "solusi": "N/A"
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Laporan perjalanan dinas not found",
     *  "data": null
     * }
     */
    public function storeDetail(StoreLaperdinDetailRequest $request, $id)
    {
        $laporan = LaporanPerjalananDinas::find($id);

        if (! $laporan) {
            return $this->error('Laporan perjalanan dinas not found', null, 404);
        }

        $detail = $this->laperdinService->createDetail($laporan, $request->validated());

        return $this->success($detail, 'Detail laporan perjalanan dinas created successfully', 201);
    }

    /**
     * Update Detail
     *
     * Update a detail entry of a travel report.
     *
     * @urlParam id int required The ID of the report involved. Example: 1
     * @urlParam detailId int required The ID of the detail entry. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Detail laporan perjalanan dinas updated successfully",
     *  "data": {
     *      "id": 1,
     *      "uraian_lhp": "Updated description",
     *      "kendala": "Traffic",
     *      "solusi": "Depart earlier"
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Detail laporan perjalanan dinas not found",
     *  "data": null
     * }
     */
    public function updateDetail(UpdateLaperdinDetailRequest $request, $id, $detailId)
    {
        $detail = LaporanPerjalananDinasDetail::where('laporan_perjalanan_dinas_id', $id)->find($detailId);

        if (! $detail) {
            return $this->error('Detail laporan perjalanan dinas not found', null, 404);
        }

        $this->laperdinService->updateDetail($detail, $request->validated());

        return $this->success($detail, 'Detail laporan perjalanan dinas updated successfully');
    }

    /**
     * Delete Detail
     *
     * Delete a detail entry from a travel report.
     *
     * @urlParam id int required The ID of the report involved. Example: 1
     * @urlParam detailId int required The ID of the detail entry. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Detail laporan perjalanan dinas deleted successfully",
     *  "data": null
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Detail laporan perjalanan dinas not found",
     *  "data": null
     * }
     */
    public function destroyDetail($id, $detailId)
    {
        $detail = LaporanPerjalananDinasDetail::where('laporan_perjalanan_dinas_id', $id)->find($detailId);

        if (! $detail) {
            return $this->error('Detail laporan perjalanan dinas not found', null, 404);
        }

        $this->laperdinService->deleteDetail($detail);

        return $this->success(null, 'Detail laporan perjalanan dinas deleted successfully');
    }

    /**
     * Upload Dokumentasi
     *
     * Upload documentation (photo/file) for a travel report.
     *
     * @urlParam id int required The ID of the report. Example: 1
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Dokumentasi uploaded successfully",
     *  "data": {
     *      "id": 1,
     *      "laporan_perjalanan_dinas_id": 1,
     *      "file_path": "uploads/laperdin/doc1.jpg",
     *      "deskripsi": "Meeting photo"
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Laporan perjalanan dinas not found",
     *  "data": null
     * }
     */
    public function uploadDokumentasi(StoreLaperdinDokumentasiRequest $request, $id)
    {
        $laporan = LaporanPerjalananDinas::find($id);

        if (! $laporan) {
            return $this->error('Laporan perjalanan dinas not found', null, 404);
        }

        $dokumentasi = $this->laperdinService->uploadDokumentasi(
            $laporan,
            $request->file('file'),
            $request->deskripsi
        );

        return $this->success($dokumentasi, 'Dokumentasi uploaded successfully', 201);
    }

    /**
     * Update Dokumentasi
     *
     * Update documentation for a travel report.
     *
     * @urlParam id int required The ID of the report. Example: 1
     * @urlParam docId int required The ID of the documentation. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Dokumentasi updated successfully",
     *  "data": {
     *      "id": 1,
     *      "deskripsi": "Updated description"
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Dokumentasi not found",
     *  "data": null
     * }
     */
    public function updateDokumentasi(UpdateLaperdinDokumentasiRequest $request, $id, $docId)
    {
        $dokumentasi = LaporanPerjalananDinasDokumentasi::where('laporan_perjalanan_dinas_id', $id)->find($docId);

        if (! $dokumentasi) {
            return $this->error('Dokumentasi not found', null, 404);
        }

        $this->laperdinService->updateDokumentasi(
            $dokumentasi,
            $request->file('file'),
            $request->deskripsi
        );

        return $this->success($dokumentasi, 'Dokumentasi updated successfully');
    }

    /**
     * Delete Dokumentasi
     *
     * Delete documentation from a travel report.
     *
     * @urlParam id int required The ID of the report. Example: 1
     * @urlParam docId int required The ID of the documentation. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Dokumentasi deleted successfully",
     *  "data": null
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Dokumentasi not found",
     *  "data": null
     * }
     */
    public function deleteDokumentasi($id, $docId)
    {
        $dokumentasi = LaporanPerjalananDinasDokumentasi::where('laporan_perjalanan_dinas_id', $id)->find($docId);

        if (! $dokumentasi) {
            return $this->error('Dokumentasi not found', null, 404);
        }

        $this->laperdinService->deleteDokumentasi($dokumentasi);

        return $this->success(null, 'Dokumentasi deleted successfully');
    }

    /**
     * Generate PDF
     *
     * Generate a PDF version of the travel report.
     *
     * @urlParam id int required The ID of the report. Example: 1
     *
     * @response {
     *  "message": "PDF Download initiated"
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Laporan perjalanan dinas not found",
     *  "data": null
     * }
     */
    public function generatePdf($id)
    {
        $laporan = LaporanPerjalananDinas::with(['details', 'dokumentasi'])->find($id);

        if (! $laporan) {
            return $this->error('Laporan perjalanan dinas not found', null, 404);
        }

        $pdf = PDF::loadView('pdf.laperdin', compact('laporan'))
            ->setPaper('a4', 'portrait');

        $filename = 'Laporan_Perjalanan_Dinas_' . $id . '.pdf';

        return $pdf->download($filename);
    }
}
