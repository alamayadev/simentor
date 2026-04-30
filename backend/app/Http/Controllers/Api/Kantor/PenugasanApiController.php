<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Kantor\Penugasan\IndexPenugasanRequest;
use App\Http\Requests\Kantor\Penugasan\InsertPenugasanRequest;
use App\Http\Requests\Kantor\Penugasan\StorePenugasanRequest;
use App\Http\Requests\Kantor\Penugasan\UpdatePenugasanRequest;
use App\Models\Mitra;
use App\Models\Penugasan;
use App\Services\PenugasanService;
use App\Exports\PenugasanExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * @group Kantor - Penugasan
 *
 * APIs for managing assignments (Penugasan).
 */
class PenugasanApiController extends BaseApiController
{
    protected PenugasanService $penugasanService;

    public function __construct(PenugasanService $penugasanService)
    {
        $this->penugasanService = $penugasanService;
    }

    /**
     * List Penugasan
     *
     * Retrieve a list of assignments with optional filtering and pagination.
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int The number of items per page. Example: 20
     * @queryParam search string Search by name or other fields. Example: Survey
     * @queryParam fungsi string Filter by function. Example: IPDS
     * @queryParam kegiatan_id int Filter by activity ID. Example: 1
     * @queryParam mitra_id int Filter by partner ID. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "kegiatan_id": 1,
     *          "mitra_id": 1,
     *          "jabatan": "PCL",
     *          "volume": 100,
     *          "satuan": "Dokumen",
     *          "harga_satuan": 5000,
     *          "jumlah_bayar": 500000,
     *          "tgl_mulai": "2023-01-01",
     *          "tgl_selesai": "2023-01-31",
     *          "status": "active"
     *      }
     *  ],
     *  "meta": {
     *       "pagination_info": {
     *           "total": 50,
     *           "per_page": 20,
     *           "current_page": 1,
     *           "last_page": 3
     *       },
     *       "total_nilai": 25000000
     *  }
     * }
     */
    public function index(IndexPenugasanRequest $request)
    {
        $result = $this->penugasanService->listPenugasan($request->validated());

        return $this->success(
            $result['data'],
            'Data retrieved successfully',
            200,
            [
                'total_nilai' => $result['total_nilai'],
            ]
        );
    }

    /**
     * Get Filters
     *
     * Retrieve available filters for assignments.
     *
     * @response {
     *  "success": true,
     *  "message": "Filters retrieved successfully",
     *  "data": {
     *      "fungsi": ["IPDS", "Sosial"],
     *      "years": ["2022", "2023"]
     *  }
     * }
     */
    public function filters()
    {
        $data = $this->penugasanService->getFilters();
        return $this->success($data, 'Filters retrieved successfully');
    }

    /**
     * Create Penugasan
     *
     * Create a new assignment.
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Penugasan created successfully",
     *  "data": {
     *      "id": 1,
     *      "kegiatan_id": 1,
     *      "mitra_id": 1,
     *      "jabatan": "PCL",
     *      "volume": 100,
     *      "satuan": "Dokumen",
     *      "harga_satuan": 5000,
     *      "jumlah_bayar": 500000,
     *      "tgl_mulai": "2023-01-01",
     *      "tgl_selesai": "2023-01-31",
     *      "status": "pending"
     *  }
     * }
     */
    public function store(StorePenugasanRequest $request)
    {
        $penugasan = $this->penugasanService->createPenugasan(
            $request->validated(),
            Auth::id()
        );

        return $this->success($penugasan, 'Penugasan created successfully', 201);
    }

    /**
     * Show Penugasan
     *
     * Retrieve details of a specific assignment.
     *
     * @urlParam id string required The ID of the assignment. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "kegiatan_id": 1,
     *      "mitra_id": 1,
     *      "jabatan": "PCL",
     *      "volume": 100,
     *      "satuan": "Dokumen",
     *      "harga_satuan": 5000,
     *      "jumlah_bayar": 500000,
     *      "tgl_mulai": "2023-01-01",
     *      "tgl_selesai": "2023-01-31",
     *      "status": "active",
     *      "kegiatan": {
     *          "id": 1,
     *          "nama": "Annual Survey",
     *          "tahun": "2023",
     *          "fungsi": "IPDS"
     *      },
     *      "mitra": {
     *          "id": 1,
     *          "nama_lengkap": "Jane Doe",
     *          "sobat_id": "123456"
     *      },
     *      "pegawai": {
     *          "id": 1,
     *          "nama": "John Officer",
     *          "nip": "19900101"
     *      }
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Penugasan not found",
     *  "data": null
     * }
     */
    public function show($id)
    {
        $penugasan = Penugasan::with(['kegiatan:id,tahun,fungsi,nama', 'mitra:id,nama_lengkap,sobat_id', 'pegawai:id,nama,nip'])
            ->find($id);

        if (! $penugasan) {
            return $this->error('Penugasan not found', null, 404);
        }

        return $this->success($penugasan, 'Data retrieved successfully');
    }

    /**
     * Update Penugasan
     *
     * Update an existing assignment.
     *
     * @urlParam id string required The ID of the assignment. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Penugasan updated successfully",
     *  "data": {
     *      "id": 1,
     *      "volume": 120,
     *      "jumlah_bayar": 600000
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Penugasan not found",
     *  "data": null
     * }
     */
    public function update(UpdatePenugasanRequest $request, $id)
    {
        $penugasan = Penugasan::find($id);
        if (! $penugasan) {
            return $this->error('Penugasan not found', null, 404);
        }

        $updated = $this->penugasanService->updatePenugasan($penugasan, $request->validated());

        return $this->success($updated, 'Penugasan updated successfully');
    }

