<?php
namespace App\Http\Controllers\Api\Ipds;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AssetITMaintenanceSchedule;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Asset IT Maintenance Schedule Management
 *
 * Endpoints for managing maintenance schedules for IT assets.
 *
 * @group IPDS - Asset Maintenance Schedule
 */
class AssetITMaintenanceScheduleApiController extends BaseApiController
{
    /**
     * Display a listing of the maintenance schedules.
     *
     * @return JsonResponse
     *
     * @group IPDS - Asset Maintenance Schedule
     *
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 15). Contoh: 15
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam asset_id int Filter berdasarkan ID asset. Contoh: 1
     * @queryParam responsible_team string Filter berdasarkan tim yang bertanggung jawab. Contoh: IT Support
     * @queryParam sort_by string Kolom untuk sorting (default: created_at). Contoh: next_maintenance
     * @queryParam sort_dir string Arah sorting ASC/DESC (default: DESC). Contoh: ASC
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "asset_id": 1,
     *       "next_maintenance": "2024-12-31",
     *       "responsible_team": "IT Support",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z",
     *       "asset": {
     *         "id": 1,
     *         "kode_asset": "AS-001",
     *         "type": "hardware",
     *         "category": "Server",
     *         "brand": "Dell",
     *         "model": "PowerEdge R740",
     *         "serial_number": "SN123456789",
     *         "name": "Web Server",
     *         "location": "Data Center Jakarta",
     *         "status": "active",
     *         "purchase_date": "2023-01-15",
     *         "warranty_expiry": "2026-01-15",
     *         "delivery_date": "2023-01-10"
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 15,
     *     "has_more": true,
     *     "count": 15
     *   },
     *   "links": {
     *     "next_cursor": "...",
     *     "next_page_url": "http://127.0.0.1:8000/api/ipds/asset-it-maintenance-schedule?cursor=...",
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://127.0.0.1:8000/api/ipds/asset-it-maintenance-schedule"
     *   },
     *   "pagination_info": {
     *     "total_page": 4,
     *     "total_records": 55
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     */
    public function index(): JsonResponse
    {
        $perPage = 15;
        
        // Prepare query for maintenance schedules
        $scheduleQuery = AssetITMaintenanceSchedule::with('asset');
        
        // Get total count for pagination info (separate query for performance)
        $totalRecords = $scheduleQuery->count();

        $schedules = $scheduleQuery->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($schedules, 'Data retrieved successfully', 200, $extras);
    }

    /**
     * Store a newly created maintenance schedule in storage.
     *
     * @param Request \$request
     * @return JsonResponse
     *
     * @group IPDS - Asset Maintenance Schedule
     *
     * @authenticated
     *
     * @bodyParam asset_id int required ID asset yang akan dijadwalkan maintenance. Contoh: 1
     * @bodyParam next_maintenance date required Tanggal maintenance berikutnya (format: YYYY-MM-DD). Contoh: 2024-12-31
     * @bodyParam responsible_team string required Tim yang bertanggung jawab atas maintenance. Contoh: IT Support
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Schedule created successfully",
     *   "data": {
     *     "id": 1,
     *     "asset_id": 1,
     *     "next_maintenance": "2024-12-31",
     *     "responsible_team": "IT Support",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "asset_id": [
     *       "The asset id field is required."
     *     ]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'asset_id'         => 'required|integer|exists:asset_it,id',
            'next_maintenance' => 'required|date',
            'responsible_team' => 'required|string|max:255',
        ]);

        $schedule = AssetITMaintenanceSchedule::create($validatedData);

        return $this->success($schedule, 'Schedule created successfully', 201);
    }

    /**
     * Display the specified maintenance schedule.
     *
     * @param AssetITMaintenanceSchedule \$assetITMaintenanceSchedule
     * @return JsonResponse
     *
     * @group IPDS - Asset Maintenance Schedule
     *
     * @authenticated
     *
     * @urlParam id int required ID jadwal maintenance. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "asset_id": 1,
     *     "next_maintenance": "2024-12-31",
     *     "responsible_team": "IT Support",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z",
     *     "asset": {
     *       "id": 1,
     *       "kode_asset": "AS-001",
     *       "type": "hardware",
     *       "category": "Server",
     *       "brand": "Dell",
     *       "model": "PowerEdge R740",
     *       "serial_number": "SN123456789",
     *       "name": "Web Server",
     *       "location": "Data Center Jakarta",
     *       "status": "active",
     *       "purchase_date": "2023-01-15",
     *       "warranty_expiry": "2026-01-15",
     *       "delivery_date": "2023-01-10"
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Maintenance schedule not found"
     * }
     */
    public function show(AssetITMaintenanceSchedule $assetITMaintenanceSchedule): JsonResponse
    {
        $schedule = AssetITMaintenanceSchedule::with('asset')->find($assetITMaintenanceSchedule->id);

        if (! $schedule) {
            return $this->error('Maintenance schedule not found', null, 404);
        }

        return $this->success($schedule, 'Data retrieved successfully');
    }

    /**
     * Update the specified maintenance schedule in storage.
     *
     * @param Request \$request
     * @param AssetITMaintenanceSchedule \$assetITMaintenanceSchedule
     * @return JsonResponse
     *
     * @group IPDS - Asset Maintenance Schedule
     *
     * @authenticated
     *
     * @urlParam id int required ID jadwal maintenance. Contoh: 1
     *
     * @bodyParam asset_id int ID asset yang akan dijadwalkan maintenance. Contoh: 1
     * @bodyParam next_maintenance date Tanggal maintenance berikutnya (format: YYYY-MM-DD). Contoh: 2024-12-31
     * @bodyParam responsible_team string Tim yang bertanggung jawab atas maintenance. Contoh: IT Support
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Schedule updated successfully",
     *   "data": {
     *     "id": 1,
     *     "asset_id": 1,
     *     "next_maintenance": "2024-12-31",
     *     "responsible_team": "IT Support",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Maintenance schedule not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "next_maintenance": [
     *       "The next maintenance field is required."
     *     ]
     *   }
     * }
     */
    public function update(Request $request, AssetITMaintenanceSchedule $assetITMaintenanceSchedule): JsonResponse
    {
        $validatedData = $request->validate([
            'asset_id'         => 'sometimes|required|integer|exists:asset_it,id',
            'next_maintenance' => 'sometimes|required|date',
            'responsible_team' => 'sometimes|required|string|max:255',
        ]);

        $assetITMaintenanceSchedule->update($validatedData);

        return $this->success($assetITMaintenanceSchedule, 'Schedule updated successfully');
    }

    /**
     * Remove the specified maintenance schedule from storage.
     *
     * @param AssetITMaintenanceSchedule \$assetITMaintenanceSchedule
     * @return JsonResponse
     *
     * @group IPDS - Asset Maintenance Schedule
     *
     * @authenticated
     *
     * @urlParam id int required ID jadwal maintenance. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Maintenance schedule deleted successfully",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Maintenance schedule not found"
     * }
     */
    public function destroy(AssetITMaintenanceSchedule $assetITMaintenanceSchedule): JsonResponse
    {
        $assetITMaintenanceSchedule->delete();

        return $this->success(null, 'Maintenance schedule deleted successfully');
    }
}
