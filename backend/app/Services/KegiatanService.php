<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\User;
use App\Enums\SatuanType;
use App\Enums\FungsiType;
use App\Enums\JenisKegiatanType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Support\Collection;

class KegiatanService
{
    public function listKegiatan(array $params): array
    {
        $perPage = $params['per_page'] ?? 10;
        $search  = $params['search'] ?? '';
        $fungsi  = $params['fungsi'] ?? '';
        $countQuery = Kegiatan::query();
        if ($search) $countQuery->where('nama', 'like', "%{$search}%");
        if ($fungsi) $countQuery->where('fungsi', $fungsi);
        
        $hasTahunFilter = isset($params['filter']['tahun']) || isset($params['tahun']);
        if (!$hasTahunFilter) $countQuery->where('tahun', date('Y'));

        $totalRecords = $countQuery->count();

        $query = QueryBuilder::for(Kegiatan::class)
            ->allowedFilters(['tahun', 'fungsi', 'jenis_kegiatan', 'status'])
            ->allowedSorts(['created_at', 'id', 'nama'])
            ->defaultSort('-created_at', '-id')
            ->withCount('penugasan as jml_penugasan')
            ->withSum('penugasan', 'volume');

        if ($search) $query->where('nama', 'like', "%{$search}%");
        if ($fungsi) $query->where('fungsi', $fungsi);
        if (!$hasTahunFilter) $query->where('tahun', date('Y'));

        $kegiatan = $query->fastPaginate($perPage);

        return [
            'data' => $kegiatan,
            'pagination_info' => [
                'total_page' => $kegiatan->lastPage(),
                'total_records' => $totalRecords,
            ],
        ];
    }

    public function createKegiatan(array $data): Kegiatan
    {
        $kegiatan = Kegiatan::create($data);
        $this->invalidateKegiatanCaches();
        return $kegiatan;
    }

    public function updateKegiatan(Kegiatan $kegiatan, array $data): Kegiatan
    {
        $kegiatan->update($data);
        $this->invalidateKegiatanCaches();
        return $kegiatan;
    }

    public function deleteKegiatan(Kegiatan $kegiatan): void
    {
        $kegiatan->delete();
        $this->invalidateKegiatanCaches();
    }

    public function getFilterList(): array
    {
        return Cache::remember('kegiatan_filter_list', 7200, function () {
            return [
                'fungsiList' => Kegiatan::groupBy('fungsi')->orderBy('fungsi')->pluck('fungsi'),
                'tahunList'  => Kegiatan::groupBy('tahun')->orderBy('tahun')->pluck('tahun'),
                'jenisList'  => Kegiatan::groupBy('jenis_kegiatan')->orderBy('jenis_kegiatan')->pluck('jenis_kegiatan'),
                'statusList' => collect(['aktif', 'tidak dicairkan', 'dibatalkan']),
            ];
        });
    }

    public function getCalendarData(): array
    {
        $year = date('Y');
        return Cache::remember("kegiatan_calendar_{$year}", 7200, function () use ($year) {
            $baseQuery = Kegiatan::where('tahun', $year)
                ->select(['id', 'fungsi', 'nama', 'tgl_mulai', 'tgl_selesai', 'volume', 'satuan'])
                ->withSum('penugasan', 'volume');

            return [
                'kegiatan_berjalan'      => (clone $baseQuery)->where('tgl_mulai', '<=', now())->where('tgl_selesai', '>=', now())->get(),
                'kegiatan_akan_datang'   => (clone $baseQuery)->where('tgl_mulai', '>', now())->get(),
                'kegiatan_sudah_selesai' => (clone $baseQuery)->where('tgl_selesai', '<', now())->get(),
            ];
        });
    }

    public function getFormOptions(): array
    {
        return Cache::remember('kegiatan_form_options', 86400, function () {
            return [
                'satuan'         => array_column(SatuanType::cases(), 'value'),
                'fungsi'         => array_column(FungsiType::cases(), 'value'),
                'jenis_kegiatan' => array_column(JenisKegiatanType::cases(), 'value'),
                'status'         => ['aktif', 'tidak dicairkan', 'dibatalkan', 'dikerjakan organik'],
            ];
        });
    }

