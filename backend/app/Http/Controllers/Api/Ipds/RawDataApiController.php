<?php

namespace App\Http\Controllers\Api\Ipds;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\RawData;
use App\Http\Requests\StoreRawDataRequest;
use App\Http\Requests\UpdateRawDataRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

/**
 * Raw Data Management
 *
 * Endpoints for managing raw data files including uploads and metadata.
 *
 * @group IPDS - Raw Data
 */
class RawDataApiController extends BaseApiController
{
    /**
     * Display a listing of raw data records.
     *
     * @return JsonResponse
     *
     * @group IPDS - Raw Data
     *
     * @authenticated
     *
     * @queryParam type string Filter berdasarkan tipe. Contoh: Survei
     * @queryParam per_page int Jumlah item per halaman (default: 15). Contoh: 15
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam fungsi string Filter berdasarkan fungsi. Contoh: Survei
     * @queryParam nama string Filter berdasarkan nama. Contoh: Data Pencacahan
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "type": "Survei",
     *       "fungsi": "Survei",
     *       "nama": "Data Pencacahan",
     *       "keterangan": "Data hasil pencacahan lapangan",
     *       "file": "raw_data/survei_data_1234567890.zip",
     *       "created_at": "2026-01-09T00:00:00.000000Z",
     *       "updated_at": "2026-01-09T00:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 15,
     *     "has_more": true,
     *     "count": 15
     *   },
     *   "links": {
     *     "next_cursor": "...",
     *     "next_page_url": "http://127.0.0.1:8000/api/ipds/raw-datas?cursor=...",
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://127.0.0.1:8000/api/ipds/raw-datas"
     *   },
     *   "pagination_info": {
     *     "total_page": 2,
     *     "total_records": 20
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     *   }
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = $request->query('per_page', 15);

        $rawDataQuery = RawData::query();

        // Use exact match for type to cleanly separate RAW DATA / ARSIP INTERNAL etc.
        if ($request->has('type')) {
            $rawDataQuery->where('type', $request->type);
        }

        if ($request->has('fungsi')) {
            $rawDataQuery->where('fungsi', 'like', '%' . $request->fungsi . '%');
        }

        if ($request->has('nama')) {
            $rawDataQuery->where('nama', 'like', '%' . $request->nama . '%');
        }

        // Count after filters so total_records reflects the filtered set
        $totalRecords = $rawDataQuery->count();

        $rawDatas = $rawDataQuery->fastPaginate($perPage);

        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page'    => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($rawDatas, 'Data retrieved successfully', 200, $extras);
    }

    /**
     * Store a newly created raw data record in storage.
     *
     * @param Request $request
     * @return JsonResponse
     *
     * @group IPDS - Raw Data
     *
     * @authenticated
     *
     * @bodyParam type string required Tipe data. Contoh: Survei
     * @bodyParam fungsi string required Fungsi data. Contoh: Survei
     * @bodyParam nama string required Nama data. Contoh: Data Pencacahan
     * @bodyParam keterangan string Keterangan data. Contoh: Data hasil pencacahan lapangan
     * @bodyParam file file required File data (zip, rar, csv, xlsx). Maksimum 50MB. Contoh: data.zip
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Raw Data created successfully",
     *   "data": {
     *     "id": 1,
     *     "type": "Survei",
     *     "fungsi": "Survei",
     *     "nama": "Data Pencacahan",
     *     "keterangan": "Data hasil pencacahan lapangan",
     *     "file": "raw_data/survei_data_1234567890.zip",
     *     "created_at": "2026-01-09T00:00:00.000000Z",
     *     "updated_at": "2026-01-09T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     *   }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "fungsi": ["The fungsi field is required."],
     *     "nama": ["The nama field is required."],
     *     "file": ["The file field is required.", "The file must be a file of type: zip, rar, csv, xlsx."]
     *   }
     * }
     */
    public function store(StoreRawDataRequest $request): JsonResponse
    {
        $validatedData = $request->validated();

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('raw_data', $fileName, 'direct');

            $validatedData['file'] = $filePath;
        }

