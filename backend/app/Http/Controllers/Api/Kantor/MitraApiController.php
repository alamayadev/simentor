<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Kantor\Mitra\IndexMitraRequest;
use App\Http\Requests\Kantor\Mitra\StoreMitraPenugasanRequest;
use App\Services\MitraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * @group Kantor - Mitra
 *
 * APIs for managing Partners (Mitra).
 */
class MitraApiController extends BaseApiController
{
    protected MitraService $mitraService;

    public function __construct(MitraService $mitraService)
    {
        $this->mitraService = $mitraService;
    }

    /**
     * List Mitra
     *
     * Retrieve a list of partners with optional filtering and pagination.
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int The number of items per page. Example: 20
     * @queryParam search string Search by name or email. Example: John
     * @queryParam fungsi string Filter by function. Example: IPDS
     * @queryParam sobat_id string Filter by Sobat ID. Example: 12345
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "nama": "Jane Doe",
     *          "email": "jane@example.com",
     *          "sobat_id": "123456",
     *          "posisi": "PCL",
     *          "keca": "010",
     *          "desa": "001",
     *          "alamat": "Jl. Merdeka No. 1",
     *          "no_hp": "08123456789",
     *          "bank": "BRI",
     *          "no_rek": "1234567890",
     *          "an_rek": "Jane Doe",
     *          "status": "active",
     *          "penugasan_count": 5
     *      }
     *  ],
     *  "meta": {
     *       "pagination_info": {
     *           "total": 100,
     *           "per_page": 20,
     *           "current_page": 1,
     *           "last_page": 5
     *       }
     *  }
     * }
     */
    public function index(IndexMitraRequest $request)
    {
        $result = $this->mitraService->listMitra($request->validated());

        return $this->success(
            $result['data'],
            'Data retrieved successfully',
            200
        );
    }

    /**
     * Get Filters
     *
     * Retrieve available filters for partners.
     *
     * @response {
     *  "success": true,
     *  "message": "Filters retrieved successfully",
     *  "data": {
     *      "fungsi": ["IPDS", "Sosial"],
     *      "posisi": ["PCL", "PML"]
     *  }
     * }
     */
    public function filters(Request $request)
    {
        $data = $this->mitraService->getFilters($request->all());
        return $this->success($data, 'Filters retrieved successfully');
    }

    public function statistics()
    {
        $data = $this->mitraService->getStatistics();

        return $this->success($data, 'Statistics retrieved successfully');
    }

    /**
     * Get Penugasan Options
     *
     * Retrieve options for assigning tasks to partners.
     *
     * @response {
     *  "success": true,
     *  "message": "Options retrieved successfully",
     *  "data": {
     *      "kegiatan": [
     *          {"id": 1, "nama": "Annual Survey"}
     *      ],
     *      "jabatan": ["PCL", "PML"]
     *  }
     * }
     */
    public function penugasanOptions()
    {
        $data = $this->mitraService->getPenugasanOptions();
        return $this->success($data, 'Options retrieved successfully');
    }

    /**
     * Create Penugasan
     *
     * Assign a task to a partner.
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Penugasan created successfully",
     *  "data": {
     *      "id": 1,
     *      "mitra_id": 1,
     *      "kegiatan_id": 1,
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
     *
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to create penugasan",
     *  "data": "Error message details"
     * }
     */
    public function store(StoreMitraPenugasanRequest $request)
    {
        try {
            $penugasan = $this->mitraService->createPenugasan(
                $request->validated(),
                Auth::id()
            );

            return $this->success($penugasan, 'Penugasan created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create penugasan', $e->getMessage(), 500);
        }
    }
}
