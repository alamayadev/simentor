<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Alokasi;

echo "Analyzing alokasi_geojson values and extracted operators\n";

$total = Alokasi::whereNotNull('alokasi_geojson')->count();
echo "Total rows with alokasi_geojson: $total\n";

$distinctRaw = DB::table('alokasis')->whereNotNull('alokasi_geojson')->distinct()->count('alokasi_geojson');
echo "Distinct raw alokasi_geojson values: $distinctRaw\n";

$rows = DB::table('alokasis')->whereNotNull('alokasi_geojson')->select('alokasi_geojson')->get();

$operators = collect();
$plainCount = 0;
$jsonCount = 0;

foreach ($rows as $r) {
    $str = trim($r->alokasi_geojson);
    if ($str === '') continue;
    $data = @json_decode($str, true);
    if (! is_array($data)) {
        $plainCount++;
        $operators->push($str);
        continue;
    }
    $jsonCount++;
    $candidates = [
        ['operator'], ['name'], ['nama'], ['properties','operator'], ['properties','name'], ['properties','nama'],
        ['features',0,'properties','operator'], ['features',0,'properties','name'], ['features',0,'properties','nama']
    ];
    $found = null;
    foreach ($candidates as $path) {
        $value = $data;
        foreach ($path as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                $value = null;
                break;
            }
        }
        if ($value !== null && $value !== '') {
            $found = $value;
            break;
        }
    }
    if ($found) {
        $operators->push($found);
    }
}

$uniqueOperators = $operators->unique()->values()->sortBy(function($v){ return mb_strtolower($v); })->values();

echo "Rows parsed as JSON: $jsonCount\n";
echo "Rows parsed as plain text: $plainCount\n";
echo "Unique extracted operator-like values: " . $uniqueOperators->count() . "\n";
echo "List of unique operators (sorted):\n";
foreach ($uniqueOperators as $op) {
    echo "- $op\n";
}

// Additional: list any distinct raw json values that didn't yield an operator
$noOperatorSamples = [];
foreach (DB::table('alokasis')->whereNotNull('alokasi_geojson')->select('alokasi_geojson')->get() as $r) {
    $str = trim($r->alokasi_geojson);
    $data = @json_decode($str, true);
    if (! is_array($data)) continue;
    $found = false;
    $candidates = [
        ['operator'], ['name'], ['nama'], ['properties','operator'], ['properties','name'], ['properties','nama'],
        ['features',0,'properties','operator'], ['features',0,'properties','name'], ['features',0,'properties','nama']
    ];
    foreach ($candidates as $path) {
        $value = $data;
        foreach ($path as $key) {
            if (is_array($value) && array_key_exists($key, $value)) {
                $value = $value[$key];
            } else {
                $value = null; break;
            }
        }
        if ($value !== null && $value !== '') { $found = true; break; }
    }
    if (! $found) {
        $noOperatorSamples[] = $str;
        if (count($noOperatorSamples) >= 5) break;
    }
}

echo "\nSample JSON entries that did not yield an operator (up to 5):\n";
foreach ($noOperatorSamples as $s) {
    echo "---\n" . $s . "\n";
}

echo "\nDone.\n";
