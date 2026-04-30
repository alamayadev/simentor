<?php

namespace App\Services;

use App\Models\Alokasi;
use App\Models\CekScan;
use App\Models\CekGeoref;
use App\Models\SlsSipw;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Service that computes the wilkerstat dashboard payload.
 *
 * The compute() method accepts optional overrides to facilitate unit testing
 * (so tests don't need a DB). When overrides are provided they will be used
 * instead of running the real queries.
 */
class WilkerstatDashboardService
{
    /**
     * Compute dashboard metrics.
     *
     * @param array $overrides Optional map of values to use instead of querying the DB.
     * @return array
     */
    public function compute(array $overrides = []): array
    {
        // If overrides provided, use them directly for predictable unit tests
        if (!empty($overrides)) {
            return [
                'total_operator' => (int) ($overrides['total_operator'] ?? 0),
                'target' => (int) ($overrides['target'] ?? 0),
                'progres_scan' => (int) ($overrides['progres_scan'] ?? 0),
                'progres_georef' => (int) ($overrides['progres_georef'] ?? 0),
                'scan_not_done' => (int) ($overrides['scan_not_done'] ?? 0),
                'georef_not_done' => (int) ($overrides['georef_not_done'] ?? 0),
                'georef_last_update' => $overrides['georef_last_update'] ?? null,
                'scan_last_update' => $overrides['scan_last_update'] ?? null,
                'peta_target' => (int) ($overrides['peta_target'] ?? 0),
                'peta_not_done' => (int) ($overrides['peta_not_done'] ?? 0),
                'peta_done' => (int) ($overrides['peta_done'] ?? 0),
            ];
        }

        // Real computation using Eloquent/DB
        $totalOperator = Alokasi::query()->whereNotNull('alokasi_muatan')->distinct('alokasi_muatan')->count('alokasi_muatan');

        $target = Alokasi::query()->distinct('idsls')->count('idsls');

        $scanNotDone = Alokasi::query()
            ->whereDoesntHave('cekScan')
            ->distinct('idsls')
            ->count('idsls');

        $progresScan = max(0, (int) $target - (int) $scanNotDone);

        $georefNotDone = Alokasi::query()
            ->whereDoesntHave('cekGeoref')
            ->distinct('idsls')
            ->count('idsls');

        $progresGeoref = max(0, (int) $target - (int) $georefNotDone);

        $georefLastUpdateRaw = DB::table('cek_georefs')
            ->whereNotNull('created_time')
            ->orderByDesc('created_time')
            ->value('created_time');
        $scanLastUpdateRaw = DB::table('cek_scans')
            ->whereNotNull('created_time')
            ->orderByDesc('created_time')
            ->value('created_time');

        // Peta-related metrics
        $petaTarget = SlsSipw::query()->distinct('idsls')->count('idsls');

        $petaNotDone = SlsSipw::query()
            ->where('status_olah_peta', 'BELUM')
            ->distinct('idsls')
            ->count('idsls');

        $petaDone = SlsSipw::query()
            ->where('status_olah_peta', 'SUDAH')
            ->distinct('idsls')
            ->count('idsls');

        return [
            'total_operator' => (int) $totalOperator,
            'target' => (int) $target,
            'progres_scan' => (int) $progresScan,
            'progres_georef' => (int) $progresGeoref,
            'scan_not_done' => (int) $scanNotDone,
            'georef_not_done' => (int) $georefNotDone,
            'georef_last_update' => $this->normalizeTimestamp($georefLastUpdateRaw),
            'scan_last_update' => $this->normalizeTimestamp($scanLastUpdateRaw),
            'peta_target' => (int) $petaTarget,
            'peta_not_done' => (int) $petaNotDone,
            'peta_done' => (int) $petaDone,
        ];
    }

    private function normalizeTimestamp($value): ?string
    {
        if ($value instanceof Carbon) {
            return $value->toDateTimeString();
        }

        if ($value instanceof \DateTimeInterface) {
            return Carbon::instance($value)->toDateTimeString();
        }

        if (is_numeric($value)) {
            try {
                return Carbon::createFromTimestamp((int) $value)->toDateTimeString();
            } catch (\Throwable $e) {
                return null;
            }
        }

        if (is_string($value)) {
            try {
                return Carbon::parse($value)->toDateTimeString();
            } catch (\Throwable $e) {
                $timestamp = strtotime($value);
                if ($timestamp !== false) {
                    return Carbon::createFromTimestamp($timestamp)->toDateTimeString();
                }
            }
        }

        return null;
    }
}
