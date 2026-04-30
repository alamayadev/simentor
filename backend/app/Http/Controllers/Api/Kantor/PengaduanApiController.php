<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\Pengaduan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\QueryBuilder\QueryBuilder;

/**
 * Pengaduan API Controller
 *
 * CRUD operations for Pengaduan (Complaint) entities.
 * @group Kantor - Pengaduan
 */
class PengaduanApiController extends BaseApiController
{
    /**
     * Menampilkan daftar pengaduan dengan pagination dan pencarian.
     *
     * Endpoint ini mengembalikan daftar pengaduan dengan pagination beserta kemampuan untuk melakukan pencarian dan filter.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Pengaduan
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman. Contoh: 10
     * @queryParam cursor string Cursor untuk pagination. Contoh: eyJpZCI6M...
     * @queryParam filter[jenis_pelangaran] string Filter berdasarkan jenis pelanggaran. Contoh: Korupsi
     * @queryParam filter[pelaku] string Filter berdasarkan pelaku. Contoh: Pegawai
     * @queryParam filter[lainnya] string Filter berdasarkan field lainnya. Contoh: Tambahan informasi
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "jenis_pelangaran": "Korupsi",
     *       "lainnya": "Informasi tambahan",
     *       "pelaku": "Pegawai",
     *       "waktu_kejadian": "2023-01-01",
     *       "kronologi": "Deskripsi kronologi kejadian",
     *       "bukti": "file_bukti.jpg",
     *       "created_at": "2023-01-01T00:00:00.000000Z",
     *       "updated_at": "2023-01-01T00:00:00.000000Z"
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
     *     "path": "http://127.0.0.1:8000/api/kantor/pengaduan"
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
        if (! $user) {
            return $this->error('Unauthorized', null, 401);
        }

        $perPage = $request->get('per_page', 10);

        // Get base query for counting
        $baseQuery = Pengaduan::query();

        // Apply same filters for counting
        if ($request->get('filter.jenis_pelangaran')) {
            $baseQuery->where('jenis_pelangaran', $request->get('filter.jenis_pelangaran'));
        }

        if ($request->get('filter.pelaku')) {
            $baseQuery->where('pelaku', $request->get('filter.pelaku'));
        }

        if ($request->get('filter.lainnya')) {
            $baseQuery->where('lainnya', $request->get('filter.lainnya'));
        }

        // Get total count for pagination info (separate query for performance)
        $totalRecords = $baseQuery->count();

        $pengaduan = QueryBuilder::for(Pengaduan::class)
            ->allowedFilters(['jenis_pelangaran', 'pelaku', 'lainnya'])
            ->fastPaginate($perPage);

        // Add pagination extras including total_page
        $totalPages = $perPage > 0 ? ceil($totalRecords / $perPage) : 1;
        $extras     = [
            'pagination_info' => [
                'total_page'    => $totalPages,
                'total_records' => $totalRecords,
            ],
        ];

        return $this->success($pengaduan, 'Data retrieved successfully', 200, $extras);
    }

    /**
     * Membuat data pengaduan baru.
     *
     * Endpoint ini digunakan untuk membuat data pengaduan baru berdasarkan data yang diberikan.
     * Endpoint ini dapat diakses tanpa autentikasi.
     *
     * @group Pengaduan
     *
     * @bodyParam jenis_pelangaran string required Jenis pelanggaran. Contoh: Korupsi
     * @bodyParam lainnya string required Informasi tambahan. Contoh: Informasi tambahan
     * @bodyParam pelaku string required Pelaku kejadian. Contoh: Pegawai
     * @bodyParam waktu_kejadian date required Waktu kejadian (format: YYYY-MM-DD). Contoh: 2023-01-01
     * @bodyParam kronologi text required Kronologi kejadian. Contoh: Deskripsi kronologi kejadian
     * @bodyParam bukti string required Bukti pendukung. Contoh: file_bukti.jpg
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Pengaduan created successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis_pelangaran": "Korupsi",
     *     "lainnya": "Informasi tambahan",
     *     "pelaku": "Pegawai",
     *     "waktu_kejadian": "2023-01-01",
     *     "kronologi": "Deskripsi kronologi kejadian",
     *     "bukti": "file_bukti.jpg",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "jenis_pelangaran": ["The jenis pelangaran field is required."],
     *     "lainnya": ["The lainnya field is required."],
     *     "pelaku": ["The pelaku field is required."],
     *     "waktu_kejadian": ["The waktu kejadian field is required."],
     *     "kronologi": ["The kronologi field is required."],
     *     "bukti": ["The bukti field is required."]
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
            'jenis_pelangaran' => 'required|string|max:255',
            'lainnya'          => 'required|string|max:255',
            'pelaku'           => 'required|string|max:255',
            'waktu_kejadian'   => 'required|date',
            'kronologi'        => 'required|string',
            'bukti'            => 'required|string|max:255',
        ]);

        // Create the pengaduan
        $pengaduan = Pengaduan::create($validatedData);

        return $this->success($pengaduan, 'Pengaduan created successfully', 201);
    }

    /**
     * Menampilkan detail pengaduan.
     *
     * Endpoint ini mengembalikan detail informasi pengaduan berdasarkan ID yang diberikan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Pengaduan
     * @authenticated
     *
     * @urlParam id int required ID pengaduan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis_pelangaran": "Korupsi",
     *     "lainnya": "Informasi tambahan",
     *     "pelaku": "Pegawai",
     *     "waktu_kejadian": "2023-01-01",
     *     "kronologi": "Deskripsi kronologi kejadian",
     *     "bukti": "file_bukti.jpg",
     *     "created_at": "2023-01-01T00:00:00.000000Z",
     *     "updated_at": "2023-01-01T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Pengaduan not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = Auth::user();
        if (! $user) {
            return $this->error('Unauthorized', null, 401);
        }

        $pengaduan = Pengaduan::find($id);

        if (! $pengaduan) {
            return $this->error('Pengaduan not found', null, 404);
        }

        return $this->success($pengaduan, 'Data retrieved successfully');
    }

    /**
     * Memperbarui data pengaduan.
     *
     * Endpoint ini digunakan untuk memperbarui data pengaduan berdasarkan ID dan data yang diberikan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Pengaduan
     * @authenticated
     *
     * @urlParam id int required ID pengaduan. Contoh: 1
     * @bodyParam jenis_pelangaran string Jenis pelanggaran. Contoh: Korupsi
     * @bodyParam lainnya string Informasi tambahan. Contoh: Informasi tambahan
     * @bodyParam pelaku string Pelaku kejadian. Contoh: Pegawai
     * @bodyParam waktu_kejadian date Waktu kejadian (format: YYYY-MM-DD). Contoh: 2023-01-01
     * @bodyParam kronologi text Kronologi kejadian. Contoh: Deskripsi kronologi kejadian
     * @bodyParam bukti string Bukti pendukung. Contoh: file_bukti.jpg
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pengaduan updated successfully",
     *   "data": {
     *     "id": 1,
     *     "jenis_pelangaran": "Korupsi Updated",
     *     "lainnya": "Informasi tambahan",
     *     "pelaku": "Pegawai",
     *     "waktu_kejadian": "2023-01-01",
     *     "kronologi": "Deskripsi kronologi kejadian",
     *     "bukti": "file_bukti.jpg",
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
     *   "message": "Pengaduan not found"
     * }
     * @response 422 {
     *   "message": "The given data was invalid.",
     *   "errors": {
     *     "waktu_kejadian": ["The waktu kejadian is not a valid date."],
     *     "bukti": ["The bukti must not be greater than 255 characters."]
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
        if (! $user) {
            return $this->error('Unauthorized', null, 401);
        }

        // Find the pengaduan
        $pengaduan = Pengaduan::find($id);

        if (! $pengaduan) {
            return $this->error('Pengaduan not found', null, 404);
        }

        // Validasi input
        $validatedData = $request->validate([
            'jenis_pelangaran' => 'sometimes|string|max:255',
            'lainnya'          => 'sometimes|string|max:255',
            'pelaku'           => 'sometimes|string|max:255',
            'waktu_kejadian'   => 'sometimes|date',
            'kronologi'        => 'sometimes|string',
            'bukti'            => 'sometimes|string|max:255',
        ]);

        // Update the pengaduan
        $pengaduan->update($validatedData);

        return $this->success($pengaduan, 'Pengaduan updated successfully');
    }

    /**
     * Menghapus data pengaduan.
     *
     * Endpoint ini digunakan untuk menghapus data pengaduan berdasarkan ID yang diberikan.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Pengaduan
     * @authenticated
     *
     * @urlParam id int required ID pengaduan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pengaduan deleted successfully",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Pengaduan not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = Auth::user();
        if (! $user) {
            return $this->error('Unauthorized', null, 401);
        }

        // Find the pengaduan
        $pengaduan = Pengaduan::find($id);

        if (! $pengaduan) {
            return $this->error('Pengaduan not found', null, 404);
        }

        // Delete the pengaduan
        $pengaduan->delete();

        return $this->success(null, 'Pengaduan deleted successfully');
    }
}