    public function getStatistics(): array
    {
        $year = date('Y');
        return Cache::remember("kegiatan_statistics_{$year}", 3600, function () use ($year) {
            $driver = DB::getDriverName();
            if ($driver === 'sqlite') {
                $monthExpr      = "strftime('%Y-%m', bln_bayar)";
                $monthOrderExpr = $monthExpr;
                $yearExpr       = "strftime('%Y', penugasan.bln_bayar)";
            } else {
                $monthExpr      = "DATE_FORMAT(bln_bayar, '%Y-%b')";
                $monthOrderExpr = "DATE_FORMAT(bln_bayar, '%Y-%m')";
                $yearExpr       = "YEAR(penugasan.bln_bayar)";
            }

            $statistics = Kegiatan::selectRaw("
                    tahun, fungsi,
                    SUM(COALESCE(volume, 0) * (COALESCE(rate_pcl, 0) + COALESCE(rate_pml, 0) + COALESCE(rate_entri, 0))) as total_anggaran
                ")
                ->where('tahun', $year)
                ->groupBy('fungsi', 'tahun')
                ->orderBy('tahun', 'desc')
                ->orderBy('fungsi')
                ->get();

            $penyerapan = Kegiatan::join('penugasan', 'kegiatan.id', '=', 'penugasan.kegiatan_id')
                ->selectRaw("kegiatan.tahun, kegiatan.fungsi, SUM(COALESCE(penugasan.nilai, 0)) as total_penyerapan")
                ->where('kegiatan.tahun', $year)
                ->groupBy('kegiatan.fungsi', 'kegiatan.tahun')
                ->get();

            $nilaiPerMonth = DB::table('penugasan')
                ->selectRaw("{$monthExpr} as month, SUM(nilai) as total_nilai")
                ->whereNotNull('bln_bayar')
                ->whereYear('bln_bayar', $year)
                ->groupBy(DB::raw($monthExpr))
                ->orderBy(DB::raw($monthOrderExpr), 'asc')
                ->get();

            $topMitraHonor = DB::table('penugasan')
                ->join('mitra_kepka', 'penugasan.mitra_id', '=', 'mitra_kepka.id')
                ->selectRaw("mitra_kepka.nama_lengkap, mitra_kepka.keca, {$yearExpr} as tahun, SUM(penugasan.nilai) as total_nilai")
                ->whereNotNull('penugasan.mitra_id')
                ->whereNotNull('penugasan.bln_bayar')
                ->whereYear('penugasan.bln_bayar', $year)
                ->groupBy('mitra_kepka.id', 'mitra_kepka.nama_lengkap', 'mitra_kepka.keca', DB::raw($yearExpr))
                ->orderBy('total_nilai', 'desc')
                ->limit(10)
                ->get();

            $kegiatanPenyerapan100 = Kegiatan::where('tahun', $year)
                ->withSum('penugasan', 'nilai')
                ->get()
                ->filter(function ($keg) {
                    $anggaran = ($keg->volume ?? 0) * (($keg->rate_pcl ?? 0) + ($keg->rate_pml ?? 0) + ($keg->rate_entri ?? 0));
                    return abs($anggaran - ($keg->penugasan_sum_nilai ?? 0)) < 0.01;
                })
                ->values();

            $result = $statistics->map(function ($item) use ($penyerapan) {
                $p = $penyerapan->first(fn($x) => $x->fungsi === $item->fungsi && $x->tahun === $item->tahun);
                $totalP = $p ? $p->total_penyerapan : 0;
                $percent = $item->total_anggaran > 0 ? ($totalP / $item->total_anggaran) * 100 : 0;

                return [
                    'tahun' => (int) $item->tahun,
                    'fungsi' => $item->fungsi,
                    'total_anggaran' => (int) $item->total_anggaran,
                    'total_penyerapan' => (int) $totalP,
                    'persen' => round($percent, 2),
                ];
            });

            return [
                'kegiatan_by_fungsi'       => $result,
                'nilai_penugasan_by_month' => $nilaiPerMonth->map(fn($m) => ['month' => $m->month, 'total_nilai' => (int) $m->total_nilai]),
                'top_mitra_honor'          => $topMitraHonor->map(fn($mitra) => [
                    'nama_lengkap' => $mitra->nama_lengkap,
                    'keca' => $mitra->keca,
                    'tahun' => (int) $mitra->tahun,
                    'total_nilai' => (int) $mitra->total_nilai
                ]),
                'kegiatan_penyerapan_100'  => $kegiatanPenyerapan100->map(fn($keg) => [
                    'id' => $keg->id,
                    'tahun' => $keg->tahun,
                    'fungsi' => $keg->fungsi,
                    'nama' => $keg->nama,
                    'total_nilai' => (int) $keg->penugasan_sum_nilai,
                ]),
            ];
        });
    }

    public function getKegiatanByYear(string $tahun): Collection
    {
        return QueryBuilder::for(Kegiatan::class)
            ->allowedFilters(['tahun'])
            ->where('tahun', $tahun)
            ->select(['id', 'tahun', 'fungsi', 'nama', 'tgl_mulai', 'tgl_selesai'])
            ->get();
    }

    public function invalidateKegiatanCaches(): void
    {
        Cache::forget('kegiatan_filter_list');
        Cache::forget('kegiatan_form_options');
        $years = DB::table('kegiatan')->distinct()->pluck('tahun');
        foreach ($years as $year) {
            Cache::forget("kegiatan_calendar_{$year}");
            Cache::forget("kegiatan_statistics_{$year}");
        }
    }
}
