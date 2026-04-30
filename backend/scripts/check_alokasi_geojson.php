<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Alokasi;

$count = Alokasi::whereNotNull('alokasi_geojson')->count();
echo "COUNT: $count\n";
$samples = Alokasi::whereNotNull('alokasi_geojson')->limit(5)->pluck('alokasi_geojson')->toArray();
echo "SAMPLES:\n";
foreach ($samples as $s) {
    echo $s . "\n---\n";
}
