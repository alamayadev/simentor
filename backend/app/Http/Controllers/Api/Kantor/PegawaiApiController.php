<?php
namespace App\Http\Controllers\Api\Kantor;

use App\Http\Controllers\Api\BaseApiController;
use App\Http\Requests\Kantor\Pegawai\AccessPegawaiRequest;
use App\Http\Requests\Kantor\Pegawai\IndexPegawaiRequest;
use App\Http\Requests\Kantor\Pegawai\StorePegawaiRequest;
use App\Http\Requests\Kantor\Pegawai\UpdatePegawaiRequest;
use App\Models\Pegawai;
use App\Services\PegawaiService;
use Illuminate\Support\Facades\Auth;

/**
 * @group Kantor - Pegawai
 */
class PegawaiApiController extends BaseApiController
{
    protected PegawaiService $pegawaiService;

    public function __construct(PegawaiService $pegawaiService)
    {
        $this->pegawaiService = $pegawaiService;
    }

    /**
     * Menampilkan daftar pegawai.
     *
     * Endpoint ini mengembalikan daftar pegawai dengan fitur pagination, pencarian, dan filtering.
     *
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 10). Example: 10
     * @queryParam cursor string Cursor untuk pagination.
     * @queryParam search string Kata kunci pencarian (nama/NIP). Example: Budi
     * @queryParam filter[pangkat] string Filter berdasarkan pangkat. Example: Pembina
     * @queryParam filter[jabatan] string Filter berdasarkan jabatan. Example: Statistisi Ahli Muda
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama": "Budi Santoso",
     *       "nip": "198001012000011001",
     *       "pangkat": "Pembina",
     *       "gol": "IV/a",
     *       "jabatan": "Statistisi Ahli Madya",
     *       "kelas": "11",
     *       "status": "Aktif"
     *     }
     *   ],
     *   "meta": {
     *     "pagination_info": {
     *       "total_page": 5,
     *       "total_records": 50
     *     }
     *   }
     * }
     */
    public function index(IndexPegawaiRequest $request)
    {
        $result = $this->pegawaiService->listPegawai($request->validated());

        return $this->success(
            $result['data'],
            'Data retrieved successfully',
            200
        );
    }

    /**
     * Membuat data pegawai baru.
     *
     * Endpoint ini digunakan untuk menambahkan data pegawai baru.
     *
     * @authenticated
     *
     * @bodyParam nama string required Nama lengkap pegawai. Example: Andi Wijaya
     * @bodyParam nip string required NIP pegawai (18 digit, unik). Example: 199001012015011001
     * @bodyParam pangkat string required Pangkat pegawai. Example: Penata Muda
     * @bodyParam gol string required Golongan pegawai. Example: III/a
     * @bodyParam jabatan string required Jabatan pegawai. Example: Statistisi Ahli Pertama
     * @bodyParam kelas string required Kelas jabatan. Example: 8
     * @bodyParam user_id int required ID User terkait. Example: 5
     * @bodyParam status string Status pegawai using (default: Aktif). Example: Aktif
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Pegawai created successfully",
     *   "data": {
     *     "id": 10,
     *     "nama": "Andi Wijaya",
     *     "nip": "199001012015011001"
     *   }
     * }
     */
    public function store(StorePegawaiRequest $request)
    {
        $pegawai = $this->pegawaiService->createPegawai($request->validated());

        return $this->success($pegawai, 'Pegawai created successfully', 201);
    }

    /**
     * Menampilkan detail pegawai.
     *
     * @authenticated
     *
     * @urlParam id int required ID Pegawai. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "nama": "Budi Santoso",
     *     "nip": "198001012000011001",
     *     "user": {
     *       "id": 1,
     *       "name": "Budi Santoso",
     *       "email": "budi@bps.go.id"
     *     }
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Pegawai not found"
     * }
     */
    public function show(AccessPegawaiRequest $request, $id)
    {
        $pegawai = Pegawai::with('user')->find($id);

        if (! $pegawai) {
            return $this->error('Pegawai not found', null, 404);
        }

        return $this->success($pegawai, 'Data retrieved successfully');
    }

