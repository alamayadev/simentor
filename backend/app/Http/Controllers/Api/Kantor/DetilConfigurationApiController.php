<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\DetilConfiguration;
use Illuminate\Http\Request;

class DetilConfigurationApiController extends BaseApiController
{
    /**
     * Get available fields based on foreign key.
     *
     * Mengambil field yang tersedia berdasarkan kunci asing.
     * Endpoint ini mengembalikan konfigurasi detail berdasarkan nilai dan tipe kunci asing yang diberikan.
     *
     * @group Monitoring Kegiatan - Config Data Detail
     *
     * @authenticated
     *
     * @queryParam foreign_key_value string required Nilai kunci asing. Contoh: "01"
     * @queryParam foreign_key_type string required Tipe kunci asing. Harus salah satu dari: kegiatan_id, kec_id, desa_id. Contoh: "kegiatan_id"
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Available fields retrieved successfully",
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
    public function getAvailableFields(Request $request)
    {
        $request->validate([
            'foreign_key_value' => 'required',
            'foreign_key_type' => 'required|in:kegiatan_id,kec_id,desa_id',
        ]);

        $configs = DetilConfiguration::getByForeignKey(
            $request->foreign_key_value,
            $request->foreign_key_type
        );

        return $this->success($configs, 'Available fields retrieved successfully');
    }

    /**
     * Store a new Detil Configuration.
     *
     * Membuat konfigurasi detail baru.
     * Endpoint ini memvalidasi input dan membuat entri konfigurasi detail baru dalam database.
     *
     * @group Monitoring Kegiatan - Config Data Detail
     *
     * @authenticated
     *
     * @bodyParam name string required Nama konfigurasi. Contoh: "Konfigurasi Kegiatan"
     * @bodyParam related_table string Tabel terkait opsional. Contoh: "kegiatans"
     * @bodyParam foreign_key string Kunci asing opsional. Harus salah satu dari: kegiatan_id, kec_id, desa_id. Contoh: "kegiatan_id"
     * @bodyParam field array required Detail field konfigurasi.
     * @bodyParam field.name string required Nama field. Contoh: "target"
     * @bodyParam field.label string required Label field. Contoh: "Target"
     * @bodyParam field.source string required Sumber field. Contoh: "custom"
     * @bodyParam field.required boolean Field wajib. Contoh: true
     * @bodyParam field.type string Tipe field untuk field kustom. Harus salah satu dari: text, number, date, enum. Contoh: "number"
     * @bodyParam field.options array Opsi untuk field tipe enum.
     * @bodyParam field.options.* string Opsi enum.
     * @bodyParam is_active boolean Status aktif konfigurasi. Contoh: true
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Configuration created successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Konfigurasi Kegiatan",
     *     "related_table": "kegiatans",
     *     "foreign_key": "kegiatan_id",
     *     "field": {
     *       "name": "target",
     *       "label": "Target",
     *       "source": "custom",
     *       "required": true,
     *       "type": "number"
     *     },
     *     "is_active": true,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."]
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
    public function storeConfiguration(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'related_table' => 'nullable|string',
            'foreign_key' => 'nullable|string|in:kegiatan_id,kec_id,desa_id',
            'field' => 'required|array',
            'field.name' => 'required|string',
            'field.label' => 'required|string',
            'field.required' => 'boolean',
            // For custom fields
            'field.type' => 'required|string|in:text,number,date,enum',
            'field.options' => 'array|required_if:field.type,enum', // Required only for enum type
            'field.options.*' => 'string',
            'is_active' => 'boolean',
        ]);

        // Set field.source to always be 'custom'
        $validated['field']['source'] = 'custom';

        $config = DetilConfiguration::create($validated);

        return $this->success($config, 'Configuration created successfully', 201);
    }

    /**
     * Get all Detil Configurations.
     *
     * Mengambil semua konfigurasi detail.
     * Endpoint ini mengembalikan koleksi semua konfigurasi detail yang tersedia.
     *
     * @group Monitoring Kegiatan - Config Data Detail
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "name": "Konfigurasi Kegiatan",
     *       "related_table": "kegiatans",
     *       "foreign_key": "kegiatan_id",
     *       "field": {
     *         "name": "target",
     *         "label": "Target",
     *         "source": "custom",
     *         "required": true,
     *         "type": "number"
     *       },
     *       "is_active": true,
     *       "created_at": "2024-01-15T00:00:00.000000Z",
     *       "updated_at": "2024-01-15T00:00:00.000000Z"
     *     }
     *   ]
     * }
     * @response 500 {
     *   "message": "Server Error"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        return $this->success(DetilConfiguration::all(), 'Data retrieved successfully');
    }

    /**
     * Get a specific Detil Configuration.
     *
     * Mengambil konfigurasi detail spesifik berdasarkan ID.
     * Endpoint ini mengembalikan data konfigurasi detail berdasarkan ID yang diberikan.
     *
     * @group Monitoring Kegiatan - Config Data Detail
     *
     * @authenticated
     *
     * @urlParam id int required ID dari konfigurasi detail. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Konfigurasi Kegiatan",
     *     "related_table": "kegiatans",
     *     "foreign_key": "kegiatan_id",
     *     "field": {
     *       "name": "target",
     *       "label": "Target",
     *       "source": "custom",
     *       "required": true,
     *       "type": "number"
     *     },
     *     "is_active": true,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
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
        $config = DetilConfiguration::find($id);

        if (!$config) {
            return $this->error('Configuration not found', null, 404);
        }

        return $this->success($config, 'Data retrieved successfully');
    }

    /**
     * Update a Detil Configuration.
     *
     * Memperbarui konfigurasi detail berdasarkan ID.
     * Endpoint ini memvalidasi input dan memperbarui entri konfigurasi detail yang ada.
     *
     * @group Monitoring Kegiatan - Config Data Detail
     *
     * @authenticated
     *
     * @urlParam id int required ID dari konfigurasi detail yang akan diperbarui. Contoh: 1
     * @bodyParam name string Nama konfigurasi. Contoh: "Konfigurasi Kegiatan"
     * @bodyParam related_table string Tabel terkait opsional. Contoh: "kegiatans"
     * @bodyParam foreign_key string Kunci asing opsional. Harus salah satu dari: kegiatan_id, kec_id, desa_id. Contoh: "kegiatan_id"
     * @bodyParam field array Detail field konfigurasi.
     * @bodyParam field.name string Nama field. Contoh: "target"
     * @bodyParam field.label string Label field. Contoh: "Target"
     * @bodyParam field.source string Sumber field. Contoh: "custom"
     * @bodyParam field.required boolean Field wajib. Contoh: true
     * @bodyParam field.type string Tipe field untuk field kustom. Harus salah satu dari: text, number, date, enum. Contoh: "number"
     * @bodyParam field.options array Opsi untuk field tipe enum.
     * @bodyParam field.options.* string Opsi enum.
     * @bodyParam is_active boolean Status aktif konfigurasi. Contoh: true
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Configuration updated successfully",
     *   "data": {
     *     "id": 1,
     *     "name": "Konfigurasi Kegiatan",
     *     "related_table": "kegiatans",
     *     "foreign_key": "kegiatan_id",
     *     "field": {
     *       "name": "target",
     *       "label": "Target",
     *       "source": "custom",
     *       "required": true,
     *       "type": "number"
     *     },
     *     "is_active": true,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Configuration not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "name": ["The name field is required."]
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
        $config = DetilConfiguration::find($id);

        if (!$config) {
            return $this->error('Configuration not found', null, 404);
        }

        $validated = $request->validate([
            'name' => 'string',
            'related_table' => 'nullable|string',
            'foreign_key' => 'nullable|string|in:kegiatan_id,kec_id,desa_id',
            'field' => 'array',
            'field.name' => 'string',
            'field.label' => 'string',
            'field.required' => 'boolean',
            // For custom fields
            'field.type' => 'string|in:text,number,date,enum',
            'field.options' => 'array|required_if:field.type,enum', // Required only for enum type
            'field.options.*' => 'string',
            'is_active' => 'boolean',
        ]);

        // Set field.source to always be 'custom'
        $validated['field']['source'] = 'custom';

        $config->update($validated);

        return $this->success($config, 'Configuration updated successfully');
    }

    /**
     * Delete a Detil Configuration.
     *
     * Menghapus konfigurasi detail berdasarkan ID.
     * Endpoint ini menghapus entri konfigurasi detail dari database.
     *
     * @group Monitoring Kegiatan - Config Data Detail
     *
     * @authenticated
     *
     * @urlParam id int required ID dari konfigurasi detail yang akan dihapus. Contoh: 1
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
        $config = DetilConfiguration::find($id);

        if (!$config) {
            return $this->error('Configuration not found', null, 404);
        }

        $config->delete();

        return $this->success(null, 'Configuration deleted successfully');
    }
}
