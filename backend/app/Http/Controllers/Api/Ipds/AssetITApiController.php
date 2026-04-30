<?php

namespace App\Http\Controllers\Api\Ipds;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\AssetIT;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

/**
 * Asset IT Management
 *
 * Endpoints for managing IT assets including hardware, software, and network equipment.
 *
 * @group IPDS - Asset IT
 */
class AssetITApiController extends BaseApiController
{
    /**
     * Display a listing of the assets.
     *
     * @return JsonResponse
     *
     * @group IPDS - Asset IT
     *
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 15). Contoh: 15
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[category] string Filter berdasarkan kategori asset. Contoh: Server
     * @queryParam filter[object_category] string Filter berdasarkan object kategori asset. Contoh: hardware
     * @queryParam filter[type] string Filter berdasarkan type asset (hardware, software, network). Contoh: hardware
     * @queryParam filter[status] string Filter berdasarkan status asset. Contoh: active
     * @queryParam sort string Sort field (default: -created_at). Example: kode_asset
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "kode_asset": "AS-001",
     *       "type": "hardware",
     *       "category": "Server",
     *       "brand": "Dell",
     *       "model": "PowerEdge R740",
     *       "serial_number": "SN123456789",
     *       "name": "Web Server",
     *       "license_key": null,
     *       "device": null,
     *       "ip_address": "192.168.1.100",
     *       "location": "Data Center Jakarta",
     *       "status": "active",
     *       "assigned_to": "Infrastructure Team",
     *       "purchase_date": "2023-01-15",
     *       "warranty_expiry": "2026-01-15",
     *       "expiry_date": null,
     *       "delivery_date": "2023-01-10",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z",
     *       "maintenance_schedules": [
     *         {
     *           "id": 1,
     *           "asset_id": 1,
     *           "next_maintenance": "2024-12-31",
     *           "responsible_team": "IT Support",
     *           "created_at": "2024-01-01T00:00:00.000000Z",
     *           "updated_at": "2024-01-01T00:00:00.000000Z"
     *         }
     *       ]
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 15,
     *     "has_more": true,
     *     "count": 15
     *   },
     *   "links": {
     *     "next_cursor": "...",
     *     "next_page_url": "http://127.0.0.1:8000/api/ipds/asset-it?cursor=...",
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://127.0.0.1:8000/api/ipds/asset-it"
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
        
        // Prepare query for assets
        $assetQuery = AssetIT::with('maintenanceSchedules');
        
        // Get total count for pagination info (separate query for performance)
        $totalRecords = $assetQuery->count();

        $assets = \Spatie\QueryBuilder\QueryBuilder::for($assetQuery)
            ->allowedFilters([
                \Spatie\QueryBuilder\AllowedFilter::partial('category'),
                \Spatie\QueryBuilder\AllowedFilter::partial('object_category', 'type'),
                \Spatie\QueryBuilder\AllowedFilter::partial('type'),
                \Spatie\QueryBuilder\AllowedFilter::partial('status'),
            ])
            ->defaultSort('id')
            ->allowedSorts(['id'])
            ->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($assets, 'Data retrieved successfully', 200, $extras);
    }

    /**
     * Store a newly created asset in storage.
     *
     * @param Request \$request
     * @return JsonResponse
     *
     * @group IPDS - Asset IT
     *
     * @authenticated
     *
     * @bodyParam kode_asset string required Kode unik asset. Contoh: AS-001
     * @bodyParam type string required Type asset (hardware, software, network). Contoh: hardware
     * @bodyParam category string required Kategori asset. Contoh: Server
     * @bodyParam brand string Merek asset. Contoh: Dell
     * @bodyParam model string Model asset. Contoh: PowerEdge R740
     * @bodyParam serial_number string Nomor seri asset. Contoh: SN123456789
     * @bodyParam name string Nama asset. Contoh: Web Server
     * @bodyParam license_key string Kunci lisensi (untuk software). Contoh: ABCD-EFGH-IJKL-MNOP
     * @bodyParam device string Perangkat terkait. Contoh: Switch
     * @bodyParam ip_address string Alamat IP (untuk perangkat jaringan). Contoh: 192.168.1.100
     * @bodyParam location string Lokasi fisik asset. Contoh: Data Center Jakarta
     * @bodyParam status string required Status asset (active, in-use, maintenance, inactive). Contoh: active
     * @bodyParam assigned_to string Pihak yang ditugaskan. Contoh: Infrastructure Team
     * @bodyParam purchase_date date Tanggal pembelian (format: YYYY-MM-DD). Contoh: 2023-01-15
     * @bodyParam warranty_expiry date Tanggal kadaluarsa garansi (format: YYYY-MM-DD). Contoh: 2026-01-15
     * @bodyParam expiry_date date Tanggal kadaluarsa asset (format: YYYY-MM-DD). Contoh: 2027-01-15
     * @bodyParam delivery_date date Tanggal pengiriman asset (format: YYYY-MM-DD). Contoh: 2023-01-10
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Asset created successfully",
     *   "data": {
     *     "id": 1,
     *     "kode_asset": "AS-001",
     *     "type": "hardware",
     *     "category": "Server",
     *     "brand": "Dell",
     *     "model": "PowerEdge R740",
     *     "serial_number": "SN123456789",
     *     "name": "Web Server",
     *     "license_key": null,
     *     "device": null,
     *     "ip_address": "192.168.1.100",
     *     "location": "Data Center Jakarta",
     *     "status": "active",
     *     "assigned_to": "Infrastructure Team",
     *     "purchase_date": "2023-01-15",
     *     "warranty_expiry": "2026-01-15",
     *     "expiry_date": null,
     *     "delivery_date": "2023-01-10",
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
     *     "kode_asset": [
     *       "The kode asset field is required."
     *     ]
     *   }
     * }
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'kode_asset' => 'required|string|unique:asset_it,kode_asset',
            'type' => 'required|in:hardware,software,network',
            'category' => 'required|string',
            'brand' => 'nullable|string',
            'model' => 'nullable|string',
            'serial_number' => 'nullable|string',
            'name' => 'nullable|string',
            'license_key' => 'nullable|string',
            'device' => 'nullable|string',
            'ip_address' => 'nullable|string',
            'location' => 'nullable|string',
            'status' => 'required|in:active,in-use,maintenance,inactive',
            'assigned_to' => 'nullable|string',
            'purchase_date' => 'nullable|date',
            'warranty_expiry' => 'nullable|date',
            'expiry_date' => 'nullable|date',
            'delivery_date' => 'nullable|date',
        ]);

        $asset = AssetIT::create($validatedData);

        return $this->success($asset, 'Asset created successfully', 201);
    }

    /**
     * Display the specified asset.
     *
     * @param AssetIT \$assetIT
     * @return JsonResponse
     *
     * @group IPDS - Asset IT
     *
     * @authenticated
     *
     * @urlParam id int required ID asset. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "kode_asset": "AS-001",
     *     "type": "hardware",
     *     "category": "Server",
     *     "brand": "Dell",
     *     "model": "PowerEdge R740",
     *     "serial_number": "SN123456789",
     *     "name": "Web Server",
     *     "license_key": null,
     *     "device": null,
     *     "ip_address": "192.168.1.100",
     *     "location": "Data Center Jakarta",
     *     "status": "active",
     *     "assigned_to": "Infrastructure Team",
     *     "purchase_date": "2023-01-15",
     *     "warranty_expiry": "2026-01-15",
     *     "expiry_date": null,
     *     "delivery_date": "2023-01-10",
     *     "created_at": "2024-01-01T00:00:00.000000Z",
     *     "updated_at": "2024-01-01T00:00:00.000000Z",
     *     "maintenance_schedules": [
     *       {
     *         "id": 1,
     *         "asset_id": 1,
     *         "next_maintenance": "2024-12-31",
     *         "responsible_team": "IT Support",
     *         "created_at": "2024-01-01T00:00:00.000000Z",
     *         "updated_at": "2024-01-01T00:00:00.000000Z"
     *       }
     *     ]
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Asset not found"
     * }
     */
    public function show($id): JsonResponse
    {
        $asset = AssetIT::with('maintenanceSchedules')->find($id);

        if (!$asset) {
            return $this->error('Asset not found', null, 404);
        }

        return $this->success($asset, 'Data retrieved successfully');
    }

