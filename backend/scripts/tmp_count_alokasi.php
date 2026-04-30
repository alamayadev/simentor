<?php
$start = microtime(true);
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');
$count = 0;
try {
    $count = $db->table('alokasis')->count();
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(2);
}
$end = microtime(true);
$peak = memory_get_peak_usage(true);
echo "ALOKASIS_COUNT:" . $count . PHP_EOL;
echo "RUNTIME_SECONDS:" . number_format($end - $start, 4) . PHP_EOL;
echo "PEAK_MEMORY_BYTES:" . $peak . PHP_EOL;
