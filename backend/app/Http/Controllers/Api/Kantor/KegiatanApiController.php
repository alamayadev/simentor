<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Kantor\Kegiatan\IndexKegiatanRequest;
use App\Http\Requests\Kantor\Kegiatan\StoreKegiatanRequest;
use App\Http\Requests\Kantor\Kegiatan\UpdateKegiatanRequest;
use App\Models\Kegiatan;
use App\Services\KegiatanService;
use Illuminate\Http\Request;

/**
 * @group Kantor - Kegiatan
 *
 * APIs for managing activities (Kegiatan).
 */
class KegiatanApiController extends BaseApiController
{
    protected KegiatanService $kegiatanService;

    public function __construct(KegiatanService $kegiatanService)
    {
        $this->kegiatanService = $kegiatanService;
    }

    /**
     * List Kegiatan
     *
     * Retrieve a list of activities with optional filtering and pagination.
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int The number of items per page. Example: 20
     * @queryParam year string Filter by year. Example: 2023
     * @queryParam search string Search by name or code. Example: Census
     * @queryParam fungsi string Filter by function. Example: IPDS
     * @queryParam jenis_kegiatan string Filter by activity type. Example: PENGOLAHAN
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "tahun": "2023",
     *          "fungsi": "IPDS",
     *          "kode_kegiatan": "12345678901",
     *          "nama": "Annual Survey",
     *          "tgl_mulai": "2023-01-01",
     *          "tgl_selesai": "2023-12-31",
     *          "jenis_kegiatan": "PENGOLAHAN",
     *          "jml_ptgs": 5,
     *          "volume": 100,
     *          "satuan": "Documents",
     *          "rate_pcl": 50000,
     *          "rate_pml": 60000,
     *          "rate_entri": 45000,
     *          "status": "aktif",
     *          "created_at": "2023-01-01T00:00:00.000000Z",
     *          "updated_at": "2023-01-01T00:00:00.000000Z"
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
    public function index(IndexKegiatanRequest $request)
    {
        $result = $this->kegiatanService->listKegiatan($request->validated());

        return $this->success(
            $result['data'],
            'Data retrieved successfully',
            200
        );
    }

    /**
     * Create Kegiatan
     *
     * Create a new activity.
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Kegiatan created successfully",
     *  "data": {
     *      "id": 1,
     *      "tahun": "2023",
     *      "fungsi": "IPDS",
     *      "kode_kegiatan": "12345678901",
     *      "nama": "Annual Survey",
     *      "tgl_mulai": "2023-01-01",
     *      "tgl_selesai": "2023-12-31",
     *      "jenis_kegiatan": "PENGOLAHAN",
     *      "jml_ptgs": 5,
     *      "volume": 100,
     *      "satuan": "Documents",
     *      "rate_pcl": 50000,
     *      "rate_pml": 60000,
     *      "rate_entri": 45000,
     *      "status": "aktif",
     *      "created_at": "2023-01-01T00:00:00.000000Z",
     *      "updated_at": "2023-01-01T00:00:00.000000Z"
     *  }
     * }
     */
    public function store(StoreKegiatanRequest $request)
    {
        $kegiatan = $this->kegiatanService->createKegiatan($request->validated());

        return $this->success($kegiatan, 'Kegiatan created successfully', 201);
    }

    /**
     * Show Kegiatan
     *
     * Retrieve details of a specific activity.
     *
     * @urlParam id int required The ID of the activity. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "tahun": "2023",
     *      "fungsi": "IPDS",
     *      "kode_kegiatan": "12345678901",
     *      "nama": "Annual Survey",
     *      "tgl_mulai": "2023-01-01",
     *      "tgl_selesai": "2023-12-31",
     *      "jenis_kegiatan": "PENGOLAHAN",
     *      "jml_ptgs": 5,
     *      "volume": 100,
     *      "satuan": "Documents",
     *      "rate_pcl": 50000,
     *      "rate_pml": 60000,
     *      "rate_entri": 45000,
     *      "status": "aktif",
     *      "jml_penugasan": 10,
     *      "penugasan_sum_volume": 500,
     *      "created_at": "2023-01-01T00:00:00.000000Z",
     *      "updated_at": "2023-01-01T00:00:00.000000Z"
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Kegiatan not found",
     *  "data": null
     * }
     */
    public function show($id)
    {
        $kegiatan = Kegiatan::withCount('penugasan as jml_penugasan')
            ->withSum('penugasan', 'volume')
            ->find($id);

        if (! $kegiatan) {
            return $this->error('Kegiatan not found', null, 404);
        }

        return $this->success($kegiatan, 'Data retrieved successfully');
    }