        $rawData = RawData::create($validatedData);

        return $this->success($rawData, 'Raw Data created successfully', 201);
    }

    /**
     * Display specified raw data record.
     *
     * @param int $id
     * @return JsonResponse
     *
     * @group IPDS - Raw Data
     *
     * @authenticated
     *
     * @urlParam id int required ID raw data. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "type": "Survei",
     *     "fungsi": "Survei",
     *     "nama": "Data Pencacahan",
     *     "keterangan": "Data hasil pencacahan lapangan",
     *     "file": "raw_data/survei_data_1234567890.zip",
     *     "created_at": "2026-01-09T00:00:00.000000Z",
     *     "updated_at": "2026-01-09T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     *   }
     * @response 404 {
     *   "success": false,
     *   "message": "Raw Data not found"
     *   }
     */
    public function show($id): JsonResponse
    {
        $rawData = RawData::find($id);

        if (!$rawData) {
            return $this->error('Raw Data not found', null, 404);
        }

        return $this->success($rawData, 'Data retrieved successfully');
    }

    /**
     * Update specified raw data record in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     *
     * @group IPDS - Raw Data
     *
     * @authenticated
     *
     * @urlParam id int required ID raw data. Contoh: 1
     *
     * @bodyParam type string Tipe data. Contoh: Survei
     * @bodyParam fungsi string Fungsi data. Contoh: Survei
     * @bodyParam nama string Nama data. Contoh: Data Pencacahan
     * @bodyParam keterangan string Keterangan data. Contoh: Data hasil pencacahan lapangan
     * @bodyParam file file File data (zip, rar, csv, xlsx). Maksimum 50MB. Contoh: data.zip
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Raw Data updated successfully",
     *   "data": {
     *     "id": 1,
     *     "type": "Survei",
     *     "fungsi": "Survei",
     *     "nama": "Data Pencacahan",
     *     "keterangan": "Data hasil pencacahan lapangan",
     *     "file": "raw_data/survei_data_1234567890.zip",
     *     "created_at": "2026-01-09T00:00:00.000000Z",
     *     "updated_at": "2026-01-09T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     *   }
     * @response 404 {
     *   "success": false,
     *   "message": "Raw Data not found"
     *   }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation Error",
     *   "errors": {
     *     "fungsi": ["The fungsi must be a string."],
     *     "nama": ["The nama must be a string."],
     *     "file": ["The file must be a file of type: zip, rar, csv, xlsx."]
     *   }
     *   }
     */
    public function update(UpdateRawDataRequest $request, $id): JsonResponse
    {
        $rawData = RawData::find($id);

        if (!$rawData) {
            return $this->error('Raw Data not found', null, 404);
        }

        $validatedData = $request->validated();

        if ($request->hasFile('file')) {
            if ($rawData->file && Storage::disk('direct')->exists($rawData->file)) {
                Storage::disk('direct')->delete($rawData->file);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('raw_data', $fileName, 'direct');

            $validatedData['file'] = $filePath;
        }

        $rawData->update($validatedData);

        return $this->success($rawData, 'Raw Data updated successfully');
    }

    /**
     * Remove specified raw data record from storage.
     *
     * @param int $id
     * @return JsonResponse
     *
     * @group IPDS - Raw Data
     *
     * @authenticated
     *
     * @urlParam id int required ID raw data. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Raw Data deleted successfully",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     *   }
     * @response 404 {
     *   "success": false,
     *   "message": "Raw Data not found"
     *   }
     */
    public function destroy($id): JsonResponse
    {
        $rawData = RawData::find($id);

        if (!$rawData) {
            return $this->error('Raw Data not found', null, 404);
        }

        if ($rawData->file && Storage::disk('direct')->exists($rawData->file)) {
            Storage::disk('direct')->delete($rawData->file);
        }

        $rawData->delete();

        return $this->success(null, 'Raw Data deleted successfully');
    }
}
