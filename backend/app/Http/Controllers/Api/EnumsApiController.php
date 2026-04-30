<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Enums\FungsiType;
use App\Enums\JabatanTugasType;
use App\Enums\JenisKegiatanType;
use App\Enums\SatuanType;
use App\Enums\PangkatType;
use App\Enums\GolonganType;
use App\Enums\JabatanType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class EnumsApiController extends BaseApiController
{
    /**
     * Menampilkan daftar enum yang tersedia.
     *
     * Endpoint ini mengembalikan daftar enum yang dapat digunakan sebagai opsi (dropdown) di sisi klien.
     * Akses terbatas untuk pengguna terotentikasi.
     *
     * @group Enums
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "fungsi_types": ["..."],
     *     "jabatan_tugas_types": ["..."],
     *     "jenis_kegiatan_types": ["..."],
     *     "satuan_types": ["..."],
     *     "pangkat_types": ["..."],
     *     "golongan_types": ["..."],
     *     "jabatan_types": ["..."]
     *   }
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache all enums for 24 hours
        // Enum values never change, so 24h TTL is safe and provides max performance
        $data = Cache::remember('enums_all', 86400, function () {
            return [
                'fungsi_types' => array_column(FungsiType::cases(), 'value'),
                'jabatan_tugas_types' => array_column(JabatanTugasType::cases(), 'value'),
                'jenis_kegiatan_types' => array_column(JenisKegiatanType::cases(), 'value'),
                'satuan_types' => array_column(SatuanType::cases(), 'value'),
                'pangkat_types' => array_column(PangkatType::cases(), 'value'),
                'golongan_types' => array_column(GolonganType::cases(), 'value'),
                'jabatan_types' => array_column(JabatanType::cases(), 'value'),
            ];
        });

        return $this->success($data, 'Enums retrieved successfully');
    }

    /**
     * Enum FungsiType.
     *
     * Endpoint ini mengembalikan daftar nilai dari enum FungsiType.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Enums
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["...", "...", "..."]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function fungsiTypes()
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache enum values for 24 hours
        // Enum values never change, so 24h TTL is safe and provides max performance
        $data = Cache::remember('enums_fungsi_types', 86400, function () {
            return array_column(FungsiType::cases(), 'value');
        });

        return $this->success($data, 'FungsiType enum retrieved successfully');
    }

    /**
     * Enum JabatanTugasType.
     *
     * Endpoint ini mengembalikan daftar nilai dari enum JabatanTugasType.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Enums
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["PCL", "PML", "OPERATOR", "..."]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jabatanTugasTypes()
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache enum values for 24 hours
        // Enum values never change, so 24h TTL is safe and provides max performance
        $data = Cache::remember('enums_jabatan_tugas_types', 86400, function () {
            return array_column(JabatanTugasType::cases(), 'value');
        });

        return $this->success($data, 'JabatanTugasType enum retrieved successfully');
    }

    /**
     * Enum JenisKegiatanType.
     *
     * Endpoint ini mengembalikan daftar nilai dari enum JenisKegiatanType.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Enums
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["Rutin", "Proyek", "..."]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jenisKegiatanTypes()
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache enum values for 24 hours
        // Enum values never change, so 24h TTL is safe and provides max performance
        $data = Cache::remember('enums_jenis_kegiatan_types', 86400, function () {
            return array_column(JenisKegiatanType::cases(), 'value');
        });

        return $this->success($data, 'JenisKegiatanType enum retrieved successfully');
    }

    /**
     * Enum SatuanType.
     *
     * Endpoint ini mengembalikan daftar nilai dari enum SatuanType.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Enums
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["Dokumen", "Kegiatan", "Responden", "..."]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function satuanTypes()
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache enum values for 24 hours
        // Enum values never change, so 24h TTL is safe and provides max performance
        $data = Cache::remember('enums_satuan_types', 86400, function () {
            return array_column(SatuanType::cases(), 'value');
        });

        return $this->success($data, 'SatuanType enum retrieved successfully');
    }

    /**
     * Enum PangkatType.
     *
     * Endpoint ini mengembalikan daftar nilai dari enum PangkatType.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Enums
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["Pembina", "Penata", "Pengatur", "..."]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function pangkatTypes()
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache enum values for 24 hours
        // Enum values never change, so 24h TTL is safe and provides max performance
        $data = Cache::remember('enums_pangkat_types', 86400, function () {
            return array_column(PangkatType::cases(), 'value');
        });

        return $this->success($data, 'PangkatType enum retrieved successfully');
    }

    /**
     * Enum GolonganType.
     *
     * Endpoint ini mengembalikan daftar nilai dari enum GolonganType.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Enums
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["IV", "III", "II", "I"]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function golonganTypes()
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache enum values for 24 hours
        // Enum values never change, so 24h TTL is safe and provides max performance
        $data = Cache::remember('enums_golongan_types', 86400, function () {
            return array_column(GolonganType::cases(), 'value');
        });

        return $this->success($data, 'GolonganType enum retrieved successfully');
    }

    /**
     * Enum JabatanType.
     *
     * Endpoint ini mengembalikan daftar nilai dari enum JabatanType.
     * Hanya pengguna yang sudah login yang dapat mengakses endpoint ini.
     *
     * @group Enums
     * @authenticated
     *
     * @response 200 {
     *   "success": true,
     *   "data": ["Kepala BPS", "Kepala Bagian", "..."]
     * }
     * @response 401 {
     *   "success": false,
     *   "message": "Unauthorized"
     * }
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function jabatanTypes()
    {
        /** @var \App\Models\User $authUser */
        $authUser = Auth::user();
        if (!$authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache enum values for 24 hours
        // Enum values never change, so 24h TTL is safe and provides max performance
        $data = Cache::remember('enums_jabatan_types', 86400, function () {
            return array_column(JabatanType::cases(), 'value');
        });

        return $this->success($data, 'JabatanType enum retrieved successfully');
    }
}
