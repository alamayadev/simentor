<?php

namespace App\Services;

use App\Enums\GolonganType;
use App\Enums\JabatanType;
use App\Enums\PangkatType;
use App\Models\Pegawai;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Spatie\QueryBuilder\QueryBuilder;

class PegawaiService
{
    public function listPegawai(array $params): array
    {
        $perPage = $params['per_page'] ?? 10;
        $search = $params['search'] ?? null;

        $query = QueryBuilder::for(Pegawai::class)
            ->where(fn($q) => $q->whereNull('status')->orWhere('status', 'aktif'))
            ->with('user')
            ->allowedFilters(['pangkat', 'jabatan'])
            ->defaultSort('-kelas');

        if ($search) {
            $query->where(fn($q) => $q->where('nama', 'LIKE', "%{$search}%")->orWhere('nip', 'LIKE', "%{$search}%"));
        }

        $countQuery = Pegawai::where(fn($q) => $q->whereNull('status')->orWhere('status', 'aktif'));
        if ($search) {
            $countQuery->where(fn($q) => $q->where('nama', 'LIKE', "%{$search}%")->orWhere('nip', 'LIKE', "%{$search}%"));
        }
        $totalRecords = $countQuery->count();

        $pegawai = $query->fastPaginate($perPage);

        return [
            'data' => $pegawai,
            'pagination_info' => [
                'total_page' => $pegawai->lastPage(),
                'total_records' => $totalRecords,
            ]
        ];
    }

    public function createPegawai(array $data): Pegawai
    {
        $pegawai = Pegawai::create($data);
        $this->invalidatePegawaiDropdownCaches();
        return $pegawai;
    }

    public function updatePegawai(Pegawai $pegawai, array $data): Pegawai
    {
        $pegawai->update($data);
        $this->invalidatePegawaiDropdownCaches();
        return $pegawai;
    }

    public function deletePegawai(Pegawai $pegawai): void
    {
        $pegawai->delete();
        $this->invalidatePegawaiDropdownCaches();
    }

    public function getPangkatList()
    {
        return Cache::remember('pegawai_pangkat_list', 3600, function () {
            return Pegawai::select('pangkat')
                ->whereNotNull('pangkat')
                ->distinct()
                ->orderBy('pangkat')
                ->pluck('pangkat');
        });
    }

    public function getJabatanList()
    {
        return Cache::remember('pegawai_jabatan_list', 3600, function () {
            return Pegawai::select('jabatan')
                ->whereNotNull('jabatan')
                ->distinct()
                ->orderBy('jabatan')
                ->pluck('jabatan');
        });
    }

    public function getJabatanPangkatList()
    {
        return Cache::remember('pegawai_jabatan_pangkat_list', 3600, function () {
            return [
                'jabatanList' => Pegawai::select('jabatan')->whereNotNull('jabatan')->distinct()->orderBy('jabatan')->pluck('jabatan'),
                'pangkatList' => Pegawai::select('pangkat')->whereNotNull('pangkat')->distinct()->orderBy('pangkat')->pluck('pangkat')
            ];
        });
    }

    public function filters(): array
    {
        return [
            'pegawaiList' => Pegawai::query()
                ->whereNull('status')
                ->whereNotNull('nama')
                ->orderBy('nama')
                ->pluck('nama')
                ->all(),
            'jabatanList' => Pegawai::query()
                ->whereNull('status')
                ->whereNotNull('jabatan')
                ->distinct()
                ->orderBy('jabatan')
                ->pluck('jabatan')
                ->all(),
        ];
    }

    public function formOptions(): array
    {
        return [
            'pangkat'      => array_column(PangkatType::cases(), 'value'),
            'golongan'     => array_column(GolonganType::cases(), 'value'),
            'jabatan'      => array_column(JabatanType::cases(), 'value'),
            'userNoPegawai' => User::query()
                ->select(['id', 'name'])
                ->where('id', '<', 100)
                ->whereNotIn('id', function ($query) {
                    $query->select('user_id')
                        ->from('profil_pegawai')
                        ->whereNotNull('user_id');
                })
                ->orderBy('id')
                ->get()
                ->toArray(),
            'userPegawai'  => User::query()
                ->select(['id', 'name'])
                ->where('id', '<', 100)
                ->orderBy('id')
                ->get()
                ->toArray(),
        ];
    }

    public function hasAccess(User $user): bool
    {
        $userRoles = DB::table('model_has_roles')
            ->where('model_id', $user->id)
            ->where('model_type', User::class)
            ->join('roles', 'model_has_roles.role_id', '=', 'roles.id')
            ->pluck('roles.name');

        return !($userRoles->contains('mitra') || $userRoles->contains('user'));
    }

    public function invalidatePegawaiDropdownCaches(): void
    {
        Cache::forget('pegawai_pangkat_list');
        Cache::forget('pegawai_jabatan_list');
        Cache::forget('pegawai_jabatan_pangkat_list');
    }
}