    /**
     * Update the specified asset in storage.
     *
     * @param Request \$request
     * @param AssetIT \$assetIT
     * @return JsonResponse
     *
     * @group IPDS - Asset IT
     *
     * @authenticated
     *
     * @urlParam id int required ID asset. Contoh: 1
     *
     * @bodyParam kode_asset string Kode unik asset. Contoh: AS-001
     * @bodyParam type string Type asset (hardware, software, network). Contoh: hardware
     * @bodyParam category string Kategori asset. Contoh: Server
     * @bodyParam brand string Merek asset. Contoh: Dell
     * @bodyParam model string Model asset. Contoh: PowerEdge R740
     * @bodyParam serial_number string Nomor seri asset. Contoh: SN123456789
     * @bodyParam name string Nama asset. Contoh: Web Server
     * @bodyParam license_key string Kunci lisensi (untuk software). Contoh: ABCD-EFGH-IJKL-MNOP
     * @bodyParam device string Perangkat terkait. Contoh: Switch
     * @bodyParam ip_address string Alamat IP (untuk perangkat jaringan). Contoh: 192.168.1.100
     * @bodyParam location string Lokasi fisik asset. Contoh: Data Center Jakarta
     * @bodyParam status string Status asset (active, in-use, maintenance, inactive). Contoh: active
     * @bodyParam assigned_to string Pihak yang ditugaskan. Contoh: Infrastructure Team
     * @bodyParam purchase_date date Tanggal pembelian (format: YYYY-MM-DD). Contoh: 2023-01-15
     * @bodyParam warranty_expiry date Tanggal kadaluarsa garansi (format: YYYY-MM-DD). Contoh: 2026-01-15
     * @bodyParam expiry_date date Tanggal kadaluarsa asset (format: YYYY-MM-DD). Contoh: 2027-01-15
     * @bodyParam delivery_date date Tanggal pengiriman asset (format: YYYY-MM-DD). Contoh: 2023-01-10
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Asset updated successfully",
     *   "data": {
     *     "id": 1,
     *     "kode_asset": "AS-001",
     *     "type": "hardware",
     *     "category": "Server",
     *     "brand": "Dell",
     *     "model": "PowerEdge R740",
     *     "serial_number": "SN123456789",
     *     "name": "Web Server",
     *     "license_key": null,
     *     "device": null,
     *     "ip_address": "192.168.1.100",
     *     "location": "Data Center Jakarta",
     *     "status": "active",
     *     "assigned_to": "Infrastructure Team",
     *     "purchase_date": "2023-01-15",
     *     "warranty_expiry": "2026-01-15",
     *     "expiry_date": null,
     *     "delivery_date": "2023-01-10",
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
     *   "message": "Asset not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "kode_asset": [
     *       "The kode asset has already been taken."
     *     ]
     *   }
     * }
     */
    public function update(Request $request, $id): JsonResponse
    {
        // Find the asset manually
        $assetIT = AssetIT::find($id);

        if (!$assetIT) {
            return $this->error('Asset not found', null, 404);
        }

        $validatedData = $request->validate([
            'kode_asset' => [
                'sometimes',
                'required',
                'string',
                'unique:asset_it,kode_asset,' . $assetIT->id,
            ],
            'type' => 'sometimes|required|in:hardware,software,network',
            'category' => 'sometimes|required|string',
            'brand' => 'sometimes|nullable|string',
            'model' => 'sometimes|nullable|string',
            'serial_number' => 'sometimes|nullable|string',
            'name' => 'sometimes|nullable|string',
            'license_key' => 'sometimes|nullable|string',
            'device' => 'sometimes|nullable|string',
            'ip_address' => 'sometimes|nullable|string',
            'location' => 'sometimes|nullable|string',
            'status' => 'sometimes|required|in:active,in-use,maintenance,inactive',
            'assigned_to' => 'sometimes|nullable|string',
            'purchase_date' => 'sometimes|nullable|date',
            'warranty_expiry' => 'sometimes|nullable|date',
            'expiry_date' => 'sometimes|nullable|date',
            'delivery_date' => 'sometimes|nullable|date',
        ]);

        $assetIT->update($validatedData);

        return $this->success($assetIT, 'Asset updated successfully');
    }

