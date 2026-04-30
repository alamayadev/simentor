<?php
// Compare rows in database cek_scans vs CSV file using filename + fullpath as key
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

$csvPath = database_path('initial_data/olah_scan.csv');
if (!File::exists($csvPath)) {
    echo "CSV not found: {$csvPath}\n";
    exit(1);
}

$handle = fopen($csvPath, 'r');
if (! $handle) {
    echo "Failed to open CSV\n"; exit(1);
}

// Read header
$header = fgets($handle);
$lineNo = 1;
$csvKeys = [];
$skipped = 0;
while (!feof($handle)) {
    $line = fgets($handle);
    $lineNo++;
    if ($line === false) break;
    if (trim($line) === '') continue;
    $data = str_getcsv($line, ';', '"', '\\');
    if (count($data) < 9) { $skipped++; continue; }
    $filename = trim($data[2] ?? '');
    $fullpath = trim($data[3] ?? '');
    $key = $filename . '|' . $fullpath;
    $csvKeys[$key] = ['line' => $lineNo, 'filename' => $filename, 'fullpath' => $fullpath];
}
fclose($handle);

echo "CSV entries scanned: " . count($csvKeys) . " (skipped malformed lines: {$skipped})\n";

// Fetch DB keys
$dbRows = DB::table('cek_scans')->select('filename','fullpath')->get();
$dbKeys = [];
foreach ($dbRows as $r) {
    $k = trim($r->filename) . '|' . trim($r->fullpath);
    $dbKeys[$k] = true;
}

// Find missing
$missing = [];
foreach ($csvKeys as $k => $meta) {
    if (!isset($dbKeys[$k])) {
        $missing[$k] = $meta;
    }
}

echo "DB rows: " . count($dbRows) . "\n";
echo "CSV keys: " . count($csvKeys) . "\n";
echo "Missing in DB: " . count($missing) . "\n";
if (count($missing) > 0) {
    echo "First 100 missing entries (line|filename|fullpath):\n";
    $i = 0;
    foreach ($missing as $k => $m) {
        echo $m['line'] . " | " . $m['filename'] . " | " . $m['fullpath'] . "\n";
        if (++$i >= 100) break;
    }
}

// Also show DB entries not in CSV (sanity)
$extra = [];
foreach ($dbKeys as $k => $_) {
    if (!isset($csvKeys[$k])) $extra[] = $k;
}
echo "DB entries not found in CSV: " . count($extra) . "\n";
if (count($extra) > 0) {
    echo "Sample DB-only keys: \n";
    $i=0; foreach ($extra as $k) { echo $k . "\n"; if (++$i>=20) break; }
}
