<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Exports\MonitoringKegiatanExport;
use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Kantor\MonitoringKegiatan\IndexMonitoringRequest;
use App\Http\Requests\Kantor\MonitoringKegiatan\StoreMonitoringRequest;
use App\Http\Requests\Kantor\MonitoringKegiatan\UpdateMonitoringRequest;
use App\Models\Kegiatan;
use App\Models\MonitoringKegiatan;
use App\Services\MonitoringKegiatanService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

/**
 * @group Kantor - Monitoring Kegiatan
 *
 * APIs for monitoring activities (Monitoring Kegiatan).
 */
class MonitoringKegiatanApiController extends BaseApiController
{
    protected MonitoringKegiatanService $monitoringService;

    public function __construct(MonitoringKegiatanService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * List Monitoring
     *
     * Retrieve a list of monitoring data with optional filtering and pagination.
     *
     * @queryParam page int The page number. Example: 1
     * @queryParam per_page int The number of items per page. Example: 20
     * @queryParam kegiatan_id int Filter by Activity ID. Example: 1
     * @queryParam date string Filter by date. Example: 2023-01-01
     *
     * @response {
     *  "success": true,
     *  "message": "Monitoring kegiatan retrieved successfully",
     *  "data": [
     *      {
     *          "id": 1,
     *          "kegiatan_id": 1,
     *          "tanggal": "2023-01-01",
     *          "deskripsi": "Monitoring visit 1",
     *          "status": "completed",
     *          "petugas": "John Doe",
     *          "lokasi": "Desa A",
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
    public function index(IndexMonitoringRequest $request)
    {
        $monitoring = $this->monitoringService->listMonitoring($request->validated());
        return $this->success($monitoring, 'Monitoring kegiatan retrieved successfully', 200);
    }

    /**
     * Create Monitoring
     *
     * Create a new monitoring entry.
     *
     * @response 201 {
     *  "success": true,
     *  "message": "Monitoring kegiatan created successfully",
     *  "data": {
     *      "id": 1,
     *      "kegiatan_id": 1,
     *      "tanggal": "2023-01-01",
     *      "deskripsi": "Monitoring visit 1",
     *      "status": "pending",
     *      "petugas": "John Doe",
     *      "lokasi": "Desa A",
     *      "created_at": "2023-01-01T00:00:00.000000Z",
     *      "updated_at": "2023-01-01T00:00:00.000000Z"
     *  }
     * }
     *
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to create monitoring kegiatan: Error details",
     *  "data": null
     * }
     */
    public function store(StoreMonitoringRequest $request)
    {
        try {
            $monitoring = $this->monitoringService->createMonitoring($request->validated());
            return $this->success($monitoring, 'Monitoring kegiatan created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create monitoring kegiatan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Show Monitoring
     *
     * Retrieve details of a specific monitoring entry.
     *
     * @urlParam id string required The ID of the monitoring entry. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Monitoring kegiatan retrieved successfully",
     *  "data": {
     *      "id": 1,
     *      "kegiatan_id": 1,
     *      "tanggal": "2023-01-01",
     *      "deskripsi": "Monitoring visit 1",
     *      "status": "pending",
     *      "kegiatan": {
     *          "id": 1,
     *          "nama": "Annual Survey"
     *      },
     *      "kecamatan": {
     *          "id": "010",
     *          "nama": "Kecamatan A"
     *      },
     *      "desa": {
     *          "id": "001",
     *          "nama": "Desa A"
     *      },
     *      "monitoringConfig": {
     *          "id": 1,
     *          "detilConfigurations": []
     *      }
     *  }
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "Monitoring kegiatan not found",
     *  "data": null
     * }
     */
    public function show(string $id)
    {
        try {
            $monitoring = MonitoringKegiatan::with(['kegiatan', 'kecamatan', 'desa', 'monitoringConfig.detilConfigurations'])
                ->findOrFail($id);
            return $this->success($monitoring, 'Monitoring kegiatan retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Monitoring kegiatan not found', null, 404);
        }
    }

    /**
     * Update Monitoring
     *
     * Update an existing monitoring entry.
     *
     * @urlParam id string required The ID of the monitoring entry. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Monitoring kegiatan updated successfully",
     *  "data": {
     *      "id": 1,
     *      "kegiatan_id": 1,
     *      "tanggal": "2023-01-02",
     *      "deskripsi": "Updated description",
     *      "status": "completed"
     *  }
     * }
     *
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to update monitoring kegiatan: Error details",
     *  "data": null
     * }
     */
    public function update(UpdateMonitoringRequest $request, string $id)
    {
        try {
            $monitoring = MonitoringKegiatan::findOrFail($id);
            $this->monitoringService->updateMonitoring($monitoring, $request->validated());
            return $this->success($monitoring, 'Monitoring kegiatan updated successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to update monitoring kegiatan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Delete Monitoring
     *
     * Delete a monitoring entry.
     *
     * @urlParam id string required The ID of the monitoring entry. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Monitoring kegiatan deleted successfully",
     *  "data": null
     * }
     *
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to delete monitoring kegiatan: Error details",
     *  "data": null
     * }
     */
    public function destroy(string $id)
    {
        try {
            $monitoring = MonitoringKegiatan::findOrFail($id);
            $this->monitoringService->deleteMonitoring($monitoring);
            return $this->success(null, 'Monitoring kegiatan deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Failed to delete monitoring kegiatan: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Download Excel
     *
     * Download monitoring data as an Excel file.
     *
     * @queryParam kegiatan_id string required The ID of the activity. Example: 1
     *
     * @response 200 {
     *  "binary content": "Excel file content"
     * }
     *
     * @response 404 {
     *  "success": false,
     *  "message": "No monitoring data found for the specified kegiatan_id",
     *  "data": null
     * }
     *
     * @response 500 {
     *  "success": false,
     *  "message": "Failed to generate Excel file: Error details",
     *  "data": null
     * }
     */
    public function download(Request $request)
    {
        $validated = $request->validate(['kegiatan_id' => 'required|string']);

        try {
            $count = MonitoringKegiatan::where('kegiatan_id', $validated['kegiatan_id'])->count();
            if ($count === 0) {
                return $this->error('No monitoring data found for the specified kegiatan_id', null, 404);
            }

            $kegiatan     = Kegiatan::find($validated['kegiatan_id']);
            $kegiatanName = $kegiatan ? str_replace([' ', '/', '\\'], '_', $kegiatan->nama) : 'Unknown';
            $fileName     = "monitoring_kegiatan_{$validated['kegiatan_id']}_{$kegiatanName}_" . now()->format('Ymd_His') . ".xlsx";

            return Excel::download(new MonitoringKegiatanExport($validated['kegiatan_id']), $fileName, \Maatwebsite\Excel\Excel::XLSX);
        } catch (\Exception $e) {
            return $this->error('Failed to generate Excel file: ' . $e->getMessage(), null, 500);
        }
    }

    /**
     * Get Kegiatan Options
     *
     * Retrieve activity options, optionally filtered by function.
     *
     * @queryParam fungsi string The function to filter by. Example: IPDS
     *
     * @response {
     *  "success": true,
     *  "message": "Kegiatan options retrieved successfully",
     *  "data": [
     *      {"id": 1, "nama": "Annual Survey"}
     *  ]
     * }
     */
    public function kegiatanOptions(Request $request)
    {
        $kegiatan = $this->monitoringService->getKegiatanOptions($request->input('fungsi'));
        return $this->success($kegiatan, 'Kegiatan options retrieved successfully');
    }

    /**
     * Get All Kegiatan Options
     *
     * Retrieve all available activity options.
     *
     * @response {
     *  "success": true,
     *  "message": "All Kegiatan options retrieved successfully",
     *  "data": [
     *      {"id": 1, "nama": "Annual Survey"},
     *      {"id": 2, "nama": "Monthly Report"}
     *  ]
     * }
     */
    public function allKegiatanOptions()
    {
        $kegiatan = $this->monitoringService->getAllKegiatanOptions();
        return $this->success($kegiatan, 'All Kegiatan options retrieved successfully');
    }

    /**
     * Get Petugas Options
     *
     * Retrieve available officers (Petugas) for a specific activity.
     *
     * @queryParam kegiatan_id int required The ID of the activity. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Petugas options retrieved successfully",
     *  "data": [
     *      {"id": 1, "nama": "John Officer", "role": "PCL"}
     *  ]
     * }
     */
    public function petugasOptions(Request $request)
    {
        $request->validate(['kegiatan_id' => 'required|exists:kegiatan,id']);
        $petugas = $this->monitoringService->getPetugasOptions((int) $request->kegiatan_id);
        return $this->success($petugas, 'Petugas options retrieved successfully');
    }

    /**
     * Get SLS Options
     *
     * Retrieve SLS (Satuan Lingkungan Setempat) options for a specific village.
     *
     * @queryParam desa_id string required The ID of the village (Desa). Example: 001
     *
     * @response {
     *  "success": true,
     *  "message": "SLS options retrieved successfully",
     *  "data": [
     *      {"id": "00100", "nama": "00100 - RT 01 RW 01"}
     *  ]
     * }
     */
    public function slsOptions(Request $request)
    {
        $sls = $this->monitoringService->getSlsOptions($request->desa_id);
        return $this->success($sls, 'SLS options retrieved successfully');
    }

    /**
     * Get Blok Options
     *
     * Retrieve Census Block options for a specific village.
     *
     * @queryParam desa_id string required The ID of the village (Desa). Example: 001
     *
     * @response {
     *  "success": true,
     *  "message": "Blok options retrieved successfully",
     *  "data": [
     *      {"id": "001B", "nama": "001B - Blok Sensus 001B"}
     *  ]
     * }
     */
    public function blokOptions(Request $request)
    {
        $blok = $this->monitoringService->getBlokOptions($request->desa_id);
        return $this->success($blok, 'Blok options retrieved successfully');
    }

    /**
     * Get Kecamatan Options
     *
     * Retrieve all Kecamatan options.
     *
     * @response {
     *  "success": true,
     *  "message": "Kecamatan options retrieved successfully",
     *  "data": [
     *      {"id": "010", "nama": "Kecamatan A"}
     *  ]
     * }
     */
    public function kecOptions()
    {
        $kec = $this->monitoringService->getKecOptions();
        return $this->success($kec, 'Kecamatan options retrieved successfully');
    }

    /**
     * Get Desa Options
     *
     * Retrieve Desa options for a specific Kecamatan.
     *
     * @queryParam kec_id string required The ID of the Kecamatan. Example: 010
     *
     * @response {
     *  "success": true,
     *  "message": "Desa options retrieved successfully",
     *  "data": [
     *      {"id": "001", "nama": "Desa A"}
     *  ]
     * }
     */
    public function desaOptions(Request $request)
    {
        $request->validate(['kec_id' => 'required']);
        $desa = $this->monitoringService->getDesaOptions($request->kec_id);
        return $this->success($desa, 'Desa options retrieved successfully');
    }

    /**
     * Get Detil Configuration
     *
     * Retrieve configuration details for monitoring.
     *
     * @queryParam monitoring_kegiatan_config_id int required The configuration ID. Example: 1
     *
     * @response {
     *  "success": true,
     *  "message": "Detil configuration retrieved successfully",
     *  "data": [
     *      {"id": 1, "label": "Progress", "type": "number"}
     *  ]
     * }
     */
    public function getDetilConfiguration(Request $request)
    {
        $request->validate(['monitoring_kegiatan_config_id' => 'required']);
        $configs = $this->monitoringService->getDetilConfiguration((int) $request->monitoring_kegiatan_config_id);
        return $this->success($configs, 'Detil configuration retrieved successfully');
    }

    /**
     * Get Filters Options
     *
     * Retrieve options used for filtering lists.
     *
     * @queryParam fungsi string Optional filter by function. Example: IPDS
     *
     * @response {
     *  "success": true,
     *  "message": "Filter options retrieved successfully",
     *  "data": {
     *      "years": ["2022", "2023"],
     *      "functions": ["IPDS"]
     *  }
     * }
     */
    public function filtersOptions(Request $request)
    {
        $data = $this->monitoringService->getFiltersOptions($request->fungsi);
        return $this->success($data, 'Filter options retrieved successfully');
    }

    /**
     * Get Pengawas Options
     *
     * Retrieve Supervisor options with optional search.
     *
     * @queryParam search string Optional search term. Example: Doe
     *
     * @response {
     *  "success": true,
     *  "message": "Pengawas options retrieved successfully",
     *  "data": [
     *      {"id": 1, "nama": "John Supervisor"}
     *  ]
     * }
     */
    public function pengawasOptions(Request $request)
    {
        $pengawas = $this->monitoringService->getPengawasOptions($request->input('search'));
        return $this->success($pengawas, 'Pengawas options retrieved successfully');
    }

    /**
     * Get Supervisor Options
     *
     * Retrieve Supervisor options (alias for pengawas) with optional search.
     *
     * @queryParam search string Optional search term. Example: Doe
     *
     * @response {
     *  "success": true,
     *  "message": "Supervisor options retrieved successfully",
     *  "data": [
     *      {"id": 1, "nama": "Jane Supervisor"}
     *  ]
     * }
     */
    public function supervisorOptions(Request $request)
    {
        $supervisors = $this->monitoringService->getSupervisorOptions($request->input('search'));
        return $this->success($supervisors, 'Supervisor options retrieved successfully');
    }
}
