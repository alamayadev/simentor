<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ExportAlokasisJson extends Command
{
    protected $signature = 'export:alokasis-json {--out=tools/benchmark/alokasis_full.json}';
    protected $description = 'Export all rows from alokasis table to a JSON file (streamed)';

    public function handle()
    {
        $out = base_path($this->option('out'));
        @mkdir(dirname($out), 0755, true);

        $fh = fopen($out, 'w');
        if ($fh === false) {
            $this->error('Failed to open output file: ' . $out);
            return 1;
        }

        fwrite($fh, "[");
        $first = true;
        $count = 0;

        // use cursor to avoid loading all into memory
        foreach (DB::table('alokasis')->cursor() as $row) {
            $arr = (array)$row;
            // cast any binary/objects to strings where needed
            $json = json_encode($arr, JSON_UNESCAPED_UNICODE);
            if ($json === false) {
                $json = json_encode(array_map('strval', $arr), JSON_UNESCAPED_UNICODE);
            }
            if (!$first) fwrite($fh, ",\n");
            fwrite($fh, $json);
            $first = false;
            $count++;
        }

        fwrite($fh, "]");
        fclose($fh);

        $this->info("Exported $count rows to $out");
        return 0;
    }
}
