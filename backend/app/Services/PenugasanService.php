<?php

namespace App\Services;

use App\Models\Penugasan;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\User;
use App\Enums\JabatanTugasType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Spatie\QueryBuilder\AllowedFilter;
use Carbon\Carbon;

class PenugasanService
{
    public function listPenugasan(array $params): array
    {
        $perPage = $params['per_page'] ?? 10;
        $filterBlnBayar = $params['filter']['bln_bayar'] ?? null;

        $query = QueryBuilder::for(Penugasan::class)
            ->when($filterBlnBayar, function ($q) use ($filterBlnBayar) {
                $filterYear = Carbon::parse($filterBlnBayar)->year;

                $q->whereHas('kegiatan', fn($subQuery) => $subQuery->where('tahun', $filterYear));
            })
            ->allowedFilters([
                AllowedFilter::exact('kegiatan_id'),
                'jabatan_tugas',
                AllowedFilter::exact('mitra_id'),
                AllowedFilter::exact('bln_bayar'),
            ])
            ->with(['kegiatan:id,tahun,fungsi,nama', 'mitra:id,nama_lengkap,sobat_id', 'pegawai:id,nama,nip']);

        $totalRecords = $query->count();
        $totalNilai = (clone $query)->sum('nilai');
        $penugasan = $query->fastPaginate($perPage);

        return [
            'data' => $penugasan,
            'pagination_info' => [
                'total_page' => $penugasan->lastPage(),
                'total_records' => $totalRecords,
            ],
            'total_nilai' => $totalNilai,
        ];
    }

    public function createPenugasan(array $data, int $userId): Penugasan
    {
        if (isset($data['bln_bayar'])) {
            $data['bln_bayar'] = Carbon::parse($data['bln_bayar'])->firstOfMonth()->format('Y-m-d');
        }

        $data['created_by'] = $userId;
        $data['nilai'] = $this->calculateNilai($data);

        $penugasan = Penugasan::create($data);
        $this->invalidatePenugasanCaches();
        return $penugasan;
    }

    public function updatePenugasan(Penugasan $penugasan, array $data): Penugasan
    {
        if (isset($data['bln_bayar'])) {
            $data['bln_bayar'] = Carbon::parse($data['bln_bayar'])->firstOfMonth()->format('Y-m-d');
        }

        $calcData = array_merge($penugasan->toArray(), $data);
        $data['nilai'] = $this->calculateNilai($calcData);

        $penugasan->update($data);
        $this->invalidatePenugasanCaches();
        return $penugasan->refresh();
    }

    public function deletePenugasan(Penugasan $penugasan): void
    {
        $penugasan->delete();
        $this->invalidatePenugasanCaches();
    }

    public function insertPenugasan(Penugasan $existing, array $data, int $userId): Penugasan
    {
        $newData = array_merge($existing->toArray(), $data);
        unset($newData['id'], $newData['created_at'], $newData['updated_at']);

        $newData['created_by'] = $userId;
        if (isset($data['bln_bayar'])) {
            $newData['bln_bayar'] = Carbon::parse($data['bln_bayar'])->firstOfMonth()->format('Y-m-d');
        }

        $newData['nilai'] = $this->calculateNilai($newData);

        $penugasan = Penugasan::create($newData);
        $this->invalidatePenugasanCaches();
        return $penugasan;
    }

    public function getFilters(): array
    {
        return Cache::remember('penugasan_filters_list', 86400, function () {
            $sixMonthsAgo = date('Y-m-d', strtotime('-12 months'));
            $blnBayarList = Penugasan::whereNotNull('bln_bayar')
                ->where('bln_bayar', '>=', $sixMonthsAgo)
                ->groupBy('bln_bayar')
                ->orderBy('bln_bayar', 'DESC')
                ->pluck('bln_bayar')
                ->map(fn($date) => $date instanceof \DateTime ? $date->format('Y-m-d') : $date)
                ->values()
                ->toArray();

            $kegiatanList = Kegiatan::select('id', 'nama')
                ->where('tahun', date('Y'))
                ->whereIn('id', fn($q) => $q->select('kegiatan_id')->from('penugasan')->whereNotNull('kegiatan_id'))
                ->get();

            return [
                'blnBayarList' => $blnBayarList,
                'kegiatanList' => $kegiatanList,
            ];
        });
    }

    public function getMitraOptions(): \Illuminate\Support\Collection
    {
        return Cache::remember('penugasan_mitra_options', 3600, function () {
            return Mitra::select('id', 'nama_lengkap')
                ->whereIn('id', fn($q) => $q->select('mitra_id')->from('penugasan')->whereNotNull('mitra_id'))
                ->get();
        });
    }

    public function getFormOptions(): array
    {
        return [
            'jabatanTugasOptions' => array_column(JabatanTugasType::cases(), 'value'),
            'fungsiOptions' => Kegiatan::groupBy('fungsi')->orderBy('fungsi')->pluck('fungsi'),
        ];
    }

    public function getKegiatanOptions(string $fungsi): array
    {
        return Kegiatan::select('id', 'nama')
            ->where('fungsi', $fungsi)
            ->where('tahun', date('Y'))
            ->where('tgl_selesai', '>', now())
            ->get()
            ->toArray();
    }

    protected function calculateNilai(array $data): int
    {
        if (!isset($data['kegiatan_id'], $data['jabatan_tugas'], $data['volume'])) return 0;

        $kegiatan = Kegiatan::where('id', $data['kegiatan_id'])->select('rate_pcl', 'rate_pml', 'rate_entri')->first();
        if (!$kegiatan) return 0;

        $volume = (int)$data['volume'];
        return match ($data['jabatan_tugas']) {
            'PCL' => $volume * ($kegiatan->rate_pcl ?? 0),
            'PML' => $volume * ($kegiatan->rate_pml ?? 0),
            'OPERATOR' => $volume * ($kegiatan->rate_entri ?? 0),
            default => 0,
        };
    }

    public function getExportData(?string $blnBayar): \Illuminate\Support\Collection
    {
        $filterYear = $blnBayar ? Carbon::parse($blnBayar)->year : date('Y');

        $query = Penugasan::query()
            ->whereHas('kegiatan', fn($q) => $q->where('tahun', $filterYear))
            ->when($blnBayar, fn($q) => $q->where('bln_bayar', Carbon::parse($blnBayar)->firstOfMonth()->format('Y-m-d')))
            ->with(['kegiatan:id,tahun,fungsi,nama', 'mitra:id,nama_lengkap,sobat_id', 'pegawai:id,nama,nip'])
            ->orderBy('bln_bayar')
            ->orderBy('kegiatan_id');

        return $query->get();
    }

    public function invalidatePenugasanCaches(): void
    {
        Cache::forget('penugasan_filters_list');
        Cache::forget('penugasan_mitra_options');
        $years = DB::table('kegiatan')->distinct()->pluck('tahun');
        foreach ($years as $year) {
            Cache::forget("kegiatan_statistics_{$year}");
        }
    }
}
