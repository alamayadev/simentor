<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Setting;
use App\Models\Pegawai;
use App\Services\CacheService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class SettingApiController extends BaseApiController
{
    /**
     * Menampilkan daftar pengaturan yang dikelompokkan berdasarkan grup.
     *
     * Endpoint ini mengembalikan daftar pengaturan yang dikelompokkan berdasarkan nilai grup.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Settings
     * @authenticated
     *
     * @queryParam search string Search term untuk mencari berdasarkan key atau value. Contoh: tahun
     * @queryParam filter[grup] int Filter berdasarkan grup pengaturan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Settings retrieved successfully",
     *   "data": {
     *     "1": [
     *       {
     *         "id": 1,
     *         "tahun": "2024",
     *         "key": "app_name",
     *         "value": "Sistem Informasi Kantor",
     *         "grup": 1,
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ],
     *     "2": [
     *       {
     *         "id": 2,
     *         "tahun": "2024",
     *         "key": "max_cuti",
     *         "value": "12",
     *         "grup": 2,
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $search = $request->get('search');
        $grupFilter = $request->input('filter.grup');

        // Build the query
        $settingsQuery = Setting::query();

        // Add search functionality for key and value
        if ($search) {
            $settingsQuery->where(function ($query) use ($search) {
                $query->where('key', 'LIKE', "%{$search}%")
                      ->orWhere('value', 'LIKE', "%{$search}%");
            });
        }

        // Filter by grup if provided
        if ($grupFilter) {
            $settingsQuery->where('grup', $grupFilter);
        }

        // Get all settings and group them by grup, sorted by grup name ASC
        $settings = $settingsQuery->orderBy('grup', 'asc')->get();
        $groupedSettings = $settings->groupBy('grup');

        return $this->success($groupedSettings, 'Settings retrieved successfully');
    }

    /**
     * Menyimpan pengaturan baru.
     *
     * Endpoint ini membuat pengaturan baru.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Settings
     * @authenticated
     *
     * @bodyParam tahun string required Tahun pengaturan. Contoh: 2024
     * @bodyParam key string required Kunci pengaturan. Contoh: app_name
     * @bodyParam value string required Nilai pengaturan. Contoh: Sistem Informasi Kantor
     * @bodyParam grup int required Grup pengaturan. Contoh: 1
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Setting created successfully",
     *   "data": {
     *     "id": 1,
     *     "tahun": "2024",
     *     "key": "app_name",
     *     "value": "Sistem Informasi Kantor",
     *     "grup": 1,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "key": [
     *       "The key field is required."
     *     ]
     *   }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $validator = Validator::make($request->all(), [
            'tahun' => 'required|string|max:4',
            'key' => 'required|string|max:255|unique:settings',
            'value' => 'required|string',
            'grup' => 'required|integer',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors(), 422);
        }

        $setting = Setting::create($request->all());

        // PERFORMANCE FIX: Clear settings cache after creating new setting
        CacheService::forget(CacheService::key('settings', 'all'));
        CacheService::forget(CacheService::key('settings', 'grup_list'));

        return $this->success($setting, 'Setting created successfully', 201);
    }

    /**
     * Menampilkan detail pengaturan.
     *
     * Endpoint ini mengembalikan detail pengaturan tertentu.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Settings
     * @authenticated
     *
     * @urlParam id int required ID pengaturan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Setting retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "tahun": "2024",
     *     "key": "app_name",
     *     "value": "Sistem Informasi Kantor",
     *     "grup": 1,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Setting not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $setting = Setting::find($id);

        if (!$setting) {
            return $this->error('Setting not found', null, 404);
        }

        return $this->success($setting, 'Setting retrieved successfully');
    }

    /**
     * Memperbarui data pengaturan.
     *
     * Endpoint ini memperbarui data pengaturan yang sudah ada.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Settings
     * @authenticated
     *
     * @urlParam id int required ID pengaturan. Contoh: 1
     *
     * @bodyParam tahun string Tahun pengaturan. Contoh: 2024
     * @bodyParam value string Nilai pengaturan. Contoh: Sistem Informasi Kantor
     * @bodyParam grup int Grup pengaturan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Setting updated successfully",
     *   "data": {
     *     "id": 1,
     *     "tahun": "2024",
     *     "key": "app_name",
     *     "value": "Sistem Informasi Kantor Updated",
     *     "grup": 1,
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T01:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Setting not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "value": [
     *       "The value field is required."
     *     ]
     *   }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $setting = Setting::find($id);

        if (!$setting) {
            return $this->error('Setting not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'tahun' => 'sometimes|required|string|max:4',
            'value' => 'sometimes|required|string',
            'grup' => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors(), 422);
        }

        // Exclude key from update
        $updateData = $request->except('key');
        
        // Check if this is PPK or KEPALA_KANTOR update to auto-update corresponding NIP
        if ($setting->key === 'PPK' && isset($updateData['value'])) {
            $this->updateCorrespondingNip('PPK', 'NIP_PPK', $updateData['value']);
        } elseif ($setting->key === 'KEPALA_KANTOR' && isset($updateData['value'])) {
            $this->updateCorrespondingNip('KEPALA_KANTOR', 'NIP_KEPALA', $updateData['value']);
        }

        $setting->update($updateData);

        return $this->success($setting, 'Setting updated successfully');
    }

    /**
     * Menghapus data pengaturan.
     *
     * Endpoint ini menghapus data pengaturan yang sudah ada.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Settings
     * @authenticated
     *
     * @urlParam id int required ID pengaturan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Setting deleted successfully",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Setting not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $setting = Setting::find($id);

        if (!$setting) {
            return $this->error('Setting not found', null, 404);
        }

        $setting->delete();

        // PERFORMANCE FIX: Clear settings cache after deleting setting
        CacheService::forget(CacheService::key('settings', 'all'));
        CacheService::forget(CacheService::key('settings', 'grup_list'));
        CacheService::forget(CacheService::key('settings', $setting->key));

        return $this->success(null, 'Setting deleted successfully');
    }

    /**
     * Mendapatkan daftar nilai unik untuk kolom grup dari model Setting.
     *
     * Endpoint ini mengembalikan daftar nilai unik untuk kolom grup dari semua pengaturan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Settings
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     1,
     *     2,
     *     3
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function getGrupList()
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE FIX: Cache grup list for 1 hour
        $cacheKey = CacheService::key('settings', 'grup_list');

        $grupList = CacheService::remember($cacheKey, 3600, function () {
            return Setting::select('grup')
                ->whereNotNull('grup')
                ->distinct()
                ->orderBy('grup')
                ->pluck('grup');
        });

        return $this->success($grupList, 'Data retrieved successfully');
    }

    /**
     * Mendapatkan daftar pegawai dengan kelas > 8.
     *
     * Endpoint ini mengembalikan daftar pegawai dengan kelas lebih besar dari 8,
     * menampilkan hanya nama dan NIP.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Settings
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "nama": "Robert Ronytua Pardosi, S.Si, MAB",
     *       "nip": "19710426 199211 1 001"
     *     },
     *     {
     *       "nama": "Ir. Mina Nur Aini M.M.",
     *       "nip": "19680802 199302 2 001"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\Response
     */
    public function officers()
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $officers = Pegawai::where('kelas', '>', 8)
            ->whereNull('status')  // Add condition for NULL status
            ->select(['nama', 'nip'])
            ->get();

        return $this->success($officers, 'Data retrieved successfully');
    }

    /**
     * Mendapatkan pengaturan berdasarkan key.
     *
     * Endpoint ini mengembalikan pengaturan berdasarkan key tertentu.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Settings
     * @authenticated
     *
     * @urlParam key string required Kunci pengaturan. Contoh: app_name
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "tahun": "2024",
     *     "key": "app_name",
     *     "value": "Sistem Informasi Kantor",
     *     "grup": "sistem",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Setting not found"
     * }
     *
     * @param  string  $key
     * @return \Illuminate\Http\Response
     */
    public function getByKey($key)
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE FIX: Cache individual setting by key for 1 hour
        $cacheKey = CacheService::key('settings', $key);

        $setting = CacheService::remember($cacheKey, 3600, function () use ($key) {
            return Setting::where('key', $key)->first();
        });

        if (!$setting) {
            return $this->error('Setting not found', null, 404);
        }

        return $this->success($setting, 'Data retrieved successfully');
    }

    /**
     * Update both name and NIP values for officer settings.
     *
     * @param  string  $nameKey
     * @param  string  $nipKey
     * @param  string  $officerName
     * @return void
     */
    private function updateCorrespondingNip($nameKey, $nipKey, $officerName)
    {
        try {
            // Find the officer by name
            $officer = Pegawai::where('nama', $officerName)
                ->where('kelas', '>', 8)
                ->whereNull('status')
                ->first();

            if ($officer && !empty($officer->nip)) {
                // Find and update the corresponding NIP setting
                $nipSetting = Setting::where('key', $nipKey)->first();
                if ($nipSetting) {
                    $nipSetting->update(['value' => $officer->nip]);
                }
                
                // Also ensure the name setting is updated (in case it wasn't already)
                $nameSetting = Setting::where('key', $nameKey)->first();
                if ($nameSetting && $nameSetting->value !== $officerName) {
                    $nameSetting->update(['value' => $officerName]);
                }
            }
        } catch (\Exception $e) {
            // Log error but don't fail the main update
            \Log::error("Failed to update NIP for key {$nipKey}: " . $e->getMessage());
        }
    }

    
}
