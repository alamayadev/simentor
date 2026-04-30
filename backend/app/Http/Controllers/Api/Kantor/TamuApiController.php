<?php

namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Tamu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Tamu API Controller
 *
 * CRUD operations for Tamu (Guest) entities.
 * @group Tamu
 */
class TamuApiController extends BaseApiController
{
    /**
     * Menampilkan daftar tamu dengan pagination dan pencarian.
     *
     * Endpoint ini mengembalikan daftar tamu dengan pagination beserta kemampuan untuk melakukan pencarian dan filter.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Tamu
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[nama] string Filter berdasarkan nama tamu. Contoh: Budi
     * @queryParam filter[email] string Filter berdasarkan email tamu. Contoh: budi@example.com
     * @queryParam filter[asal_instansi] string Filter berdasarkan asal instansi tamu. Contoh: BPS
     * @queryParam filter[tujuan_kunjungan] string Filter berdasarkan tujuan kunjungan tamu. Contoh: Konsultasi
     * @queryParam filter[jenis_layanan] string Filter berdasarkan jenis layanan tamu. Contoh: Informasi
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama": "Budi Santoso",
     *       "email": "budi@example.com",
     *       "no_hp": "081234567890",
     *       "asal_instansi": "BPS",
     *       "tgl_kunjungan": "2023-01-01",
     *       "tujuan_kunjungan": "Konsultasi",
     *       "jenis_layanan": "Informasi",
     *       "detil_layanan": "Meminta informasi tentang kegiatan sensus"
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
     *     "path": "http://127.0.0.1:8000/api/kantor/tamu"
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
        $user = Auth::user();
        if (!$user) {
            return $this->error('Unauthorized', null, 401);
        }

        $perPage = $request->get('per_page', 10);

        // Get base query for counting
        $baseQuery = Tamu::query();

        // Apply same filters for counting
        if ($request->get('filter.nama')) {
            $baseQuery->where('nama', $request->get('filter.nama'));
        }
        
        if ($request->get('filter.email')) {
            $baseQuery->where('email', $request->get('filter.email'));
        }
        
        if ($request->get('filter.asal_instansi')) {
            $baseQuery->where('asal_instansi', $request->get('filter.asal_instansi'));
        }
        
        if ($request->get('filter.tujuan_kunjungan')) {
            $baseQuery->where('tujuan_kunjungan', $request->get('filter.tujuan_kunjungan'));
        }
        
        if ($request->get('filter.jenis_layanan')) {
            $baseQuery->where('jenis_layanan', $request->get('filter.jenis_layanan'));
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $baseQuery->count();

        $tamu = QueryBuilder::for(Tamu::class)
            ->allowedFilters(['nama', 'email', 'asal_instansi', 'tujuan_kunjungan', 'jenis_layanan'])
            ->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras = [
            'pagination_info' => [
                'total_page' => $totalPages,
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($tamu, 'Data retrieved successfully', 200, $extras);
    }

    /**
     * Membuat data tamu baru.
     *
     * Endpoint ini digunakan untuk membuat data tamu baru berdasarkan data yang diberikan.
     * Endpoint ini dapat diakses tanpa autentikasi.
     *
     * @group Tamu
     *
     * @bodyParam nama string required Nama lengkap tamu. Contoh: Budi Santoso
     * @bodyParam email string required Email tamu. Contoh: budi@example.com
     * @bodyParam no_hp string required Nomor HP tamu. Contoh: 081234567890
     * @bodyParam asal_instansi string required Asal instansi tamu. Contoh: BPS
     * @bodyParam tgl_kunjungan date required Tanggal kunjungan (format: YYYY-MM-DD). Contoh: 2023-01-01
     * @bodyParam tujuan_kunjungan string required Tujuan kunjungan tamu. Contoh: Konsultasi
     * @bodyParam jenis_layanan string required Jenis layanan yang dibutuhkan. Contoh: Informasi
     * @bodyParam detil_layanan text required Detil layanan yang dibutuhkan. Contoh: Meminta informasi tentang kegiatan sensus
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Tamu created successfully",
     *   "data": {
     *     "id": 1,
     *     "nama": "Budi Santoso",
     *     "email": "budi@example.com",
     *     "no_hp": "081234567890",
     *     "asal_instansi": "BPS",
     *     "tgl_kunjungan": "2023-01-01",
     *     "tujuan_kunjungan": "Konsultasi",
     *     "jenis_layanan": "Informasi",
     *     "detil_layanan": "Meminta informasi tentang kegiatan sensus",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "nama": ["The nama field is required."],
     *     "email": ["The email field is required."],
     *     "no_hp": ["The no hp field is required."],
     *     "asal_instansi": ["The asal instansi field is required."],
     *     "tgl_kunjungan": ["The tgl kunjungan field is required."],
     *     "tujuan_kunjungan": ["The tujuan kunjungan field is required."],
     *     "jenis_layanan": ["The jenis layanan field is required."],
     *     "detil_layanan": ["The detil layanan field is required."]
     *   }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // Validasi input
        $validatedData = $request->validate([
            'nama' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'no_hp' => 'required|string|max:20',
            'asal_instansi' => 'required|string|max:255',
            'tgl_kunjungan' => 'required|date',
            'tujuan_kunjungan' => 'required|string|max:255',
            'jenis_layanan' => 'required|string|max:255',
            'detil_layanan' => 'required|string',
        ]);

        // Create the tamu
        $tamu = Tamu::create($validatedData);

        return $this->success($tamu, 'Tamu created successfully', 201);
    }

    /**
     * Menampilkan detail tamu.
     *
     * Endpoint ini mengembalikan detail informasi tamu berdasarkan ID yang diberikan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Tamu
     * @authenticated
     *
     * @urlParam id int required ID tamu. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "nama": "Budi Santoso",
     *     "email": "budi@example.com",
     *     "no_hp": "081234567890",
     *     "asal_instansi": "BPS",
     *     "tgl_kunjungan": "2023-01-01",
     *     "tujuan_kunjungan": "Konsultasi",
     *     "jenis_layanan": "Informasi",
     *     "detil_layanan": "Meminta informasi tentang kegiatan sensus"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Tamu not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->error('Unauthorized', null, 401);
        }

        $tamu = Tamu::find($id);

        if (!$tamu) {
            return $this->error('Tamu not found', null, 404);
        }

        return $this->success($tamu, 'Data retrieved successfully');
    }

    /**
     * Memperbarui data tamu.
     *
     * Endpoint ini digunakan untuk memperbarui data tamu berdasarkan ID dan data yang diberikan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Tamu
     * @authenticated
     *
     * @urlParam id int required ID tamu. Contoh: 1
     * @bodyParam nama string Nama lengkap tamu. Contoh: Budi Santoso
     * @bodyParam email string Email tamu. Contoh: budi@example.com
     * @bodyParam no_hp string Nomor HP tamu. Contoh: 081234567890
     * @bodyParam asal_instansi string Asal instansi tamu. Contoh: BPS
     * @bodyParam tgl_kunjungan date Tanggal kunjungan (format: YYYY-MM-DD). Contoh: 2023-01-01
     * @bodyParam tujuan_kunjungan string Tujuan kunjungan tamu. Contoh: Konsultasi
     * @bodyParam jenis_layanan string Jenis layanan yang dibutuhkan. Contoh: Informasi
     * @bodyParam detil_layanan text Detil layanan yang dibutuhkan. Contoh: Meminta informasi tentang kegiatan sensus
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Tamu updated successfully",
     *   "data": {
     *     "id": 1,
     *     "nama": "Budi Santoso Updated",
     *     "email": "budi_updated@example.com",
     *     "no_hp": "081234567890",
     *     "asal_instansi": "BPS",
     *     "tgl_kunjungan": "2023-01-01",
     *     "tujuan_kunjungan": "Konsultasi",
     *     "jenis_layanan": "Informasi",
     *     "detil_layanan": "Meminta informasi tentang kegiatan sensus",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-02T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Tamu not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "email": ["The email must be a valid email address."],
     *     "tgl_kunjungan": ["The tgl kunjungan is not a valid date."]
     *   }
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->error('Unauthorized', null, 401);
        }

        // Find the tamu
        $tamu = Tamu::find($id);

        if (!$tamu) {
            return $this->error('Tamu not found', null, 404);
        }

        // Validasi input
        $validatedData = $request->validate([
            'nama' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|max:255',
            'no_hp' => 'sometimes|string|max:20',
            'asal_instansi' => 'sometimes|string|max:255',
            'tgl_kunjungan' => 'sometimes|date',
            'tujuan_kunjungan' => 'sometimes|string|max:255',
            'jenis_layanan' => 'sometimes|string|max:255',
            'detil_layanan' => 'sometimes|string',
        ]);

        // Update the tamu
        $tamu->update($validatedData);

        return $this->success($tamu, 'Tamu updated successfully');
    }

    /**
     * Menghapus data tamu.
     *
     * Endpoint ini digunakan untuk menghapus data tamu berdasarkan ID yang diberikan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Tamu
     * @authenticated
     *
     * @urlParam id int required ID tamu. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Tamu deleted successfully",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Tamu not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (!$user) {
            return $this->error('Unauthorized', null, 401);
        }

        // Find the tamu
        $tamu = Tamu::find($id);

        if (!$tamu) {
            return $this->error('Tamu not found', null, 404);
        }

        // Delete the tamu
        $tamu->delete();

        return $this->success(null, 'Tamu deleted successfully');
    }
}