    /**
     * Delete Penugasan
     *
     * Delete an assignment.
     *
     * @urlParam id string required The ID of the assignment. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Penugasan berhasil dihapus",
     *  "data": null
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Penugasan not found",
     *  "data": null
     * }
     */
    public function destroy($id)
    {
        $penugasan = Penugasan::find($id);
        if (! $penugasan) {
            return $this->error('Penugasan not found', null, 404);
        }

        $this->penugasanService->deletePenugasan($penugasan);

        return $this->success(null, 'Penugasan berhasil dihapus');
    }

    /**
     * Insert Penugasan
     *
     * Create a new assignment based on an existing one (cloning/insertion).
     *
     * @urlParam id string required The ID of the existing assignment to base on. Example: 1
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Penugasan created successfully",
     *  "data": {
     *      "id": 2,
     *      "source_id": 1,
     *      "status": "pending"
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Penugasan not found",
     *  "data": null
     * }
     */
    public function insert(InsertPenugasanRequest $request, $id)
    {
        $existing = Penugasan::find($id);
        if (! $existing) {
            return $this->error('Penugasan not found', null, 404);
        }

        $newPenugasan = $this->penugasanService->insertPenugasan(
            $existing,
            $request->validated(),
            Auth::id()
        );

        return $this->success($newPenugasan, 'Penugasan created successfully', 201);
    }

    /**
     * Mitra Dropdown
     *
     * Retrieve a list of partners for dropdown selection.
     *
     * @queryParam filter[sobat_id] string Filter by Sobat ID.
     * @queryParam filter[nama_lengkap] string Filter by full name.
     * @queryParam filter[nik] string Filter by NIK.
     *
     * @response {
     *  "success": true,
     *  "message": "Mitra list retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "sobat_id": "123456",
     *          "nama_lengkap": "Jane Doe",
     *          "nik": "1234567890123456",
     *          "keca": "010"
     *      }
     *  ]
     * }
     */
    public function mitraDropdown(Request $request)
    {
        $mitra = QueryBuilder::for(Mitra::class)
            ->allowedFilters(['sobat_id', 'nama_lengkap', 'nik'])
            ->limit(10)
            ->get(['id', 'sobat_id', 'nama_lengkap', 'nik', 'keca']);

        return $this->success($mitra, 'Mitra list retrieved successfully');
    }

    /**
     * Get Mitra Options
     *
     * Retrieve all partner options.
     *
     * @response {
     *  "success": true,
     *  "message": "Mitra options retrieved successfully",
     *  "data": {
     *      "mitraOptions": [
     *          {"id": 1, "nama": "Jane Doe"}
     *      ]
     *  }
     * }
     */
    public function mitraOptions()
    {
        $mitraOptions = $this->penugasanService->getMitraOptions();
        return $this->success(['mitraOptions' => $mitraOptions], 'Mitra options retrieved successfully');
    }

    /**
     * Get Form Options
     *
     * Retrieve options for assignment forms.
     *
     * @response {
     *  "success": true,
     *  "message": "Options retrieved successfully",
     *  "data": {
     *      "jabatan": ["PCL", "PML"],
     *      "satuan": ["Dokumen", "Orang"]
     *  }
     * }
     */
    public function formOptions()
    {
        $data = $this->penugasanService->getFormOptions();
        return $this->success($data, 'Options retrieved successfully');
    }

    /**
     * Get Kegiatan Options
     *
     * Retrieve activity options filtered by function.
     *
     * @queryParam fungsi string required The function to filter by. Example: IPDS
     *
     * @response {
     *  "success": true,
     *  "message": "Kegiatan options retrieved successfully",
     *  "data": {
     *      "kegiatanOptions": [
     *          {"id": 1, "nama": "Annual Survey"}
     *      ]
     *  }
     * }
     *
     * @response 422 {
     *  "success": false,
     *  "message": "Fungsi parameter is required.",
     *  "data": null
     * }
     */
    public function kegiatanOptions(Request $request)
    {
        $fungsi = $request->query('fungsi');
        if (! $fungsi) {
            return $this->error('Fungsi parameter is required.', null, 422);
        }

        $kegiatanOptions = $this->penugasanService->getKegiatanOptions($fungsi);

        return $this->success(['kegiatanOptions' => $kegiatanOptions], 'Kegiatan options retrieved successfully');
    }

    /**
     * Export Penugasan to Excel
     *
     * Download penugasan data as an Excel file, filtered by bulan bayar.
     *
     * @queryParam filter[bln_bayar] string Filter by bulan bayar (YYYY-MM-DD format). Example: 2025-11-01
     *
     * @response binary XLSX file download
     */
    public function export(Request $request)
    {
        $blnBayar = $request->input('filter.bln_bayar');

        $data = $this->penugasanService->getExportData($blnBayar);

        $monthLabel = $blnBayar
            ? \Carbon\Carbon::parse($blnBayar)->format('Y_m')
            : date('Y_m');

        $fileName = "penugasan_{$monthLabel}.xlsx";

        return Excel::download(new PenugasanExport($data), $fileName);
    }
}
