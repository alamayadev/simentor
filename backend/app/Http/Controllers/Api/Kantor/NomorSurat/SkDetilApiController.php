<?php

namespace App\Http\Controllers\Api\Kantor\NomorSurat;

use App\Http\Controllers\Api\BaseApiController;
use App\Models\SkDetil;
use App\Models\SkBast;
use App\Models\Penugasan;
use App\Models\Kegiatan;
use App\Models\Pegawai;
use App\Models\Mitra;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class SkDetilApiController extends BaseApiController
{
    /**
     * Menampilkan daftar detail SK dengan pagination dan filtering.
     */
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 10);

        $query = SkDetil::query();

        // Filter by sk_id
        if ($request->has('sk_id')) {
            $query->where('sk_id', $request->get('sk_id'));
        }

        // Filter by pegawai_id
        if ($request->has('pegawai_id')) {
            $query->where('pegawai_id', $request->get('pegawai_id'));
        }

        // Filter by mitra_id
        if ($request->has('mitra_id')) {
            $query->where('mitra_id', $request->get('mitra_id'));
        }

        $totalRecords = $query->count();

        // Eager load relationships
        $query->with(['pegawai', 'mitra', 'nomor', 'penugasan']);

        $data = $query->fastPaginate($perPage);

        $extras = [
            'pagination_info' => [
                'total_page' => $data->lastPage(),
                'total_records' => $totalRecords,
            ]
        ];

        return $this->success($data, 'Data berhasil diambil', 200, $extras);
    }

    /**
     * Membuat detail SK baru.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sk_id' => 'required|integer|exists:sk_bast,id',
            'pegawai_id' => 'nullable|integer|exists:profil_pegawai,id',
            'mitra_id' => 'nullable|integer|exists:mitra_kepka,id',
            'penugasan_id' => 'nullable|integer|exists:penugasan,id',
            'detil' => 'nullable|array',
            'isOrganik' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            // Validate that sk_id exists
            $skBast = SkBast::find($request->sk_id);
            if (!$skBast) {
                return $this->error('SK not found', null, 404);
            }

            $skDetil = SkDetil::create($request->all());

            // Load relationships
            $skDetil->load(['pegawai', 'mitra', 'nomor', 'penugasan']);

            return $this->success($skDetil, 'Detail SK berhasil disimpan', 201);

        } catch (\Exception $e) {
            return $this->error('Failed to create detail SK', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail SK berdasarkan ID.
     */
    public function show($id)
    {
        try {
            $skDetil = SkDetil::with(['pegawai', 'mitra', 'nomor', 'penugasan'])->find($id);

            if (!$skDetil) {
                return $this->error('Detail SK not found', null, 404);
            }

            return $this->success($skDetil, 'Data retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Detail SK not found', $e->getMessage(), 404);
        }
    }

    /**
     * Mengupdate detail SK berdasarkan ID.
     */
    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'sk_id' => 'required|integer|exists:sk_bast,id',
            'pegawai_id' => 'nullable|integer|exists:profil_pegawai,id',
            'mitra_id' => 'nullable|integer|exists:mitra_kepka,id',
            'penugasan_id' => 'nullable|integer|exists:penugasan,id',
            'detil' => 'nullable|array',
            'isOrganik' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $skDetil = SkDetil::find($id);
            if (!$skDetil) {
                return $this->error('Detail SK not found', null, 404);
            }

            // Validate that sk_id exists
            $skBast = SkBast::find($request->sk_id);
            if (!$skBast) {
                return $this->error('SK not found', null, 404);
            }

            $skDetil->update($request->all());
            $skDetil->load(['pegawai', 'mitra', 'nomor', 'penugasan']);

            return $this->success($skDetil, 'Detail SK berhasil diupdate');

        } catch (\Exception $e) {
            return $this->error('Failed to update detail SK', $e->getMessage(), 500);
        }
    }

    /**
     * Menghapus detail SK berdasarkan ID.
     */
    public function destroy($id)
    {
        try {
            $skDetil = SkDetil::find($id);

            if (!$skDetil) {
                return $this->error('Detail SK not found', null, 404);
            }

            $skDetil->delete();

            return $this->success(null, 'Detail SK berhasil dihapus');

        } catch (\Exception $e) {
            return $this->error('Failed to delete detail SK', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan detail SK berdasarkan ID SK.
     */
    public function getBySkId($sk_id)
    {
        try {
            $skBast = SkBast::find($sk_id);
            if (!$skBast) {
                return $this->error('SK not found', null, 404);
            }

            $skDetil = SkDetil::with(['pegawai', 'mitra', 'penugasan'])
                             ->where('sk_id', $sk_id)
                             ->get();

            return $this->success($skDetil, 'Data retrieved successfully');

        } catch (\Exception $e) {
            return $this->error('Failed to retrieve detail SK', $e->getMessage(), 500);
        }
    }

    /**
     * Menampilkan opsi pegawai dengan status NULL.
     */
    public function pegawaiOptions()
    {
        $pegawai = Pegawai::whereNull('status')->get();

        return $this->success($pegawai, 'Data berhasil diambil');
    }

    /**
     * Menampilkan opsi kegiatan pada tahun berjalan yang memiliki penugasan.
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
     */
    public function mitraPenugasanOptions(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kegiatan_id' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        $penugasan = Penugasan::select('id', 'mitra_id')
            ->where('kegiatan_id', $request->kegiatan_id)
            ->with('mitra:id,nama_lengkap')
            ->get();

        return $this->success($penugasan, 'Data berhasil diambil');
    }

    /**
     * Menampilkan opsi mitra yang dapat dicari berdasarkan kecamatan atau nama.
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
     * Bulk create SK detil for multiple mitra.
     */
    public function bulkMitra(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'sk_id' => 'required|integer|exists:sk_bast,id',
            'mitra_ids' => 'required|array|min:1',
            'mitra_ids.*' => 'integer',
            'kegiatan_id' => 'required|integer|exists:kegiatan,id',
            'detil' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return $this->error('Validation error', $validator->errors(), 422);
        }

        try {
            $skBast = SkBast::find($request->sk_id);
            if (!$skBast) {
                return $this->error('SK not found', null, 404);
            }

            $mitraIds = $request->mitra_ids;
            $createdDetails = [];
            $failedDetails = [];

            foreach ($mitraIds as $mitraId) {
                try {
                    if (!Mitra::find($mitraId)) {
                        throw new \Exception("Mitra with ID {$mitraId} not found");
                    }

                    // Get penugasan_id from Penugasan where mitra_id and kegiatan_id match
                    $penugasan = Penugasan::where('mitra_id', $mitraId)
                                         ->where('kegiatan_id', $request->kegiatan_id)
                                         ->first();

                    $skDetilData = [
                        'sk_id' => $request->sk_id,
                        'mitra_id' => $mitraId,
                        'penugasan_id' => $penugasan ? $penugasan->id : null,
                        'pegawai_id' => null,
                        'detil' => $request->detil,
                        'isOrganik' => false,
                    ];

                    $skDetil = SkDetil::create($skDetilData);
                    $createdDetails[] = $skDetil;

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
            ], 'Berhasil menambahkan ' . count($createdDetails) . ' detail SK');

        } catch (\Exception $e) {
            return $this->error('Failed to create bulk SK detil', $e->getMessage(), 500);
        }
    }
}
