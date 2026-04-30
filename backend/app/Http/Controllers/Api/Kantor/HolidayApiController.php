<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Holiday;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;

class HolidayApiController extends BaseApiController
{
    /**
     * Menampilkan daftar hari libur dengan pagination dan filtering.
     *
     * Endpoint ini mengembalikan daftar hari libur dengan kemampuan filtering
     * berdasarkan deskripsi, tahun, dan tanggal. Mendukung pagination dan sorting.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Holidays
     * @authenticated
     *
     * @queryParam filter[deskripsi] string Filter berdasarkan deskripsi. Contoh: tahun baru
     * @queryParam filter[year] int Filter berdasarkan tahun. Contoh: 2024
     * @queryParam filter[search] string Pencarian global di deskripsi. Contoh: tahun
     * @queryParam sort string Kolom untuk sorting (default: -tanggal). Contoh: -tanggal,deskripsi
     * @queryParam per_page int Jumlah item per halaman (default: 15). Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Holidays retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "tanggal": "2024-01-01",
     *       "deskripsi": "Tahun Baru Masehi",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
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
     *     "path": "http://localhost:8000/api/kantor/holidays"
     *   },
     *   "pagination_info": {
     *     "total_page": 1,
     *     "total_records": 1
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

        $perPage = $request->get('per_page', 15);

        // Get base query for counting
        $baseQuery = Holiday::query();

        // Apply same filters for counting
        if ($request->get('filter.deskripsi')) {
            $baseQuery->where('deskripsi', 'LIKE', '%' . $request->get('filter.deskripsi') . '%');
        }

        if ($request->get('filter.year')) {
            $baseQuery->whereYear('tanggal', $request->get('filter.year'));
        }

        if ($request->get('filter.search')) {
            $baseQuery->where('deskripsi', 'LIKE', '%' . $request->get('filter.search') . '%');
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $baseQuery->count();

        // Use Spatie QueryBuilder for filtering and sorting
        $holidays = QueryBuilder::for(Holiday::class)
            ->allowedFilters([
                'deskripsi',
                AllowedFilter::callback('year', function ($query, $value) {
                    if (is_numeric($value)) {
                        $query->whereYear('tanggal', $value);
                    }
                }),
                AllowedFilter::callback('search', function ($query, $value) {
                    $query->where('deskripsi', 'LIKE', "%{$value}%");
                }),
            ])
            ->allowedSorts(['id', 'tanggal', 'deskripsi'])
            ->defaultSort('-tanggal')
            ->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($holidays, 'Holidays retrieved successfully', 200, $extras);
    }

    /**
     * Menyimpan hari libur baru.
     *
     * Endpoint ini membuat hari libur baru.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Holidays
     * @authenticated
     *
     * @bodyParam tanggal date required Tanggal hari libur. Contoh: 2024-01-01
     * @bodyParam deskripsi string required Deskripsi hari libur. Contoh: Tahun Baru Masehi
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Holiday created successfully",
     *   "data": {
     *     "id": 1,
     *     "tanggal": "2024-01-01",
     *     "deskripsi": "Tahun Baru Masehi",
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
     *     "tanggal": [
     *       "The tanggal field is required."
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
            'tanggal' => 'required|date',
            'deskripsi' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors(), 422);
        }

        $holiday = Holiday::create($request->all());

        return $this->success($holiday, 'Holiday created successfully', 201);
    }

    /**
     * Menampilkan detail hari libur.
     *
     * Endpoint ini mengembalikan detail hari libur tertentu.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Holidays
     * @authenticated
     *
     * @urlParam id int required ID hari libur. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Holiday retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "tanggal": "2024-01-01",
     *     "deskripsi": "Tahun Baru Masehi",
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
     *   "message": "Holiday not found"
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

        $holiday = Holiday::find($id);

        if (!$holiday) {
            return $this->error('Holiday not found', null, 404);
        }

        return $this->success($holiday, 'Holiday retrieved successfully');
    }

    /**
     * Memperbarui data hari libur.
     *
     * Endpoint ini memperbarui data hari libur yang sudah ada.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Holidays
     * @authenticated
     *
     * @urlParam id int required ID hari libur. Contoh: 1
     *
     * @bodyParam tanggal date Tanggal hari libur. Contoh: 2024-01-01
     * @bodyParam deskripsi string Deskripsi hari libur. Contoh: Tahun Baru Masehi
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Holiday updated successfully",
     *   "data": {
     *     "id": 1,
     *     "tanggal": "2024-01-01",
     *     "deskripsi": "Tahun Baru Masehi",
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
     *   "message": "Holiday not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "deskripsi": [
     *       "The deskripsi field is required."
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

        $holiday = Holiday::find($id);

        if (!$holiday) {
            return $this->error('Holiday not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'tanggal' => 'sometimes|required|date',
            'deskripsi' => 'sometimes|required|string|max:255',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors(), 422);
        }

        $holiday->update($request->all());

        return $this->success($holiday, 'Holiday updated successfully');
    }

    /**
     * Menghapus data hari libur.
     *
     * Endpoint ini menghapus data hari libur yang sudah ada.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Holidays
     * @authenticated
     *
     * @urlParam id int required ID hari libur. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Holiday deleted successfully",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Holiday not found"
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

        $holiday = Holiday::find($id);

        if (!$holiday) {
            return $this->error('Holiday not found', null, 404);
        }

        $holiday->delete();

        return $this->success(null, 'Holiday deleted successfully');
    }
}
