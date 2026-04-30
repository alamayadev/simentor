<?php

namespace App\Services;

use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\Penugasan;
use App\Enums\JabatanTugasType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;

class MitraService
{
    public function listMitra(array $params): array
    {
        $perPage = $params['per_page'] ?? 10;
        $search = $params['search'] ?? null;

        $query = Mitra::withCount('penugasan');
        
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%")
                  ->orWhere('sobat_id', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        $queryBuilder = QueryBuilder::for($query)
            ->allowedFilters([
                'email', 'sobat_id', 'nama_lengkap', 'kab', 'keca', 'desa', 'nik', 'tgl_lahir', 'posisi'
            ]);
        
        $selectColumns = [
            'id', 'nama_lengkap', 'nik', 'email', 'no_telp',
            'keca', 'desa', 'posisi', 'jenis_kelamin', 'agama',
            'status_seleksi', 'status_kawin', 'pendidikan', 'sobat_id',
            'tgl_lahir'
        ];

        $mitra = $queryBuilder->select($selectColumns)
            ->withCount('penugasan')
            ->withCasts(['penugasan_count' => 'integer'])
            ->fastPaginate($perPage);

        $countQuery = QueryBuilder::for(Mitra::query())
            ->allowedFilters(['email', 'sobat_id', 'nama_lengkap', 'kab', 'keca', 'desa', 'nik', 'tgl_lahir', 'posisi']);
            
        if ($search) {
            $countQuery->where(function ($q) use ($search) {
                $q->where('nama_lengkap', 'LIKE', "%{$search}%")
                  ->orWhere('nik', 'LIKE', "%{$search}%")
                  ->orWhere('sobat_id', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }
        
        $totalRecords = $countQuery->count();
        return [
            'data' => $mitra,
            'pagination_info' => [
                'total_page' => $mitra->lastPage(),
                'total_records' => $totalRecords,
            ]
        ];
    }

    public function getFilters(array $params): array
    {
        $selectedKeca = $params['selected_keca'] ?? null;

        return [
            'kecList' => Mitra::select('keca')->whereNotNull('keca')->groupBy('keca')->pluck('keca')->toArray(),
            'posisiList' => Mitra::select('posisi')->whereNotNull('posisi')->groupBy('posisi')->pluck('posisi')->toArray(),
            'desaList' => $selectedKeca
                ? Mitra::select('desa')
                    ->whereNotNull('desa')
                    ->where('keca', $selectedKeca)
                    ->groupBy('desa')
                    ->pluck('desa')
                    ->toArray()
                : [],
        ];
    }

    public function getStatistics(): array
    {
        $currentYear = now()->year;

        $penugasanQuery = Penugasan::query()
            ->whereYear('tgl_bast', $currentYear);

        return [
            'total_mitra' => Mitra::count(),
            'mitra_aktif' => (clone $penugasanQuery)
                ->whereNotNull('mitra_id')
                ->distinct('mitra_id')
                ->count('mitra_id'),
            'rata_tugas' => round((float) ((clone $penugasanQuery)->avg('volume') ?? 0), 2),
            'tugas_terbanyak' => (int) ((clone $penugasanQuery)->max('volume') ?? 0),
        ];
    }

    public function getPenugasanOptions(): array
    {
        $jabatanTugasOptions = array_map(fn($case) => $case->value, JabatanTugasType::cases());
        $currentYear = date('Y');
        $kegiatanOptions = Kegiatan::select('id', 'nama')->where('tahun', $currentYear)->get();

        return [
            'jabatanTugasOptions' => $jabatanTugasOptions,
            'kegiatanOptions' => $kegiatanOptions
        ];
    }

    public function createPenugasan(array $data, int $userId): Penugasan
    {
        $kegiatan = Kegiatan::where('id', $data['kegiatan_id'])->select('rate_pcl', 'rate_pml', 'rate_entri')->firstOrFail();

        $nilai = 0;
        $volume = $data['volume'];
        if ($data['jabatan_tugas'] == "PCL") {
            $nilai = $volume * ($kegiatan->rate_pcl ?? 0);
        } elseif ($data['jabatan_tugas'] == "PML") {
            $nilai = $volume * ($kegiatan->rate_pml ?? 0);
        } elseif ($data['jabatan_tugas'] == "OPERATOR") {
            $nilai = $volume * ($kegiatan->rate_entri ?? 0);
        }

        $bln_bayar = Carbon::parse($data['bln_bayar'])->firstOfMonth()->format('Y-m-d');

        return Penugasan::create([
            'kegiatan_id' => $data['kegiatan_id'],
            'jabatan_tugas' => $data['jabatan_tugas'],
            'mitra_id' => $data['mitra_id'],
            'volume' => $volume,
            'nilai' => $nilai,
            'bln_bayar' => $bln_bayar,
            'created_by' => $userId,
        ]);
    }
}