    /**
     * Remove the specified asset from storage.
     *
     * @param AssetIT \$assetIT
     * @return JsonResponse
     *
     * @group IPDS - Asset IT
     *
     * @authenticated
     *
     * @urlParam id int required ID asset. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Asset deleted successfully",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Asset not found"
     * }
     */
    public function destroy($id): JsonResponse
    {
        $assetIT = AssetIT::find($id);

        if (!$assetIT) {
            return $this->error('Asset not found', null, 404);
        }

        $assetIT->delete();

        return $this->success(null, 'Asset deleted successfully');
    }

    /**
     * Get unique filter values for type, status, and category.
     *
     * @return JsonResponse
     *
     * @group IPDS - Asset IT
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Filters retrieved successfully",
     *   "data": {
     *     "type": ["hardware", "software", "network"],
     *     "status": ["active", "in-use", "maintenance", "inactive"],
     *     "category": ["Server", "Workstation", "Printer"]
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     */
    public function filters(): JsonResponse
    {
        $types = AssetIT::distinct()->pluck('type')->filter()->values()->toArray();
        $statuses = AssetIT::distinct()->pluck('status')->filter()->values()->toArray();
        $categories = AssetIT::distinct()->pluck('category')->filter()->values()->toArray();

        $filters = [
            'type' => $types,
            'status' => $statuses,
            'category' => $categories,
        ];

        return $this->success($filters, 'Filters retrieved successfully');
    }

    /**
     * Get asset statistics
     *
     * @return JsonResponse
     *
     * @group IPDS - Asset IT
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Statistics retrieved successfully",
     *   "data": {
     *     "total_aset": 55,
     *     "status_counts": {
     *       "active": 35,
     *       "in-use": 15,
     *       "maintenance": 3,
     *       "inactive": 2
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Tidak terotentikasi"
     * }
     */
    public function statistics(): JsonResponse
    {
        $totalAset = AssetIT::count();

        $statusCounts = AssetIT::select('status')
            ->selectRaw('count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status')
            ->toArray();

        $statistics = [
            'total_aset' => $totalAset,
            'status_counts' => $statusCounts,
        ];

        return $this->success($statistics, 'Statistics retrieved successfully');
    }
}
