<?php
namespace App\Services;

use App\Models\Holiday;
use App\Models\Penugasan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class SpkService
{
    public function listSpk(array $params): array
    {
        $selectedbln = $params['selectedbln'] ?? '';
        $sortBy      = $params['sort_by'] ?? 'bln_bayar';
        $sortDir     = $params['sort_dir'] ?? 'DESC';
        $perPage     = isset($params['per_page']) ? (int) $params['per_page'] : 10;

        $query = Penugasan::select('mitra_id', 'bln_bayar', 'no_sk', 'tgl_sk', 'no_bast', 'tgl_bast')
            ->selectRaw('sum(nilai) as total, count(kegiatan_id) as jml_tugas, MIN(id) as id')
            ->with(['mitra:id,nama_lengkap,nik,alamat_detail'])
            ->groupBy(['mitra_id', 'bln_bayar', 'no_sk', 'tgl_sk', 'no_bast', 'tgl_bast']);
        if ($selectedbln !== '') {
            $formattedDate = $this->formatMonth($selectedbln);
            $query->where('bln_bayar', $formattedDate);
        }

        $query->orderBy($sortBy, $sortDir)->orderBy('mitra_id', 'asc');

        $paginated = $query->fastPaginate($perPage);

        return [
            'data'            => $paginated,
            'listbln'         => $this->getListBln(),
            'limit_nilai'     => DB::table('settings')->where('key', 'NILAI_MAX_SPK')->latest('tahun')->first(),
            'pagination_info' => [
                'total_page'    => $paginated->lastPage(),
                'total_records' => $paginated->total(),
            ],
        ];
    }

    public function showSpk(int $mitra_id, string $bln_bayar): array
    {
        $date      = $bln_bayar . '-01';
        $penugasan = Penugasan::where('mitra_id', $mitra_id)
            ->where('bln_bayar', $date)
            ->with(['kegiatan:id,tahun,fungsi,nama'])
            ->get();

        if ($penugasan->isEmpty()) {
            throw new \Illuminate\Database\Eloquent\ModelNotFoundException('SPK record not found');
        }

        return ['penugasan_mitra' => $penugasan];
    }

    public function updateSpk(int $mitra_id, string $bln_bayar, array $data): int
    {
        try {
            $date = $bln_bayar . '-01';
            // Validate date format and correctness
            Carbon::parse($date);
        } catch (\Exception $e) {
            return 0; // Return 0 updated rows if date is invalid
        }

        return Penugasan::where('mitra_id', $mitra_id)
            ->where('bln_bayar', $date)
            ->update($data);
    }

    public function bulkUpdateSpk(array $mitraIds, string $tgl_sk, string $bln_bayar): int
    {
        $date = Carbon::parse($tgl_sk)->firstOfMonth();

        // If the date falls on Saturday, Sunday, or a holiday, move it forward
        // If the date falls on Saturday, Sunday, or a holiday, move it forward
        while ($date->isWeekend() || Holiday::whereDate('tanggal', $date)->exists()) {
            $date->addDay();
        }

        $formattedTglSk    = $date->format('Y-m-d');
        $targetYear        = Carbon::parse($tgl_sk)->year;
        $maxNumber         = $this->getMaxNoSk($targetYear);
        $formattedBlnBayar = $this->formatMonth($bln_bayar);

        $updatedCount = 0;
        foreach ($mitraIds as $mitraId) {
            $maxNumber++;
            $newNoSk = str_pad($maxNumber, 4, '0', STR_PAD_LEFT);

            $updatedCount += Penugasan::where('mitra_id', $mitraId)
                ->where('bln_bayar', $formattedBlnBayar)
                ->whereNull('no_sk')
                ->update([
                    'no_sk'              => $newNoSk,
                    'tgl_sk'             => $formattedTglSk,
                    'jangka_waktu_mulai' => $formattedTglSk,
                ]);
        }

        return $updatedCount;
    }

    public function bulkUpdateBast(array $mitraIds, string $tgl_bast, string $bln_bayar): int
    {
        $date = Carbon::parse($tgl_bast)->lastOfMonth();

        // If the date falls on Saturday, Sunday, or a holiday, move it backward
        // If the date falls on Saturday, Sunday, or a holiday, move it backward
        while ($date->isWeekend() || Holiday::whereDate('tanggal', $date)->exists()) {
            $date->subDay();
        }

        $formattedTglBast  = $date->format('Y-m-d');
        $targetYear        = Carbon::parse($tgl_bast)->year;
        $maxNumber         = $this->getMaxNoBast($targetYear);
        $formattedBlnBayar = $this->formatMonth($bln_bayar);

        $updatedCount = 0;
        foreach ($mitraIds as $mitraId) {
            $maxNumber++;
            $newNoBast = str_pad($maxNumber, 4, '0', STR_PAD_LEFT);

            $updatedCount += Penugasan::where('mitra_id', $mitraId)
                ->where('bln_bayar', $formattedBlnBayar)
                ->whereNull('no_bast')
                ->update([
                    'no_bast'              => $newNoBast,
                    'tgl_bast'             => $formattedTglBast,
                    'jangka_waktu_selesai' => $formattedTglBast,
                ]);
        }

        return $updatedCount;
    }

    public function monitoringSpk(array $params): array
    {
        $type        = $params['type'];
        $perPage     = isset($params['per_page']) ? (int) $params['per_page'] : 10;
        $selectedbln = $params['selectedbln'] ?? '';

        $query = Penugasan::select('mitra_id', 'bln_bayar', 'no_sk', 'tgl_sk', 'no_bast', 'tgl_bast')
            ->selectRaw('SUM(nilai) as total')
            ->with('mitra')
            ->groupBy('mitra_id', 'bln_bayar', 'no_sk', 'tgl_sk', 'no_bast', 'tgl_bast')
            ->orderBy('bln_bayar', 'DESC');

        if ($selectedbln !== '') {
            $query->where('bln_bayar', $this->formatMonth($selectedbln));
        }

        switch ($type) {
            case 'tanpa_spk':
                $query->where(fn($q) => $q->whereNull('no_sk')->orWhereNull('no_bast'));
                break;
            case 'tanpa_bast':
                $query->whereNotNull('no_sk')->whereNull('no_bast');
                break;
            case 'diatas_4jt':
                $query->havingRaw('SUM(nilai) > ?', [4000000]);
                break;
        }

        $paginated = $query->fastPaginate($perPage);

        return [
            'data'            => $paginated,
            'listbln'         => $this->getListBln(),
            'pagination_info' => [
                'total_page'    => $paginated->lastPage(),
                'total_records' => $paginated->total(),
            ],
        ];
    }

    protected function getListBln(): \Illuminate\Support\Collection
    {
        return Penugasan::groupBy('bln_bayar')
            ->orderBy('bln_bayar', 'DESC')
            ->pluck('bln_bayar')
            ->map(fn($date) => Carbon::parse($date)->format('Y-m-01'));
    }

    protected function formatMonth(string $monthStr): string
    {
        return strlen($monthStr) == 7 ? $monthStr . '-01' : substr($monthStr, 0, 7) . '-01';
    }

    protected function getMaxNoSk(int $currentYear): int
    {
        $maxNumber = 0;
        Penugasan::whereNotNull('no_sk')
            ->whereYear('bln_bayar', $currentYear)
            ->pluck('no_sk')
            ->each(function ($noSk) use (&$maxNumber) {
                if (preg_match('/(\d+)$/', $noSk, $matches)) {
                    $maxNumber = max($maxNumber, (int) $matches[1]);
                }
            });
        return $maxNumber;
    }

    protected function getMaxNoBast(int $currentYear): int
    {
        $maxNumber = 0;
        Penugasan::whereNotNull('no_bast')
            ->whereYear('bln_bayar', $currentYear)
            ->pluck('no_bast')
            ->each(function ($noBast) use (&$maxNumber) {
                if (preg_match('/(\d+)$/', $noBast, $matches)) {
                    $maxNumber = max($maxNumber, (int) $matches[1]);
                }
            });
        return $maxNumber;
    }
}
