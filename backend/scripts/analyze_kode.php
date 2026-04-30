<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\CekScan;
use App\Models\CekGeoref;

function analyze($modelName) {
    $model = $modelName;
    $q = $model::where('jenis', 'WS')->whereNotNull('kode');
    $total = $q->count();
    $raw = $q->pluck('kode')->map(fn($k)=> (string)$k)->values();
    $distinct_raw = $raw->unique()->count();
    $trimmed = $raw->map(fn($k)=> trim((string)$k))->filter()->values();
    $distinct_trim = $trimmed->unique()->count();
    $digits = $raw->map(fn($k)=> preg_replace('/\\D/', '', (string)$k))->filter()->values();
    $distinct_digits = $digits->unique()->count();

    // samples where trim changes the value
    $samples_trim_diff = $raw->unique()->filter(fn($v)=> trim((string)$v) !== (string)$v)->values()->take(20)->all();
    // samples where digits-only is empty
    $samples_digits_empty = $digits->filter(fn($v)=> $v === '')->unique()->values()->take(20)->all();
    // samples in trimmed not in digits
    $trim_minus_digits = collect($trimmed->unique()->values())->diff($digits->unique()->values())->values()->take(20)->all();
    $digits_minus_trim = collect($digits->unique()->values())->diff($trimmed->unique()->values())->values()->take(20)->all();

    return [
        'model' => $modelName,
        'total_rows' => $total,
        'distinct_raw' => $distinct_raw,
        'distinct_trim' => $distinct_trim,
        'distinct_digits' => $distinct_digits,
        'samples_trim_diff' => $samples_trim_diff,
        'samples_digits_empty' => $samples_digits_empty,
        'trim_minus_digits_sample' => $trim_minus_digits,
        'digits_minus_trim_sample' => $digits_minus_trim,
    ];
}

$result = [
    'CekScan' => analyze(CekScan::class),
    'CekGeoref' => analyze(CekGeoref::class),
];

echo json_encode($result, JSON_PRETTY_PRINT);