    /**
     * Memperbarui data pegawai.
     *
     * @authenticated
     *
     * @urlParam id int required ID Pegawai. Example: 1
     *
     * @bodyParam nama string Nama lengkap pegawai. Example: Budi Santoso Gelar
     * @bodyParam nip string NIP pegawai. Example: 198001012000011001
     * @bodyParam pangkat string Pangkat pegawai. Example: Pembina Tk I
     * @bodyParam gol string Golongan pegawai. Example: IV/b
     * @bodyParam jabatan string Jabatan pegawai. Example: Statistisi Ahli Madya
     * @bodyParam kelas string Kelas jabatan. Example: 12
     * @bodyParam user_id int ID User terkait. Example: 1
     * @bodyParam status string Status pegawai. Example: Cuti
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pegawai updated successfully",
     *   "data": {
     *     "id": 1,
     *     "nama": "Budi Santoso Gelar"
     *   }
     * }
     */
    public function update(UpdatePegawaiRequest $request, $id)
    {
        $pegawai = Pegawai::find($id);

        if (! $pegawai) {
            return $this->error('Pegawai not found', null, 404);
        }

        $this->pegawaiService->updatePegawai($pegawai, $request->validated());

        return $this->success($pegawai, 'Pegawai updated successfully');
    }

    /**
     * Menghapus data pegawai.
     *
     * @authenticated
     *
     * @urlParam id int required ID Pegawai. Example: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pegawai deleted successfully"
     * }
     */
    public function destroy(AccessPegawaiRequest $request, $id)
    {
        $pegawai = Pegawai::find($id);

        if (! $pegawai) {
            return $this->error('Pegawai not found', null, 404);
        }

        $this->pegawaiService->deletePegawai($pegawai);

        return $this->success(null, 'Pegawai deleted successfully');
    }

    /**
     * Mendapatkan daftar pangkat.
     *
     * Endpoint ini mengembalikan daftar unik pangkat yang ada di database.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pangkat list retrieved successfully",
     *   "data": [
     *     "Pengatur",
     *     "Penata Muda",
     *     "Pembina"
     *   ]
     * }
     */
    public function getPangkatList(AccessPegawaiRequest $request)
    {
        $data = $this->pegawaiService->getPangkatList();
        return $this->success($data, 'Pangkat list retrieved successfully');
    }

    /**
     * Mendapatkan daftar jabatan.
     *
     * Endpoint ini mengembalikan daftar unik jabatan yang ada di database.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Jabatan list retrieved successfully",
     *   "data": [
     *     "Statistisi Pelaksana",
     *     "Statistisi Ahli Pertama",
     *     "Pranata Komputer Ahli Muda"
     *   ]
     * }
     */
    public function getJabatanList(AccessPegawaiRequest $request)
    {
        $data = $this->pegawaiService->getJabatanList();
        return $this->success($data, 'Jabatan list retrieved successfully');
    }

    /**
     * Mendapatkan daftar jabatan dan pangkat.
     *
     * Endpoint ini mengembalikan daftar kombinasi jabatan dan pangkat yang unik.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Jabatan and Pangkat list retrieved successfully",
     *   "data": [
     *     {
     *       "jabatan": "Statistisi Ahli Pertama",
     *       "pangkat": "Penata Muda"
     *     }
     *   ]
     * }
     */
    public function getJabatanPangkatList(AccessPegawaiRequest $request)
    {
        $data = $this->pegawaiService->getJabatanPangkatList();
        return $this->success($data, 'Jabatan and Pangkat list retrieved successfully');
    }

    /**
     * Mendapatkan data filter pegawai.
     *
     * Endpoint ini mengembalikan daftar nama pegawai dan daftar jabatan
     * dari pegawai dengan status NULL.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pegawai filters retrieved successfully",
     *   "data": {
     *     "pegawaiList": [
     *       "Ahmad",
     *       "Budi"
     *     ],
     *     "jabatanList": [
     *       "Statistisi Ahli Muda",
     *       "Pranata Komputer Ahli Muda"
     *     ]
     *   }
     * }
     */
    public function filters(AccessPegawaiRequest $request)
    {
        $data = $this->pegawaiService->filters();

        return $this->success($data, 'Pegawai filters retrieved successfully');
    }

    /**
     * Mendapatkan opsi form pegawai.
     *
     * Endpoint ini mengembalikan opsi pangkat, golongan, jabatan,
     * dan daftar user yang belum terhubung ke profil pegawai.
     *
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Pegawai form options retrieved successfully",
     *   "data": {
     *     "pangkat": [
     *       "Juru Muda",
     *       "Penata Muda",
     *       "Pembina"
     *     ],
     *     "golongan": [
     *       "Ia",
     *       "IIIa",
     *       "IVa"
     *     ],
     *     "jabatan": [
     *       "Kepala",
     *       "Statistisi Ahli Muda",
     *       "Pranata Komputer Ahli Madya"
     *     ],
     *     "userNoPegawai": [
     *       {
     *         "id": 1,
     *         "name": "Budi Santoso"
     *       },
     *       {
     *         "id": 2,
     *         "name": "Andi Wijaya"
     *       }
     *     ]
     *   }
     * }
     */
    public function formOptions(AccessPegawaiRequest $request)
    {
        $data = $this->pegawaiService->formOptions();

        return $this->success($data, 'Pegawai form options retrieved successfully');
    }
}
