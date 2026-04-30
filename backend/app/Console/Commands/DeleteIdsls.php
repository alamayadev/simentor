<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DeleteIdsls extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'delete:idsls {--file=tools/benchmark/DB-only.csv} {--force : Actually perform deletion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete rows from alokasis where idsls are listed in a CSV file. Dry-run by default.';

    public function handle()
    {
        $file = base_path($this->option('file'));
        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $ids = [];
        if (($fh = fopen($file, 'r')) === false) {
            $this->error("Failed to open $file");
            return 1;
        }

        // Read CSV first column, skip header if present
        $first = true;
        while (($row = fgetcsv($fh)) !== false) {
            if ($first) {
                $first = false;
                // if the header equals 'idsls' skip it
                if (isset($row[0]) && strtolower(trim($row[0])) === 'idsls') {
                    continue;
                }
                // otherwise fallthrough and treat as data
            }
            if (!isset($row[0])) continue;
            $val = trim($row[0]);
            if ($val === '') continue;
            $ids[] = $val;
        }
        fclose($fh);

        if (empty($ids)) {
            $this->info('No ids to delete');
            return 0;
        }

        // Count matching rows first
        $count = DB::table('alokasis')->whereIn('idsls', $ids)->count();
        $this->info("Found $count rows in alokasis matching the provided ids list ({count distinct ids: " . count($ids) . "}).");

        if (!$this->option('force')) {
            $this->info('Dry-run mode. To perform deletion, re-run with --force');
            // print sample of ids that match
            $sample = DB::table('alokasis')->whereIn('idsls', $ids)->limit(50)->pluck('idsls')->all();
            if (!empty($sample)) {
                $this->info('Sample ids that would be deleted:');
                foreach ($sample as $s) $this->line($s);
            }
            return 0;
        }

        // Perform deletion (inside transaction)
        DB::beginTransaction();
        try {
            $deleted = DB::table('alokasis')->whereIn('idsls', $ids)->delete();
            DB::commit();
            $this->info("Deleted $deleted rows from alokasis");
            return 0;
        } catch (\Exception $e) {
            DB::rollBack();
            $this->error('Deletion failed: ' . $e->getMessage());
            return 1;
        }
    }
}
