<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DedupeAlokasis extends Command
{
    protected $signature = 'dedupe:alokasis {--out=tools/benchmark/alokasis_duplicates_backup.csv} {--force : Actually delete duplicates} {--keep=oldest : keep oldest|newest}';
    protected $description = 'Backup and remove duplicate rows in alokasis based on idsls, keeping one per idsls.';

    public function handle()
    {
        $out = base_path($this->option('out'));
        $keep = $this->option('keep') === 'newest' ? 'newest' : 'oldest';

        // Find duplicate idsls
        $duplicates = DB::table('alokasis')
            ->select('idsls', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('idsls')
            ->groupBy('idsls')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('idsls')
            ->all();

        if (empty($duplicates)) {
            $this->info('No duplicates found');
            return 0;
        }

        $this->info('Found ' . count($duplicates) . " duplicated idsls. Preparing backup and deletion plan.");

        @mkdir(dirname($out), 0755, true);
        $fh = fopen($out, 'w');

        // write header by selecting one sample row's columns
        $sample = DB::table('alokasis')->first();
        if ($sample) {
            fputcsv($fh, array_keys((array)$sample));
        }

        $totalToDelete = 0;

        foreach ($duplicates as $idsls) {
            // fetch rows for this idsls
            $q = DB::table('alokasis')->where('idsls', $idsls)->orderBy('id', $keep === 'oldest' ? 'asc' : 'desc');
            $rows = $q->get();
            if ($rows->count() <= 1) continue;

            // keep first row, mark others for deletion
            $toKeep = $rows->first();
            $toDelete = $rows->slice(1);

            foreach ($toDelete as $r) {
                fputcsv($fh, array_values((array)$r));
            }

            $totalToDelete += $toDelete->count();

            if ($this->option('force')) {
                $ids = $toDelete->pluck('id')->all();
                DB::table('alokasis')->whereIn('id', $ids)->delete();
            }
        }

        fclose($fh);

        $this->info("Backed up duplicates to $out");
        $this->info("Total rows that would be deleted: $totalToDelete");

        if (!$this->option('force')) {
            $this->info('Dry-run mode. To perform deletion re-run with --force');
        } else {
            $this->info('Duplicates deleted');
        }

        return 0;
    }
}
