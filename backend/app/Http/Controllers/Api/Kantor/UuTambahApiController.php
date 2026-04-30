<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\UuTambah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UuTambahApiController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     *
     * @group UU Tambahan
     *
     * @authenticated
     *
     * @queryParam sort string Field to sort by. Default is 'id'.
     * @queryParam order string Sort order ('asc' or 'desc'). Default is 'desc'.
     * @queryParam per_page int Number of items per page. Default is 15.
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     *
     * @response 200 {
     *   "success": true,
     *   "message": "UU Tambahan retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "jenis_surat": "Surat Keputusan",
     *       "surat_id": "1",
     *       "item": "Pasal 5 Ayat 2",
     *       "created_at": "2024-01-15T00:00:00.000000Z",
     *       "updated_at": "2024-01-15T00:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 15,
     *     "has_more": false,
     *     "count": 1
     *   },
     *   "links": {
     *     "next_cursor": null,
     *     "next_page_url": null,
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://localhost:8000/api/kantor/uu-tambahan"
     *   },
     *   "pagination_info": {
     *     "total_page": 1,
     *     "total_records": 1
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to retrieve UU Tambahan",
     *   "errors": "Error message details"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $sort = $request->get('sort', 'id');
            $order = $request->get('order', 'desc');
            $perPage = $request->get('per_page', 15);

            // Get total count for pagination info (separate query for performance)
            $totalRecords = UuTambah::count();

            $uuTambah = UuTambah::orderBy($sort, $order)->fastPaginate($perPage);

            // Add pagination extras including total_page
            $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
            $extras = [
                'pagination_info' => [
                    'total_page' => $totalPages,
                    'total_records' => $totalRecords,
                ]
            ];

            return $this->success($uuTambah, 'UU Tambahan retrieved successfully', 200, $extras);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve UU Tambahan', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @group UU Tambahan
     *
     * @authenticated
     *
     * @bodyParam jenis_surat string required Jenis Surat. Example: Surat Keputusan
     * @bodyParam surat_id string required ID Surat. Example: 1
     * @bodyParam item string required Item UU Tambahan. Example: Pasal 5 Ayat 2
     *
     * @response 201 {
     *   "success": true,
     *   "message": "UU Tambahan created successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis_surat": "Surat Keputusan",
     *     "surat_id": "1",
     *     "item": "Pasal 5 Ayat 2",
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "jenis_surat": ["The jenis surat field is required."],
     *     "surat_id": ["The surat id field is required."],
     *     "item": ["The item field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create UU Tambahan",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis_surat' => 'required|string|max:255',
            'surat_id' => 'required|string|max:255',
            'item' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $uuTambah = UuTambah::create($request->all());

            return $this->success($uuTambah, 'UU Tambahan created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create UU Tambahan', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @group UU Tambahan
     *
     * @authenticated
     *
     * @urlParam id int required The ID of the UU Tambahan. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "UU Tambahan retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis_surat": "Surat Keputusan",
     *     "surat_id": "1",
     *     "item": "Pasal 5 Ayat 2",
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "UU Tambahan not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $uuTambah = UuTambah::find($id);

            if (! $uuTambah) {
                return $this->error('UU Tambahan not found', null, 404);
            }

            return $this->success($uuTambah, 'UU Tambahan retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('UU Tambahan not found', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @group UU Tambahan
     *
     * @authenticated
     *
     * @urlParam id int required The ID of the UU Tambahan. Example: 1
     *
     * @bodyParam jenis_surat string required Jenis Surat. Example: Surat Keputusan
     * @bodyParam surat_id string required ID Surat. Example: 1
     * @bodyParam item string required Item UU Tambahan. Example: Pasal 5 Ayat 2
     *
     * @response 200 {
     *   "success": true,
     *   "message": "UU Tambahan updated successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis_surat": "Surat Keputusan",
     *     "surat_id": "1",
     *     "item": "Pasal 5 Ayat 2",
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "UU Tambahan not found"
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "jenis_surat": ["The jenis surat field is required."],
     *     "surat_id": ["The surat id field is required."],
     *     "item": ["The item field is required."]
     *   }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'jenis_surat' => 'required|string|max:255',
            'surat_id' => 'required|string|max:255',
            'item' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $uuTambah = UuTambah::find($id);
            if (!$uuTambah) {
                return $this->error('UU Tambahan not found', null, 404);
            }
            $uuTambah->update($request->all());

            return $this->success($uuTambah, 'UU Tambahan updated successfully');
        } catch (\Exception $e) {
            return $this->error('UU Tambahan not found', null, 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @group UU Tambahan
     *
     * @authenticated
     *
     * @urlParam id int required The ID of the UU Tambahan. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "UU Tambahan deleted successfully",
     *   "data": null
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "UU Tambahan not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $uuTambah = UuTambah::find($id);
            if (!$uuTambah) {
                return $this->error('UU Tambahan not found', null, 404);
            }
            $uuTambah->delete();

            return $this->success(null, 'UU Tambahan deleted successfully');
        } catch (\Exception $e) {
            return $this->error('UU Tambahan not found', null, 404);
        }
    }
}