    /**
     * Update Kegiatan
     *
     * Update an existing activity.
     *
     * @urlParam id int required The ID of the activity. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Kegiatan updated successfully",
     *  "data": {
     *      "id": 1,
     *      "tahun": "2023",
     *      "fungsi": "IPDS",
     *      "kode_kegiatan": "12345678901",
     *      "nama": "Annual Survey Updated",
     *      "tgl_mulai": "2023-01-01",
     *      "tgl_selesai": "2023-12-31",
     *      "jenis_kegiatan": "PENGOLAHAN",
     *      "jml_ptgs": 5,
     *      "volume": 100,
     *      "satuan": "Documents",
     *      "rate_pcl": 50000,
     *      "rate_pml": 60000,
     *      "rate_entri": 45000,
     *      "status": "aktif",
     *      "created_at": "2023-01-01T00:00:00.000000Z",
     *      "updated_at": "2023-01-02T00:00:00.000000Z"
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Kegiatan not found",
     *  "data": null
     * }
     */
    public function update(UpdateKegiatanRequest $request, $id)
    {
        $kegiatan = Kegiatan::find($id);

        if (! $kegiatan) {
            return $this->error('Kegiatan not found', null, 404);
        }

        $this->kegiatanService->updateKegiatan($kegiatan, $request->validated());

        return $this->success($kegiatan, 'Kegiatan updated successfully');
    }

    /**
     * Delete Kegiatan
     *
     * Delete an activity.
     *
     * @urlParam id int required The ID of the activity. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Kegiatan berhasil dihapus",
     *  "data": null
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Kegiatan not found",
     *  "data": null
     * }
     */
    public function destroy($id)
    {
        $kegiatan = Kegiatan::find($id);

        if (! $kegiatan) {
            return $this->error('Kegiatan not found', null, 404);
        }

        $this->kegiatanService->deleteKegiatan($kegiatan);

        return $this->success(null, 'Kegiatan berhasil dihapus');
    }

    /**
     * Get Filter List
     *
     * Retrieve available filters for activities.
     *
     * @response {
     *  "success": true,
     *  "message": "Filters retrieved successfully",
     *  "data": {
     *      "years": ["2022", "2023"],
     *      "functions": ["IPDS", "Sosial"],
     *      "types": ["PENGOLAHAN", "PENGUMPULAN DATA"]
     *  }
     * }
     */
    public function getFilterList()
    {
        $data = $this->kegiatanService->getFilterList();
        return $this->success($data, 'Filters retrieved successfully');
    }

    /**
     * Get Calendar Data
     *
     * Retrieve activity data formatted for calendar view.
     *
     * @response {
     *  "success": true,
     *  "message": "Calendar data retrieved successfully",
     *  "data": [
     *      {
     *          "title": "Annual Survey",
     *          "start": "2023-01-01",
     *          "end": "2023-12-31",
     *          "color": "#ff0000"
     *      }
     *  ]
     * }
     */
    public function calendar()
    {
        $data = $this->kegiatanService->getCalendarData();
        return $this->success($data, 'Calendar data retrieved successfully');
    }

    /**
     * Get Form Options
     *
     * Retrieve options for activity forms.
     *
     * @response {
     *  "success": true,
     *  "message": "Form options retrieved successfully",
     *  "data": {
     *      "functions": ["IPDS", "Sosial"],
     *      "types": ["PENGOLAHAN", "PENGUMPULAN DATA"]
     *  }
     * }
     */
    public function formOptions()
    {
        $data = $this->kegiatanService->getFormOptions();
        return $this->success($data, 'Form options retrieved successfully');
    }

    /**
     * Get Statistics
     *
     * Retrieve statistics about activities.
     *
     * @response {
     *  "success": true,
     *  "message": "Statistics retrieved successfully",
     *  "data": {
     *      "total": 100,
     *      "active": 90,
     *      "completed": 10
     *  }
     * }
     */
    public function statistics()
    {
        $data = $this->kegiatanService->getStatistics();
        return $this->success($data, 'Statistics retrieved successfully');
    }

    /**
     * Get Kegiatan By Year
     *
     * Retrieve activities filtered by year.
     *
     * @queryParam tahun string The year to filter by. Defaults to current year. Example: 2023
     *
     * @response {
     *  "success": true,
     *  "message": "Data retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "tahun": "2023",
     *          "nama": "Annual Survey"
     *      }
     *  ]
     * }
     */
    public function kegiatanByYear(Request $request)
    {
        $tahun    = $request->get('tahun', date('Y'));
        $kegiatan = $this->kegiatanService->getKegiatanByYear($tahun);

        return $this->success($kegiatan, 'Data retrieved successfully');
    }
}
