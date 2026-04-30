<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use App\Models\SlsSipw;

class ComputeSlsSipwStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sls-sipw:compute-statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compute and cache SLS SIPW statistics (counts of unique idsls and progress by compare)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Computing SLS SIPW statistics...');

        $stats = [
            'target' => SlsSipw::whereNotNull('idsls')->distinct()->count('idsls'),
            'progres_ok' => SlsSipw::where('compare', 'OK')->whereNotNull('idsls')->distinct()->count('idsls'),
            'progres_tidak_match' => SlsSipw::where('compare', 'TIDAK_MATCH')->whereNotNull('idsls')->distinct()->count('idsls'),
            'progres_belum_olah' => SlsSipw::where('compare', 'BELUM_OLAH')->whereNotNull('idsls')->distinct()->count('idsls'),
            'computed_at' => now()->toDateTimeString(),
        ];

        // cache for slightly longer than the update cadence (7 hours)
        Cache::put('sls_sipw:statistics', $stats, now()->addHours(7));

        $this->info('Cached sls_sipw:statistics');

        return 0;
    }
}
