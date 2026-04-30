<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CompareIdsls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'compare:idsls {--file=database/json_data/idsls.csv} {--reverse : Show ids in CSV that are not in DB} {--both : Perform both comparisons and produce a side-by-side CSV with counts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Compare idsls in alokasis table with idsls CSV and print ids present in DB but missing in CSV';

    public function handle()
    {
        $file = base_path($this->option('file'));
        if (!file_exists($file)) {
            $this->error("CSV file not found: $file");
            return 1;
        }

        // Read CSV first column into a set
        $csvIds = [];
        if (($handle = fopen($file, 'r')) !== false) {
            while (($data = fgetcsv($handle)) !== false) {
                if (count($data) === 0) continue;
                $val = trim($data[0]);
                if ($val === '') continue;
                $csvIds[$val] = true;
            }
            fclose($handle);
        }

        $this->info('Loaded ' . count($csvIds) . ' ids from CSV');

        // Query distinct idsls from alokasis
        $dbIds = DB::table('alokasis')->distinct()->pluck('idsls')->filter()->map(function ($v) { return trim((string)$v); })->unique()->values()->all();
        $this->info('Loaded ' . count($dbIds) . ' distinct ids from alokasis');

    $reverse = $this->option('reverse');
    $both = $this->option('both');

    // normalize dbIds into a set for faster lookups
    $dbSet = array_fill_keys($dbIds, true);

    if ($reverse) {
            // CSV entries not present in DB
            $csvOnly = [];
            foreach (array_keys($csvIds) as $id) {
                if (!in_array($id, $dbIds, true)) {
                    $csvOnly[] = $id;
                }
            }

            if (empty($csvOnly)) {
                $this->info('No ids found in CSV that are missing from alokasis');
                return 0;
            }

            $this->info('IDs present in CSV but missing from alokasis:');
            foreach ($csvOnly as $m) {
                $this->line($m);
            }

            $outPath = base_path('tools/benchmark/compare_idsls_csv_only.txt');
            @mkdir(dirname($outPath), 0755, true);
            file_put_contents($outPath, implode("\n", $csvOnly));
            $this->info("Wrote results to $outPath");
            return 0;
        }
        // DB entries not present in CSV
        $missing = [];
        foreach ($dbIds as $id) {
            if (!isset($csvIds[$id])) {
                $missing[] = $id;
            }
        }

        // If --both requested, also compute CSV-only and write side-by-side CSV
        if ($both) {
            $csvOnly = [];
            foreach (array_keys($csvIds) as $id) {
                if (!isset($dbSet[$id])) {
                    $csvOnly[] = $id;
                }
            }

            $this->info("Counts: CSV total=" . count($csvIds) . ", DB total=" . count($dbIds) . ", CSV-only=" . count($csvOnly) . ", DB-only=" . count($missing));

            // write the two lists to separate files as well as a side-by-side CSV
            $outCsvOnly = base_path('tools/benchmark/compare_idsls_csv_only.txt');
            @mkdir(dirname($outCsvOnly), 0755, true);
            file_put_contents($outCsvOnly, implode("\n", $csvOnly));

            $outDbOnly = base_path('tools/benchmark/compare_idsls_result.txt');
            file_put_contents($outDbOnly, implode("\n", $missing));

            $sideBySidePath = base_path('tools/benchmark/compare_idsls_side_by_side.csv');
            $fh = fopen($sideBySidePath, 'w');
            // header
            fputcsv($fh, ['csv_idsls_missing_in_db', 'db_idsls_missing_in_csv']);

            $max = max(count($csvOnly), count($missing));
            for ($i = 0; $i < $max; $i++) {
                $left = $csvOnly[$i] ?? '';
                $right = $missing[$i] ?? '';
                fputcsv($fh, [$left, $right]);
            }
            fclose($fh);

            $this->info("Wrote CSV-only to $outCsvOnly");
            $this->info("Wrote DB-only to $outDbOnly");
            $this->info("Wrote side-by-side CSV to $sideBySidePath");
            return 0;
        }

        if (empty($missing)) {
            $this->info('No ids found in alokasis that are missing from CSV');
            return 0;
        }

        $this->info('IDs present in alokasis but missing from CSV:');
        foreach ($missing as $m) {
            $this->line($m);
        }

        // Also write to tools/benchmark/compare_idsls_result.txt for convenience
        $outPath = base_path('tools/benchmark/compare_idsls_result.txt');
        @mkdir(dirname($outPath), 0755, true);
        file_put_contents($outPath, implode("\n", $missing));
        $this->info("Wrote results to $outPath");

        return 0;
    }
}
