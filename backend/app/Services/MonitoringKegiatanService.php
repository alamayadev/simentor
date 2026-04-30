<?php
namespace App\Services;

use App\Models\Desa;
use App\Models\DetilConfiguration;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\Mitra;
use App\Models\MonitoringKegiatan;
use App\Models\MonitoringKegiatanConfig;
use App\Models\Pegawai;
use App\Models\Penugasan;
use App\Models\PetaBs;
use App\Models\Sls2025;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class MonitoringKegiatanService
{
    public function listMonitoring(array $params): LengthAwarePaginator
    {
        $kegiatan_id = $params['kegiatan_id'] ?? null;
        $perPage     = $params['per_page'] ?? 10;

        $query = MonitoringKegiatan::with(['kegiatan', 'kecamatan', 'desa', 'monitoringConfig.detilConfigurations'])
            ->when($kegiatan_id, fn($q) => $q->where('kegiatan_id', $kegiatan_id));

        $paginated = QueryBuilder::for($query)
            ->allowedFilters(['kegiatan_id'])
            ->fastPaginate($perPage);

        return $paginated->through(function ($item) {
            $baseData = [
                'id'                            => $item->id,
                'fungsi'                        => $item->fungsi,
                'kegiatan_id'                   => $item->kegiatan_id,
                'kec_id'                        => $item->kec_id,
                'desa_id'                       => $item->desa_id,
                'kode_sampel'                   => $item->kode_sampel,
                'monitoring_kegiatan_config_id' => $item->monitoring_kegiatan_config_id,
                'created_at'                    => $item->created_at,
                'updated_at'                    => $item->updated_at,
                'kegiatan'                      => $item->kegiatan->nama ?? null,
                'nmkec'                         => $item->kecamatan->nmkec ?? null,
                'nmdesa'                        => $item->desa->nmdesa ?? null,
            ];

            $detilData = $item->detil_data ?? [];
            foreach ($detilData as $key => $value) {
                $baseData[$key] = $value;
            }

            return $baseData;
        });
    }

    public function createMonitoring(array $data): MonitoringKegiatan
    {
        return DB::transaction(function () use ($data) {
            $detilConfigurations = $data['detil_configurations'] ?? null;
            $createData          = collect($data)->except(['detil_configurations'])->toArray();

            $monitoring = MonitoringKegiatan::create($createData);

            if ($detilConfigurations) {
                $monitoring->directDetilConfigurations()->attach($detilConfigurations);
            }

            return $monitoring;
        });
    }

    public function updateMonitoring(MonitoringKegiatan $monitoring, array $data): MonitoringKegiatan
    {
        return DB::transaction(function () use ($monitoring, $data) {
            $detilConfigurations = $data['detil_configurations'] ?? null;
            $updateData          = collect($data)->except(['detil_configurations'])->toArray();

            $monitoring->update($updateData);

            if ($detilConfigurations !== null) {
                $monitoring->directDetilConfigurations()->sync($detilConfigurations);
            }

            return $monitoring;
        });
    }

    public function deleteMonitoring(MonitoringKegiatan $monitoring): void
    {
        $monitoring->delete();
    }

    public function getKegiatanOptions(?string $fungsi = null): Collection
    {
        $query = Kegiatan::select('id', 'nama')
            ->whereExists(fn($q) => $q->select(DB::raw(1))
                    ->from('monitoring_kegiatan_config')
                    ->whereColumn('monitoring_kegiatan_config.kegiatan_id', 'kegiatan.id'));

        if ($fungsi) {
            $query->where('fungsi', $fungsi);
        }

        return $query->get();
    }

    public function getKegiatanOptionsWithQuery(): Collection
    {
        $query = Kegiatan::select('id', 'nama', 'fungsi', 'tahun')
            ->whereHas('penugasan');

        // Support both direct parameters and Spatie's filter[] array
        $fungsi = request()->input('fungsi') ?? request()->input('filter.fungsi');
        $tahun  = request()->input('tahun') ?? request()->input('filter.tahun');

        return QueryBuilder::for($query)
            ->where(function ($q) use ($fungsi, $tahun) {
                if ($fungsi) {
                    $q->where('fungsi', $fungsi);
                }

                if ($tahun) {
                    $q->where('tahun', $tahun);
                }

            })
            ->allowedFilters(['fungsi', 'tahun'])
            ->get();
    }

    public function getAllKegiatanOptions(): Collection
    {
        return MonitoringKegiatanConfig::with('kegiatan')
            ->get()
            ->map(fn($config) => [
                'id'     => $config->kegiatan_id,
                'nama'   => $config->kegiatan->nama ?? 'Unknown',
                'fungsi' => $config->fungsi,
            ])
            ->unique('id')
            ->values();
    }

    public function getPetugasOptions(int $kegiatan_id): Collection
    {
        $mitraIds = Penugasan::where('kegiatan_id', $kegiatan_id)
            ->whereNotNull('mitra_id')
            ->pluck('mitra_id');

        return Mitra::select('id', 'nama_lengkap')
            ->whereIn('id', $mitraIds)
            ->get();
    }

    public function getSlsOptions(?string $desa_id = null): Collection
    {
        $query = Sls2025::select('id', 'nama_sls');
        if ($desa_id) {
            $query->where('desa_id', $desa_id);
        }

        return $query->get();
    }

    public function getBlokOptions(?string $desa_id = null): Collection
    {
        $query = PetaBs::select('kdbs');
        if ($desa_id) {
            $query->where('iddesa', $desa_id);
        }

        return $query->distinct('kdbs')->get();
    }

    public function getKecOptions(): Collection
    {
        return Kecamatan::select('id', 'nmkec')->get();
    }

    public function getDesaOptions(string $kec_id): Collection
    {
        return Desa::select('id', 'nmdesa')
            ->where('kecamatan_id', $kec_id)
            ->get();
    }

    public function getDetilConfiguration(int $config_id): Collection
    {
        return DetilConfiguration::where('monitoring_kegiatan_config_id', $config_id)->get();
    }

    public function getFiltersOptions(?string $fungsi = null): array
    {
        $fungsiList = MonitoringKegiatanConfig::select('fungsi')->distinct()->pluck('fungsi');

        $kegiatanIds = MonitoringKegiatanConfig::select('kegiatan_id')
            ->when($fungsi, fn($q) => $q->where('fungsi', $fungsi))
            ->distinct()
            ->pluck('kegiatan_id');

        $kegiatan = Kegiatan::whereIn('id', $kegiatanIds)->select('id', 'nama')->get();

        return [
            'fungsi'   => $fungsiList,
            'kegiatan' => $kegiatan,
        ];
    }

    public function getPengawasOptions(?string $search = null): Collection
    {
        $mitraQuery = Mitra::select('id', 'nama_lengkap as nama_pml')->selectRaw("'mitra' as tipe");
        if ($search) {
            $mitras = $mitraQuery->where('nama_lengkap', 'like', "%{$search}%")->get();
        } else {
            $mitras = $mitraQuery->inRandomOrder()->limit(5)->get();
        }

        $pegawaiQuery = Pegawai::select('id', 'nama as nama_pml')->selectRaw("'pegawai' as tipe");
        if ($search) {
            $pegawais = $pegawaiQuery->where('nama', 'like', "%{$search}%")->get();
        } else {
            $pegawais = $pegawaiQuery->inRandomOrder()->limit(5)->get();
        }

        return $mitras->concat($pegawais);
    }

    public function getSupervisorOptions(?string $search = null): Collection
    {
        $query = Pegawai::select('id', 'nama')->orderBy('nama');
        if ($search) {
            $query->where('nama', 'like', "%{$search}%");
        }

        return $query->get();
    }
}
