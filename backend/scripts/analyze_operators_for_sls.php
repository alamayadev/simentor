<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use App\Models\Alokasi;

echo "Analyzing operators for alokasis whose idsls exist in sls_sipw\n";

$slsIds = DB::table('sls_sipw')->distinct()->pluck('idsls')->filter()->unique()->values()->all();
echo "Total unique idsls in sls_sipw: " . count($slsIds) . "\n";

// Use all idsls (no slice) to mirror controller behavior after removing the limit
$slice = $slsIds;
echo "Using all " . count($slice) . " idsls (no slice)\n";

$rows = DB::table('alokasis')->whereIn('idsls', $slice)->whereNotNull('alokasi_geojson')->select('idsls','alokasi_geojson')->get();
echo "Found alokasi rows matching slice: " . $rows->count() . "\n";

$operators = collect();
foreach ($rows as $r) {
    $str = trim($r->alokasi_geojson);
    if ($str === '') continue;
    $data = @json_decode($str, true);
    if (! is_array($data)) {
        $operators->push($str);
        continue;
    }
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
            } else { $value = null; break; }
        }
        if ($value !== null && $value !== '') { $found = $value; break; }
    }
    if ($found) $operators->push($found);
}

$unique = $operators->unique()->values()->sortBy(function($v){ return mb_strtolower($v); })->values();
echo "Unique operators for slice: " . $unique->count() . "\n";
foreach ($unique as $u) { echo "- $u\n"; }

// Also show which operators exist in alokasis but not in slice
$allOperators = DB::table('alokasis')->whereNotNull('alokasi_geojson')->get()->map(function($r){ return trim($r->alokasi_geojson); })->unique()->values();
$missing = $allOperators->diff($unique);
echo "\nOperators present in alokasis but not in slice (raw comparison): " . $missing->count() . "\n";
foreach ($missing as $m) { echo "- $m\n"; }

echo "\nDone.\n";
