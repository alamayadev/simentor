<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Penugasan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Spatie\QueryBuilder\QueryBuilder;
use Carbon\Carbon;

class BastApiController extends BaseApiController
{
    /**
     * Menampilkan daftar BAST dengan pagination dan filtering.
     *
     * Endpoint ini mengembalikan daftar BAST yang dikelompokkan berdasarkan mitra_id,
     * bln_bayar, no_sk, no_bast, dan tgl_bast dengan informasi agregat.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group BAST
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 10). Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam sort_by string Kolom untuk sorting (default: created_at). Contoh: created_at
     * @queryParam sort_dir string Arah sorting ASC/DESC (default: DESC). Contoh: DESC
     * @queryParam selected_bln date Filter berdasarkan bulan bayar (format: YYYY-MM-DD). Contoh: 2024-01-01
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "mitra_id": 1,
     *       "bln_bayar": "2024-01-01",
     *       "no_sk": "SK-001",
     *       "no_bast": "BAST-001",
     *       "tgl_bast": "2024-01-15",
     *       "total": 5000000,
     *       "jml_tugas": 3,
     *       "mitra": {
     *         "id": 1,
     *         "nama_lengkap": "Budi Santoso",
     *         "nik": "1234567890123456",
     *         "alamat_detail": "Jl. Contoh No. 123"
     *       }
     *     }
     *   ],
     *   "meta": {
     *     "per_page": 10,
     *     "has_more": true,
     *     "count": 10
     *   },
     *   "links": {
     *     "next_cursor": "...",
     *     "next_page_url": "http://127.0.0.1:8000/api/bast?cursor=...",
     *     "prev_cursor": null,
     *     "prev_page_url": null,
     *     "path": "http://127.0.0.1:8000/api/bast"
     *   },
     *   "pagination_info": {
     *     "total_page": 4,
     *     "total_records": 37
     *   },
     *   "available_months": [
     *     "2024-12-01",
     *     "2024-11-01",
     *     "2024-10-01"
     *   ]
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

        $perPage = $request->get('per_page', 10);
        $page = $request->get('page', 1);
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDir = $request->get('sort_dir', 'DESC');
        $selectedBln = $request->get('selected_bln', '');

        // Get list of available months for filtering
        $listBln = Penugasan::groupBy('bln_bayar')
            ->orderBy('bln_bayar', 'DESC')
            ->pluck('bln_bayar');

        // Build the main query
        $query = Penugasan::select(['id', 'mitra_id', 'bln_bayar', 'nilai'])
            ->with(['mitra:id,nama_lengkap,nik,alamat_detail'])
            ->groupBy(['mitra_id', 'bln_bayar', 'no_sk', 'no_bast', 'tgl_bast'])
            ->selectRaw('id,mitra_id,bln_bayar,no_sk,no_bast,tgl_bast, sum(nilai) as total,count(kegiatan_id) as jml_tugas');

        // Apply date filter if provided
        if (!empty($selectedBln)) {
            $query->whereDate('bln_bayar', $selectedBln);
        }

        $query->orderBy($sortBy, $sortDir);

        // Get total count for pagination info (optimized query)
        $totalRecords = $query->count();

        // Paginate results with cursor pagination
        $penugasan = $query->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;

        return $this->success($penugasan, 'Data retrieved successfully', 200, [
            'available_months' => $listBln,
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ]);
    }

    /**
     * Menampilkan detail BAST untuk pengeditan.
     *
     * Endpoint ini mengembalikan detail penugasan BAST berdasarkan ID penugasan,
     * beserta daftar penugasan terkait untuk mitra dan bulan yang sama.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group BAST
     * @authenticated
     *
     * @urlParam id int required ID penugasan untuk BAST. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "penugasan": {
     *       "id": 1,
     *       "kegiatan_id": 1,
     *       "jabatan_tugas": "PCL",
     *       "pegawai_id": null,
     *       "mitra_id": 1,
     *       "volume": 100,
     *       "nilai": 5000000,
     *       "bln_bayar": "2024-01-01",
     *       "created_by": 1,
     *       "no_bast": "BAST-001",
     *       "tgl_bast": "2024-01-15",
     *       "no_sk": "SK-001",
     *       "tgl_sk": "2024-01-10",
     *       "jangka_waktu_mulai": "2024-01-01",
     *       "jangka_waktu_selesai": "2024-12-31",
     *       "created_at": "2024-01-01T00:00:00.000000Z",
     *       "updated_at": "2024-01-01T00:00:00.000000Z"
     *     },
     *     "penugasan_mitra": [
     *       {
     *         "id": 1,
     *         "mitra_id": 1,
     *         "bln_bayar": "2024-01-01",
     *         "no_bast": "BAST-001",
     *         "tgl_bast": "2024-01-15"
     *       }
     *     ],
     *     "suggested_tgl_bast": "2023-12-31"
     *   }
     * }
    * @response 401 {
    *   "success": false,
    *   "message": "Unauthorized"
    * }
    * @response 404 {
    *   "success": false,
    *   "message": "Penugasan not found"
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

        $penugasan = Penugasan::find($id);

        if (!$penugasan) {
            return $this->error('Penugasan not found', null, 404);
        }

        // Get related penugasan for the same mitra and month
        $penugasanMitra = Penugasan::where('mitra_id', $penugasan->mitra_id)
            ->where('bln_bayar', $penugasan->bln_bayar)
            ->get();

        // Calculate suggested tgl_bast (end of previous month)
        $suggestedTglBast = Carbon::parse($penugasan->bln_bayar)
            ->subMonthsNoOverflow()
            ->endOfMonth()
            ->toDateString();

        return $this->success([
            'penugasan' => $penugasan,
            'penugasan_mitra' => $penugasanMitra,
            'suggested_tgl_bast' => $suggestedTglBast
        ], 'Data retrieved successfully');
    }

    /**
     * Memperbarui data BAST untuk penugasan.
     *
     * Endpoint ini memperbarui nomor BAST dan tanggal BAST untuk semua penugasan
     * dengan mitra_id dan bulan bayar yang sama. Hanya pengguna yang sudah login
     * yang dapat mengakses endpoint ini.
     *
     * @group BAST
     * @authenticated
     *
     * @urlParam id int required ID penugasan untuk BAST. Contoh: 1
     *
     * @bodyParam no_bast integer Nomor BAST. Contoh: 1001
     * @bodyParam tgl_bast date required Tanggal BAST (format: YYYY-MM-DD). Contoh: 2024-01-15
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data BAST berhasil diupdate",
     *   "data": {
     *     "updated_count": 3,
     *     "data": {
     *       "no_bast": "BAST-001",
     *       "tgl_bast": "2024-01-15"
     *     }
     *   }
     * }
    * @response 401 {
    *   "success": false,
    *   "message": "Unauthorized"
    * }
    * @response 404 {
    *   "success": false,
    *   "message": "Penugasan not found"
    * }
    * @response 422 {
    *   "success": false,
    *   "message": "Validation Error",
    *   "errors": {
    *     "tgl_bast": [
    *       "Tanggal BAST wajib diisi."
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

        $penugasan = Penugasan::find($id);

        if (!$penugasan) {
            return $this->error('Penugasan not found', null, 404);
        }

        $validator = Validator::make($request->all(), [
            'no_bast' => 'sometimes|numeric|min:1',
            'tgl_bast' => 'required|date_format:Y-m-d',
        ], [
            'no_bast.numeric' => 'Nomor BAST harus berupa angka.',
            'no_bast.min' => 'Nomor BAST minimal 1.',
            'tgl_bast.required' => 'Tanggal BAST wajib diisi.',
            'tgl_bast.date_format' => 'Format tanggal BAST harus YYYY-MM-DD.',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation Error', $validator->errors(), 422);
        }

        $data = $validator->validated();

        // Update all penugasan with same mitra_id and bln_bayar
        $penugasanMitraBln = Penugasan::where('mitra_id', $penugasan->mitra_id)
            ->whereDate('bln_bayar', $penugasan->bln_bayar)
            ->get();

        $updatedCount = 0;
        foreach ($penugasanMitraBln as $item) {
            $updateData = ['tgl_bast' => $data['tgl_bast']];
            if (isset($data['no_bast'])) {
                $updateData['no_bast'] = $data['no_bast'];
            }

            Penugasan::where('id', $item->id)->update($updateData);
            $updatedCount++;
        }

        return $this->success([
            'updated_count' => $updatedCount,
            'data' => $data
        ], 'Data BAST berhasil diupdate');
    }

    /**
     * Menampilkan daftar bulan bayar yang tersedia.
     *
     * Endpoint ini mengembalikan daftar bulan bayar yang tersedia untuk filtering.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group BAST
     * @authenticated
     *
    * @response 200 {
    *   "success": true,
    *   "message": "Available months retrieved successfully",
    *   "data": {
    *     "data": [
    *       "2024-12-01",
    *       "2024-11-01",
    *       "2024-10-01"
    *     ]
    *   }
    * }
    * @response 401 {
    *   "success": false,
    *   "message": "Unauthorized"
    * }
     *
     * @return \Illuminate\Http\Response
     */
    public function getAvailableMonths()
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        $listBln = Penugasan::groupBy('bln_bayar')
            ->orderBy('bln_bayar', 'DESC')
            ->pluck('bln_bayar');

        return $this->success($listBln, 'Available months retrieved successfully');
    }
}
