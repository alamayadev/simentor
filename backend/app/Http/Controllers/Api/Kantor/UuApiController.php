<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\BaseApiController;
use App\Models\Uu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class UuApiController extends BaseApiController
{
    /**
     * Display a listing of the resource.
     *
     * @group UU
     *
     * @authenticated
     *
     * @queryParam sort string Sort field. Default: -id. Example: nama
     * @queryParam per_page int Number of items per page. Default is 15.
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[nama] string Filter by nama. Example: Undang-Undang
     * @queryParam filter[jenis] string Filter by jenis. Example: UU
     *
     * @response 200 {
     *   "success": true,
     *   "message": "UU retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "jenis": "UU",
     *       "nama": "Undang-Undang Nomor 1 Tahun 2023",
     *       "detil": "Tentang Administrasi Pemerintahan",
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
     *     "path": "http://localhost:8000/api/kantor/uu"
     *   },
     *   "pagination_info": {
     *     "total_page": 1,
     *     "total_records": 1
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to retrieve UU",
     *   "errors": "Error message details"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $perPage = $request->get('per_page', 15);

            // Get base query for counting
            $baseQuery = Uu::query();
            
            // Apply same filters for counting
            if ($request->get('filter.nama')) {
                $baseQuery->where('nama', $request->get('filter.nama'));
            }
            
            if ($request->get('filter.jenis')) {
                $baseQuery->where('jenis', $request->get('filter.jenis'));
            }
            
            if ($request->get('filter.detil')) {
                $baseQuery->where('detil', $request->get('filter.detil'));
            }

            // Get total count for pagination info (separate query for performance)
            $totalRecords = $baseQuery->count();

            $uu = \Spatie\QueryBuilder\QueryBuilder::for(Uu::class)
                ->allowedFilters([
                    'nama',
                    'jenis',
                    'detil',
                ])
                ->defaultSort('-id')
                ->allowedSorts(['id', 'nama', 'jenis', 'created_at'])
                ->fastPaginate($perPage);

            // Add pagination extras including total_page
            $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
            $extras = [
                'pagination_info' => [
                    'total_page' => $totalPages,
                    'total_records' => $totalRecords,
                ]
            ];

            return $this->success($uu, 'UU retrieved successfully', 200, $extras);
        } catch (\Exception $e) {
            return $this->error('Failed to retrieve UU', $e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @group UU
     *
     * @authenticated
     *
     * @bodyParam jenis string required Jenis UU. Example: UU
     * @bodyParam nama string required Nama UU. Example: Undang-Undang Nomor 1 Tahun 2023
     * @bodyParam detil string required Detil UU. Example: Tentang Administrasi Pemerintahan
     *
     * @response 201 {
     *   "success": true,
     *   "message": "UU created successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis": "UU",
     *     "nama": "Undang-Undang Nomor 1 Tahun 2023",
     *     "detil": "Tentang Administrasi Pemerintahan",
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "jenis": ["The jenis field is required."],
     *     "nama": ["The nama field is required."],
     *     "detil": ["The detil field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create UU",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'jenis' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'detil' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $uu = Uu::create($request->all());

            return $this->success($uu, 'UU created successfully', 201);
        } catch (\Exception $e) {
            return $this->error('Failed to create UU', $e->getMessage(), 500);
        }
    }

    /**
     * Display the specified resource.
     *
     * @group UU
     *
     * @authenticated
     *
     * @urlParam id int required The ID of the UU. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "UU retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis": "UU",
     *     "nama": "Undang-Undang Nomor 1 Tahun 2023",
     *     "detil": "Tentang Administrasi Pemerintahan",
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "UU not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $uu = Uu::find($id);

            if (! $uu) {
                return $this->error('UU not found', null, 404);
            }

            return $this->success($uu, 'UU retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('UU not found', null, 404);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @group UU
     *
     * @authenticated
     *
     * @urlParam id int required The ID of the UU. Example: 1
     *
     * @bodyParam jenis string required Jenis UU. Example: UU
     * @bodyParam nama string required Nama UU. Example: Undang-Undang Nomor 1 Tahun 2023
     * @bodyParam detil string required Detil UU. Example: Tentang Administrasi Pemerintahan
     *
     * @response 200 {
     *   "success": true,
     *   "message": "UU updated successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis": "UU",
     *     "nama": "Undang-Undang Nomor 1 Tahun 2023",
     *     "detil": "Tentang Administrasi Pemerintahan",
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "UU not found"
     * }
     *
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "jenis": ["The jenis field is required."],
     *     "nama": ["The nama field is required."],
     *     "detil": ["The detil field is required."]
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
            'jenis' => 'required|string|max:255',
            'nama' => 'required|string|max:255',
            'detil' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $uu = Uu::find($id);
            if (!$uu) {
                 return $this->error('UU not found', null, 404);
            }
            $uu->update($request->all());

            return $this->success($uu, 'UU updated successfully');
        } catch (\Exception $e) {
            return $this->error('UU not found', null, 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @group UU
     *
     * @authenticated
     *
     * @urlParam id int required The ID of the UU. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "UU deleted successfully",
     *   "data": null
     * }
     *
     * @response 404 {
     *   "success": false,
     *   "message": "UU not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $uu = Uu::find($id);
            if (!$uu) {
                return $this->error('UU not found', null, 404);
            }
            $uu->delete();

            return $this->success(null, 'UU deleted successfully');
        } catch (\Exception $e) {
            return $this->error('UU not found', null, 404);
        }
    }
}
