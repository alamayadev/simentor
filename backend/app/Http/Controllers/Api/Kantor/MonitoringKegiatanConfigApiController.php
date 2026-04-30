<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\DetilConfiguration;
use App\Models\MonitoringKegiatanConfig;
use Illuminate\Http\Request;

class MonitoringKegiatanConfigApiController extends BaseApiController
{
    protected $monitoringService;

    public function __construct(\App\Services\MonitoringKegiatanService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Get kegiatan options using Spatie QueryBuilder.
     *
     * @group Kantor - Monitoring Kegiatan - Config
     * @authenticated
     *
     * @queryParam filter[fungsi] string Filter by fungsi. Contoh: "Fungsi Statistik Sosial"
     * @queryParam filter[tahun] string Filter by tahun. Contoh: "2024"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Kegiatan options retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama": "Kegiatan A",
     *       "fungsi": "Fungsi Statistik Sosial",
     *       "tahun": "2024"
     *     }
     *   ]
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function kegiatanOptions()
    {
        $options = $this->monitoringService->getKegiatanOptionsWithQuery();
        return $this->success($options, 'Kegiatan options retrieved successfully');
    }

    /**
     * Display a listing of the resource.
     *
     * @group Kantor - Monitoring Kegiatan - Config
     * @authenticated
     *
     * @queryParam per_page int Jumlah data per halaman. Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam fungsi string Filter by fungsi. Contoh: "Fungsi Statistik Sosial"
     * @queryParam kegiatan_id string Filter by kegiatan_id. Contoh: "01"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "fungsi": "Fungsi Statistik Sosial",
     *       "kegiatan_id": "01",
     *       "detil_configurations": [1, 2, 3], // IDs stored in the config
     *       "created_at": "2024-01-15T00:00:00.000000Z",
     *       "updated_at": "2024-01-15T00:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 10,
     *     "has_more": false,
     *     "count": 1
     *   },
     *   "links": {
     *     "next_cursor": null,
     *     "next_page_url": null,
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://localhost/api/kantor/monitoring-kegiatan-config"
     *   },
     *   "pagination_info": {
     *     "total_page": 1,
     *     "total_records": 1
     *   }
     * }
     * @response 500 {
     *   "message": "Server Error"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);
        $query   = MonitoringKegiatanConfig::query();

        // Apply filters
        if ($request->filled('fungsi')) {
            $query->where('fungsi', 'like', '%' . $request->fungsi . '%');
        }

        if ($request->filled('kegiatan_id')) {
            $query->where('kegiatan_id', $request->kegiatan_id);
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $query->count();

        $configs = $query->with(['detilConfigurations', 'kegiatan'])->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras     = [
            'pagination_info' => [
                'total_page'    => $totalPages,
                'total_records' => $totalRecords,
            ],
        ];

        return $this->success($configs, 'Data retrieved successfully', 200, $extras);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @group Kantor - Monitoring Kegiatan - Config
     * @authenticated
     *
     * @bodyParam fungsi string required Fungsi statistik. Contoh: "Fungsi Statistik Sosial"
     * @bodyParam kegiatan_id string required ID kegiatan. Contoh: "01"
     * @bodyParam detil_configurations array ID detil configurations. Contoh: [1, 2, 3]
     * @bodyParam detil_configurations.* int ID detil configuration yang valid.
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Configuration created successfully",
     *   "data": {
     *     "id": 1,
     *     "fungsi": "Fungsi Statistik Sosial",
     *     "kegiatan_id": "01",
     *     "detil_configurations": [1, 2, 3],
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "fungsi": ["The fungsi field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "Server Error"
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'fungsi'                 => 'required|string',
            'kegiatan_id'            => 'required|string',
            'detil_configurations'   => 'nullable|array',
            'detil_configurations.*' => 'exists:detil_configurations,id',
        ]);

        $config = MonitoringKegiatanConfig::create($validated);

        // Sync the configurations if provided
        if (isset($validated['detil_configurations'])) {
            $config->detilConfigurations()->sync($validated['detil_configurations']);
        }

        return $this->success($config, 'Configuration created successfully', 201);
    }

    /**
     * Display the specified resource.
     *
     * @group Kantor - Monitoring Kegiatan - Config
     * @authenticated
     *
     * @urlParam id int required ID dari konfigurasi monitoring kegiatan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "fungsi": "Fungsi Statistik Sosial",
     *     "kegiatan_id": "01",
     *       "detil_configurations": [1, 2, 3], // IDs stored in the config
     *       "created_at": "2024-01-15T00:00:00.000000Z",
     *       "updated_at": "2024-01-15T00:00:00.000000Z",
     *       "detil_configurations_detail": [ // Full configuration objects
     *         {
     *           "id": 1,
     *           "name": "config_name",
     *           "field": {
     *             "name": "field_name",
     *             "label": "Field Label",
     *             "type": "text"
     *           }
     *         }
     *       ]
     *       {
     *         "id": 1,
     *         "name": "config_name",
     *         "field": {
     *           "name": "field_name",
     *           "label": "Field Label",
     *           "type": "text"
     *         }
     *       }
     *     ]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Configuration not found"
     * }
     * @response 500 {
     *   "message": "Server Error"
     * }
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        $config = MonitoringKegiatanConfig::with(['detilConfigurations', 'kegiatan'])->find($id);

        if (! $config) {
            return $this->error('Configuration not found', null, 404);
        }

        return $this->success($config, 'Data retrieved successfully');
    }

    /**
     * Update the specified resource in storage.
     *
     * @group Kantor - Monitoring Kegiatan - Config
     * @authenticated
     *
     * @urlParam id int required ID dari konfigurasi monitoring kegiatan yang akan diupdate. Contoh: 1
     * @bodyParam fungsi string Fungsi statistik. Contoh: "Fungsi Statistik Sosial"
     * @bodyParam kegiatan_id string ID kegiatan. Contoh: "01"
     * @bodyParam detil_configurations array ID detil configurations. Contoh: [1, 2, 3]
     * @bodyParam detil_configurations.* int ID detil configuration yang valid.
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Configuration updated successfully",
     *   "data": {
     *     "id": 1,
     *     "fungsi": "Fungsi Statistik Sosial",
     *     "kegiatan_id": "01",
     *     "detil_configurations": [1, 2, 3],
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-16T00:00:00.000000Z"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Configuration not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "fungsi": ["The fungsi field is required."]
     *   }
     * }
     * @response 500 {
     *   "message": "Server Error"
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $config = MonitoringKegiatanConfig::find($id);

        if (! $config) {
            return $this->error('Configuration not found', null, 404);
        }

        $validated = $request->validate([
            'fungsi'                 => 'string',
            'kegiatan_id'            => 'string',
            'detil_configurations'   => 'nullable|array',
            'detil_configurations.*' => 'exists:detil_configurations,id',
        ]);

        $config->update($validated);

        // Sync the configurations if provided
        if (isset($validated['detil_configurations'])) {
            $config->detilConfigurations()->sync($validated['detil_configurations']);
        }

        return $this->success($config, 'Configuration updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @group Kantor - Monitoring Kegiatan - Config
     * @authenticated
     *
     * @urlParam id int required ID dari konfigurasi monitoring kegiatan yang akan dihapus. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Configuration deleted successfully",
     *   "data": null
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Configuration not found"
     * }
     * @response 500 {
     *   "message": "Server Error"
     * }
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $config = MonitoringKegiatanConfig::find($id);

        if (! $config) {
            return $this->error('Configuration not found', null, 404);
        }

        $config->delete();

        return $this->success(null, 'Configuration deleted successfully');
    }

    /**
     * Get available detil configurations for a specific foreign key.
     *
     * @group Kantor - Monitoring Kegiatan - Config
     * @authenticated
     *
     * @queryParam foreign_key_value string required Nilai kunci asing. Contoh: "01"
     * @queryParam foreign_key_type string required Tipe kunci asing. Harus salah satu dari: kegiatan_id, kec_id, desa_id. Contoh: "kegiatan_id"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Available configurations retrieved successfully",
     *   "data": []
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "foreign_key_value": ["The foreign key value field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Server Error"
     * }
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAvailableDetilConfigurations(Request $request)
    {
        $request->validate([
            'foreign_key_value' => 'required',
            'foreign_key_type'  => 'required|in:kegiatan_id,kec_id,desa_id',
        ]);

        $configs = DetilConfiguration::getByForeignKey(
            $request->foreign_key_value,
            $request->foreign_key_type
        );

        return $this->success($configs, 'Available configurations retrieved successfully');
    }
}
