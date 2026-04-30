<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FindDuplicateIdsls extends Command
{
    protected $signature = 'find:duplicate-idsls {--out=tools/benchmark/duplicate_idsls.csv}';
    protected $description = 'Find duplicate idsls in alokasis and write a CSV of idsls and counts.';

    public function handle()
    {
        $out = base_path($this->option('out'));
        $rows = DB::table('alokasis')
            ->select('idsls', DB::raw('COUNT(*) as cnt'))
            ->whereNotNull('idsls')
            ->groupBy('idsls')
            ->havingRaw('COUNT(*) > 1')
            ->orderByDesc('cnt')
            ->get();

        $totalDuplicates = $rows->count();
        $extraRows = $rows->sum('cnt') - $totalDuplicates; // total extra rows beyond first unique

        $this->info("Found $totalDuplicates duplicate idsls (total extra rows: $extraRows)");

        @mkdir(dirname($out), 0755, true);
        $fh = fopen($out, 'w');
        fputcsv($fh, ['idsls', 'count']);
        foreach ($rows as $r) {
            fputcsv($fh, [$r->idsls, $r->cnt]);
        }
        fclose($fh);

        $this->info("Wrote duplicates to $out");
        return 0;
    }
}
