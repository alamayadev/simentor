<?php

namespace App\Http\Controllers\Api\Kantor\NomorSurat;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SurtugDetil;
use App\Models\SuratTugas;
use App\Models\Penugasan;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SurtugDetilApiController extends BaseApiController
{
    /**
     * Menampilkan daftar detail surat tugas dengan pagination dan filtering.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @queryParam per_page int Jumlah item per halaman (default: 10). Contoh: 10
     * @queryParam surtug_id int Filter berdasarkan ID surat tugas. Contoh: 1
     * @queryParam pegawai_id int Filter berdasarkan ID pegawai. Contoh: 10
     * @queryParam mitra_id int Filter berdasarkan ID mitra. Contoh: 5
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data berhasil diambil",
     *   "data": [
     *     {
     *       "id": 1,
     *       "surtug_id": 1,
     *       "pegawai_id": 10,
     *       "mitra_id": null,
     *       "grup_mitra": null,
     *       "grup_pegawai": 1,
     *       "penugasan_id": null,
     *       "dasar": "Peraturan BPS No. 1 Tahun 2024",
     *       "nama_kegiatan": "Sensus Penduduk",
     *       "tugas_sebagai": "Petugas Lapangan",
     *       "hari": 5,
     *       "wilayah_kerja": "Kecamatan A",
     *       "tgl_mulai": "2024-01-15",
     *       "jenis_kendaraan": "Motor",
     *       "no_dipa": "DIPA-2024-001",
     *       "isOrganik": true,
     *       "sppd": false,
     *       "created_at": "2024-01-15T00:00:00.000000Z",
     *       "updated_at": "2024-01-15T00:00:00.000000Z"
     *     }
     *   ],
     *   "meta": {
     *     "current_page": 1,
     *     "from": 1,
     *     "last_page": 1,
     *     "path": "http://localhost:8000/api/kantor/nomor-surat/surtug-detil",
     *     "per_page": 10,
     *     "to": 1,
     *     "total": 1
     *   },
     *   "links": {
     *     "first": "http://localhost:8000/api/kantor/nomor-surat/surtug-detil?page=1",
     *     "last": "http://localhost:8000/api/kantor/nomor-surat/surtug-detil?page=1",
     *     "prev": null,
     *     "next": null
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $countQuery = SurtugDetil::query();

        // Filter by surtug_id
        if ($request->has('surtug_id')) {
            $countQuery->where('surtug_id', $request->get('surtug_id'));
        }

        // Filter by pegawai_id
        if ($request->has('pegawai_id')) {
            $countQuery->where('pegawai_id', $request->get('pegawai_id'));
        }

        // Filter by mitra_id
        if ($request->has('mitra_id')) {
            $countQuery->where('mitra_id', $request->get('mitra_id'));
        }

        $totalRecords = $countQuery->count();

        $dataQuery = SurtugDetil::query();

        // Filter by surtug_id
        if ($request->has('surtug_id')) {
            $dataQuery->where('surtug_id', $request->get('surtug_id'));
        }

        // Filter by pegawai_id
        if ($request->has('pegawai_id')) {
            $dataQuery->where('pegawai_id', $request->get('pegawai_id'));
        }

        // Filter by mitra_id
        if ($request->has('mitra_id')) {
            $dataQuery->where('mitra_id', $request->get('mitra_id'));
        }

        // Eager load relationships
        $dataQuery->with(['pegawai', 'mitra', 'nomor']);

        $data = $dataQuery->fastPaginate($perPage);

        $extras = [
            'pagination_info' => [
                'total_page' => $data->lastPage(),
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($data, 'Data berhasil diambil', 200, $extras);
    }

    /**
     * Membuat detail surat tugas baru.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @bodyParam surtug_id integer required ID surat tugas. Contoh: 1
     * @bodyParam pegawai_id integer ID pegawai (opsional). Contoh: 10
     * @bodyParam mitra_id integer ID mitra (opsional). Contoh: 5
     * @bodyParam grup_mitra integer Grup mitra (opsional). Contoh: 1
     * @bodyParam grup_pegawai integer Grup pegawai (opsional). Contoh: 1
     * @bodyParam penugasan_id integer ID penugasan (opsional). Contoh: 1
     * @bodyParam dasar string required Dasar penugasan. Contoh: Peraturan BPS No. 1 Tahun 2024
     * @bodyParam nama_kegiatan string required Nama kegiatan. Contoh: Sensus Penduduk
     * @bodyParam tugas_sebagai string Tugas sebagai (opsional). Contoh: Petugas Lapangan
     * @bodyParam hari integer required Jumlah hari. Contoh: 5
     * @bodyParam wilayah_kerja string required Wilayah kerja. Contoh: Kecamatan A
     * @bodyParam tgl_mulai date required Tanggal mulai (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam jenis_kendaraan string Jenis kendaraan (opsional). Contoh: Motor
     * @bodyParam no_dipa string required Nomor DIPA. Contoh: DIPA-2024-001
     * @bodyParam isOrganik boolean required Status organik. Contoh: true
     * @bodyParam sppd boolean Status SPPD (opsional). Contoh: false
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Detail surat tugas berhasil disimpan",
     *   "data": {
     *     "id": 1,
     *     "surtug_id": 1,
     *     "pegawai_id": 10,
     *     "mitra_id": null,
     *     "grup_mitra": null,
     *     "grup_pegawai": 1,
     *     "penugasan_id": null,
     *     "dasar": "Peraturan BPS No. 1 Tahun 2024",
     *     "nama_kegiatan": "Sensus Penduduk",
     *     "tugas_sebagai": "Petugas Lapangan",
     *     "hari": 5,
     *     "wilayah_kerja": "Kecamatan A",
     *     "tgl_mulai": "2024-01-15",
     *     "jenis_kendaraan": "Motor",
     *     "no_dipa": "DIPA-2024-001",
     *     "isOrganik": true,
     *     "sppd": false,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "surtug_id": ["Field surtug_id wajib diisi."],
     *     "dasar": ["Field dasar wajib diisi."],
     *     "nama_kegiatan": ["Field nama_kegiatan wajib diisi."]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat tugas not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create detail surat tugas",
     *   "errors": "Error message details"
     * }
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'surtug_id' => 'required|integer|exists:surat_tugas,id',
            'pegawai_id' => 'nullable|integer|exists:profil_pegawai,id',
            'mitra_id' => 'nullable|integer|exists:mitra_kepka,id',
            'grup_mitra' => 'nullable|integer',
            'grup_pegawai' => 'nullable|integer',
            'penugasan_id' => 'nullable|integer|exists:penugasan,id',
            'dasar' => 'required|string',
            'nama_kegiatan' => 'required|string',
            'tugas_sebagai' => 'nullable|string',
            'hari' => 'required|integer',
            'wilayah_kerja' => 'required|string',
            'tgl_mulai' => 'required|date',
            'jenis_kendaraan' => 'nullable|string',
            'no_dipa' => 'required|string',
            'isOrganik' => 'required|boolean',
            'sppd' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            // Validate that surtug_id exists
            $suratTugas = SuratTugas::find($request->surtug_id);
            if (!$suratTugas) {
                return $this->error('Surat tugas not found', null, 404);
            }

            $surtugDetil = SurtugDetil::create($request->all());

            // Load relationships
            $surtugDetil->load(['pegawai', 'mitra', 'nomor']);

            return $this->success($surtugDetil, 'Detail surat tugas berhasil disimpan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create detail surat tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail surat tugas berdasarkan ID.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @urlParam id integer required ID detail surat tugas. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": {
     *     "id": 1,
     *     "surtug_id": 1,
     *     "pegawai_id": 10,
     *     "mitra_id": null,
     *     "grup_mitra": null,
     *     "grup_pegawai": 1,
     *     "penugasan_id": null,
     *     "dasar": "Peraturan BPS No. 1 Tahun 2024",
     *     "nama_kegiatan": "Sensus Penduduk",
     *     "tugas_sebagai": "Petugas Lapangan",
     *     "hari": 5,
     *     "wilayah_kerja": "Kecamatan A",
     *     "tgl_mulai": "2024-01-15",
     *     "jenis_kendaraan": "Motor",
     *     "no_dipa": "DIPA-2024-001",
     *     "isOrganik": true,
     *     "sppd": false,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z",
     *     "pegawai": {
     *       "id": 10,
     *       "nama": "John Doe"
     *     },
     *     "mitra": null,
     *     "nomor": {
     *       "id": 1,
     *       "no_surat": "0001/ST-100/2024"
     *     }
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Detail surat tugas not found"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $surtugDetil = SurtugDetil::with(['pegawai', 'mitra', 'nomor'])->find($id);

            if (!$surtugDetil) {
                return $this->error('Detail surat tugas not found', null, 404);
            }

            return $this->success($surtugDetil, 'Data retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Detail surat tugas not found', $e->getMessage(), 404);
        }
    }

    /**
     * Mengupdate detail surat tugas berdasarkan ID.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @urlParam id integer required ID detail surat tugas yang akan diupdate. Contoh: 1
     *
     * @bodyParam surtug_id integer required ID surat tugas. Contoh: 1
     * @bodyParam pegawai_id integer ID pegawai (opsional). Contoh: 10
     * @bodyParam mitra_id integer ID mitra (opsional). Contoh: 5
     * @bodyParam grup_mitra integer Grup mitra (opsional). Contoh: 1
     * @bodyParam grup_pegawai integer Grup pegawai (opsional). Contoh: 1
     * @bodyParam penugasan_id integer ID penugasan (opsional). Contoh: 1
     * @bodyParam dasar string required Dasar penugasan. Contoh: Peraturan BPS No. 1 Tahun 2024
     * @bodyParam nama_kegiatan string required Nama kegiatan. Contoh: Sensus Penduduk
     * @bodyParam tugas_sebagai string Tugas sebagai (opsional). Contoh: Petugas Lapangan
     * @bodyParam hari integer required Jumlah hari. Contoh: 5
     * @bodyParam wilayah_kerja string required Wilayah kerja. Contoh: Kecamatan A
     * @bodyParam tgl_mulai date required Tanggal mulai (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam jenis_kendaraan string Jenis kendaraan (opsional). Contoh: Motor
     * @bodyParam no_dipa string required Nomor DIPA. Contoh: DIPA-2024-001
     * @bodyParam isOrganik boolean required Status organik. Contoh: true
     * @bodyParam sppd boolean Status SPPD (opsional). Contoh: false
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Detail surat tugas berhasil diupdate",
     *   "data": {
     *     "id": 1,
     *     "surtug_id": 1,
     *     "pegawai_id": 10,
     *     "mitra_id": null,
     *     "grup_mitra": null,
     *     "grup_pegawai": 1,
     *     "penugasan_id": null,
     *     "dasar": "Peraturan BPS No. 1 Tahun 2024",
     *     "nama_kegiatan": "Sensus Penduduk",
     *     "tugas_sebagai": "Petugas Lapangan",
     *     "hari": 5,
     *     "wilayah_kerja": "Kecamatan A",
     *     "tgl_mulai": "2024-01-15",
     *     "jenis_kendaraan": "Motor",
     *     "no_dipa": "DIPA-2024-001",
     *     "isOrganik": true,
     *     "sppd": false,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Detail surat tugas not found"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "dasar": ["The dasar field is required."],
     *     "nama_kegiatan": ["The nama_kegiatan field is required."]
     *   }
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to update detail surat tugas",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'surtug_id' => 'required|integer|exists:surat_tugas,id',
            'pegawai_id' => 'nullable|integer|exists:profil_pegawai,id',
            'mitra_id' => 'nullable|integer|exists:mitra_kepka,id',
            'grup_mitra' => 'nullable|integer',
            'grup_pegawai' => 'nullable|integer',
            'penugasan_id' => 'nullable|integer|exists:penugasan,id',
            'dasar' => 'required|string',
            'nama_kegiatan' => 'required|string',
            'tugas_sebagai' => 'nullable|string',
            'hari' => 'required|integer',
            'wilayah_kerja' => 'required|string',
            'tgl_mulai' => 'required|date',
            'jenis_kendaraan' => 'nullable|string',
            'no_dipa' => 'required|string',
            'isOrganik' => 'required|boolean',
            'sppd' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $surtugDetil = SurtugDetil::find($id);
            if (!$surtugDetil) {
                return $this->error('Detail surat tugas not found', null, 404);
            }

            // Validate that surtug_id exists
            $suratTugas = SuratTugas::find($request->surtug_id);
            if (!$suratTugas) {
                return $this->error('Surat tugas not found', null, 404);
            }

            $surtugDetil->update($request->all());
            $surtugDetil->load(['pegawai', 'mitra', 'nomor']);

            return $this->success($surtugDetil, 'Detail surat tugas berhasil diupdate');

        } catch (\Exception $e) {
            return $this->error('Failed to update detail surat tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Menyimpan detail surat tugas sebagai data baru.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @bodyParam surtug_id integer required ID surat tugas. Contoh: 1
     * @bodyParam pegawai_id integer ID pegawai (opsional). Contoh: 10
     * @bodyParam mitra_id integer ID mitra (opsional). Contoh: 5
     * @bodyParam grup_mitra integer Grup mitra (opsional). Contoh: 1
     * @bodyParam grup_pegawai integer Grup pegawai (opsional). Contoh: 1
     * @bodyParam penugasan_id integer ID penugasan (opsional). Contoh: 1
     * @bodyParam dasar string required Dasar penugasan. Contoh: Peraturan BPS No. 1 Tahun 2024
     * @bodyParam nama_kegiatan string required Nama kegiatan. Contoh: Sensus Penduduk
     * @bodyParam tugas_sebagai string Tugas sebagai (opsional). Contoh: Petugas Lapangan
     * @bodyParam hari integer required Jumlah hari. Contoh: 5
     * @bodyParam wilayah_kerja string required Wilayah kerja. Contoh: Kecamatan A
     * @bodyParam tgl_mulai date required Tanggal mulai (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam jenis_kendaraan string Jenis kendaraan (opsional). Contoh: Motor
     * @bodyParam no_dipa string required Nomor DIPA. Contoh: DIPA-2024-001
     * @bodyParam isOrganik boolean required Status organik. Contoh: true
     * @bodyParam sppd boolean Status SPPD (opsional). Contoh: false
     *
     * @response 201 {
     *   "success": true,
     *   "message": "Detail surat tugas berhasil disimpan",
     *   "data": {
     *     "id": 1,
     *     "surtug_id": 1,
     *     "pegawai_id": 10,
     *     "mitra_id": null,
     *     "grup_mitra": null,
     *     "grup_pegawai": 1,
     *     "penugasan_id": null,
     *     "dasar": "Peraturan BPS No. 1 Tahun 2024",
     *     "nama_kegiatan": "Sensus Penduduk",
     *     "tugas_sebagai": "Petugas Lapangan",
     *     "hari": 5,
     *     "wilayah_kerja": "Kecamatan A",
     *     "tgl_mulai": "2024-01-15",
     *     "jenis_kendaraan": "Motor",
     *     "no_dipa": "DIPA-2024-001",
     *     "isOrganik": true,
     *     "sppd": false,
     *     "created_at": "2024-01-15T00:00:00.000000Z",
     *     "updated_at": "2024-01-15T00:00:00.000000Z"
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "dasar": ["The dasar field is required."],
     *     "nama_kegiatan": ["The nama_kegiatan field is required."]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat tugas not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create detail surat tugas",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function insert(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'surtug_id' => 'required|integer|exists:surat_tugas,id',
            'pegawai_id' => 'nullable|integer|exists:profil_pegawai,id',
            'mitra_id' => 'nullable|integer|exists:mitra_kepka,id',
            'grup_mitra' => 'nullable|integer',
            'grup_pegawai' => 'nullable|integer',
            'penugasan_id' => 'nullable|integer|exists:penugasan,id',
            'dasar' => 'required|string',
            'nama_kegiatan' => 'required|string',
            'tugas_sebagai' => 'nullable|string',
            'hari' => 'required|integer',
            'wilayah_kerja' => 'required|string',
            'tgl_mulai' => 'required|date',
            'jenis_kendaraan' => 'nullable|string',
            'no_dipa' => 'required|string',
            'isOrganik' => 'required|boolean',
            'sppd' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            // Validate that surtug_id exists
            $suratTugas = SuratTugas::find($request->surtug_id);
            if (!$suratTugas) {
                return $this->error('Surat tugas not found', null, 404);
            }

            $surtugDetil = SurtugDetil::create($request->all());
            $surtugDetil->load(['pegawai', 'mitra', 'nomor']);

            return $this->success($surtugDetil, 'Detail surat tugas berhasil disimpan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create detail surat tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan opsi kegiatan pada tahun berjalan yang memiliki penugasan.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data berhasil diambil",
     *   "data": []
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     */
    public function kegiatanOptions(Request $request)
    {
        $tahun = $request->get('tahun', Carbon::now()->format('Y'));

        $query = Kegiatan::select('id', 'tahun', 'nama')
            ->where('tahun', $tahun)
            ->whereHas('penugasan');

        if ($request->filled('nama')) {
            $nama = $request->get('nama');
            $query->where('nama', 'like', '%' . $nama . '%');
        } else {
            $query->inRandomOrder()->limit(10);
        }

        $kegiatan = $query->get();

        return $this->success($kegiatan, 'Data berhasil diambil');
    }

    /**
     * Menampilkan opsi mitra berdasarkan kegiatan.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @queryParam kegiatan_id integer required ID kegiatan. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data berhasil diambil",
     *   "data": [
     *     {
     *       "id": 1,
     *       "mitra_id": 10,
     *       "mitra": {
     *         "id": 10,
     *         "nama_lengkap": "Nama Mitra"
     *       }
     *     }
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     */
    public function mitraPenugasanOptions(Request $request)
    {
        $penugasan = Penugasan::select('id', 'mitra_id')
            ->where('kegiatan_id', $request->kegiatan_id)
            ->with('mitra:id,nama_lengkap')
            ->get();

        return $this->success($penugasan, 'Data berhasil diambil');
    }

    /**
     * Menampilkan opsi pegawai dengan status NULL.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data berhasil diambil",
     *   "data": []
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     */
    public function pegawaiOptions()
    {
        $pegawai = Pegawai::whereNull('status')->get();

        return $this->success($pegawai, 'Data berhasil diambil');
    }

    /**
     * Menampilkan opsi mitra yang dapat dicari berdasarkan kecamatan atau nama.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @queryParam search string Pencarian berdasarkan keca atau nama_lengkap. Contoh: Cibinong
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data berhasil diambil",
     *   "data": [
     *     {
     *       "id": 1,
     *       "nama_lengkap": "Nama Mitra",
     *       "keca": "Kecamatan"
     *     }
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     */
    public function mitraOptions(Request $request)
    {
        $query = Mitra::select('id', 'nama_lengkap', 'keca');

        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('keca', 'like', '%' . $search . '%')
                  ->orWhere('nama_lengkap', 'like', '%' . $search . '%');
            });
        } else {
            $query->inRandomOrder()->limit(10);
        }

        $mitra = $query->get();

        return $this->success($mitra, 'Data berhasil diambil');
    }

    /**
     * Menghapus detail surat tugas berdasarkan ID.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @urlParam id integer required ID detail surat tugas yang akan dihapus. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Detail surat tugas berhasil dihapus",
     *   "data": null
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Detail surat tugas not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to delete detail surat tugas",
     *   "errors": "Error message details"
     * }
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        try {
            $surtugDetil = SurtugDetil::find($id);

            if (!$surtugDetil) {
                return $this->error('Detail surat tugas not found', null, 404);
            }

            $surtugDetil->delete();

            return $this->success(null, 'Detail surat tugas berhasil dihapus');

        } catch (\Exception $e) {
            return $this->error('Failed to delete detail surat tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail surat tugas berdasarkan ID surat tugas.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @urlParam surtug_id integer required ID surat tugas. Contoh: 1
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Data retrieved successfully",
     *   "data": [
     *     {
     *       "id": 1,
     *       "surtug_id": 1,
     *       "pegawai_id": 10,
     *       "mitra_id": null,
     *       "dasar": "Peraturan BPS No. 1 Tahun 2024",
     *       "nama_kegiatan": "Sensus Penduduk",
     *       "hari": 5,
     *       "wilayah_kerja": "Kecamatan A",
     *       "tgl_mulai": "2024-01-15",
     *       "no_dipa": "DIPA-2024-001",
     *       "isOrganik": true,
     *       "sppd": false
     *     }
     *   ]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat tugas not found"
     * }
     *
     * @param  int  $surtug_id
     * @return \Illuminate\Http\Response
     */
    public function getBySurtugId($surtug_id)
    {
        try {
            // Validate that surtug_id exists
            $suratTugas = SuratTugas::find($surtug_id);
            if (!$suratTugas) {
                return $this->error('Surat tugas not found', null, 404);
            }

            $surtugDetil = SurtugDetil::with(['pegawai', 'mitra'])
                                     ->where('surtug_id', $surtug_id)
                                     ->get();

            return $this->success($surtugDetil, 'Data retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve detail surat tugas', $e->getMessage(), 500);
        }
    }

    /**
     * Bulk create surat tugas detil for multiple mitra.
     *
     * @group Detail Surat Tugas
     * @authenticated
     *
     * @bodyParam surtug_id integer required ID surat tugas. Contoh: 1
     * @bodyParam mitra_ids array required Array of mitra IDs. Contoh: [1, 2, 3]
     * @bodyParam kegiatan_id integer required ID kegiatan. Contoh: 1
     * @bodyParam dasar string required Dasar penugasan. Contoh: Peraturan BPS No. 1 Tahun 2024
     * @bodyParam nama_kegiatan string required Nama kegiatan. Contoh: Sensus Penduduk
     * @bodyParam tugas_sebagai string Tugas sebagai (opsional). Contoh: Petugas Lapangan
     * @bodyParam hari integer required Jumlah hari. Contoh: 5
     * @bodyParam wilayah_kerja string required Wilayah kerja. Contoh: Kecamatan A
     * @bodyParam tgl_mulai date required Tanggal mulai (format: YYYY-MM-DD). Contoh: 2024-01-15
     * @bodyParam no_dipa string required Nomor DIPA. Contoh: DIPA-2024-001
     *
     * @response 200 {
     *   "success": true,
     *   "message": "Berhasil menambahkan 3 detail surat tugas",
     *   "data": {
     *     "created": 3,
     *     "failed": 0,
     *     "details": [
     *       {
     *         "id": 1,
     *         "surtug_id": 1,
     *         "mitra_id": 1,
     *         "penugasan_id": 10,
     *         "dasar": "Peraturan BPS No. 1 Tahun 2024",
     *         "nama_kegiatan": "Sensus Penduduk",
     *         "hari": 5,
     *         "wilayah_kerja": "Kecamatan A",
     *         "tgl_mulai": "2024-01-15",
     *         "no_dipa": "DIPA-2024-001",
     *         "isOrganik": false,
     *         "sppd": false
     *       }
     *     ]
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     * @response 422 {
     *   "success": false,
     *   "message": "Validation error",
     *   "errors": {
     *     "surtug_id": ["Field surtug_id wajib diisi."],
     *     "mitra_ids": ["Field mitra_ids wajib diisi."],
     *     "kegiatan_id": ["Field kegiatan_id wajib diisi."]
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Surat tugas not found"
     * }
     * @response 500 {
     *   "success": false,
     *   "message": "Failed to create bulk surat tugas detil",
     *   "errors": "Error message details"
     * }
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function bulkMitra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'surtug_id' => 'required|integer|exists:surat_tugas,id',
            'mitra_ids' => 'required|array|min:1',
            'mitra_ids.*' => 'integer', // Validation handled in loop for partial success
            'kegiatan_id' => 'required|integer|exists:kegiatan,id',
            'dasar' => 'required|string',
            'nama_kegiatan' => 'required|string',
            'tugas_sebagai' => 'nullable|string',
            'hari' => 'required|integer',
            'wilayah_kerja' => 'required|string',
            'tgl_mulai' => 'required|date',
            'no_dipa' => 'required|string',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            // Validate that surtug_id exists
            $suratTugas = SuratTugas::find($request->surtug_id);
            if (!$suratTugas) {
                return $this->error('Surat tugas not found', null, 404);
            }

            $mitraIds = $request->mitra_ids;
            $createdDetails = [];
            $failedDetails = [];

            foreach ($mitraIds as $mitraId) {
                try {
                    // Validate mitra exists
                    if (!Mitra::find($mitraId)) {
                        throw new \Exception("Mitra with ID {$mitraId} not found");
                    }

                    // Get penugasan_id from Penugasan where mitra_id and kegiatan_id match
                    $penugasan = Penugasan::where('mitra_id', $mitraId)
                                         ->where('kegiatan_id', $request->kegiatan_id)
                                         ->first();

                    $surtugDetilData = [
                        'surtug_id' => $request->surtug_id,
                        'mitra_id' => $mitraId,
                        'penugasan_id' => $penugasan ? $penugasan->id : null,
                        'pegawai_id' => null,
                        'grup_mitra' => null,
                        'grup_pegawai' => null,
                        'dasar' => $request->dasar,
                        'nama_kegiatan' => $request->nama_kegiatan,
                        'tugas_sebagai' => $request->tugas_sebagai,
                        'hari' => $request->hari,
                        'wilayah_kerja' => $request->wilayah_kerja,
                        'tgl_mulai' => $request->tgl_mulai,
                        'jenis_kendaraan' => null,
                        'no_dipa' => $request->no_dipa,
                        'isOrganik' => false,
                        'sppd' => false,
                    ];

                    $surtugDetil = SurtugDetil::create($surtugDetilData);
                    $createdDetails[] = $surtugDetil;

                } catch (\Exception $e) {
                    $failedDetails[] = [
                        'mitra_id' => $mitraId,
                        'error' => $e->getMessage(),
                    ];
                }
            }

            return $this->success([
                'created' => count($createdDetails),
                'failed' => count($failedDetails),
                'details' => $createdDetails,
                'failed_details' => $failedDetails,
            ], 'Berhasil menambahkan ' . count($createdDetails) . ' detail surat tugas');

        } catch (\Exception $e) {
            return $this->error('Failed to create bulk surat tugas detil', $e->getMessage(), 500);
        }
    }
}
