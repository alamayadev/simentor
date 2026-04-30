<?php
$start = microtime(true);
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$db = $app->make('db');

$file = database_path('initial_data/alokasi.txt');
if (!file_exists($file)) {
    echo "File not found: $file\n";
    exit(2);
}

$fh = fopen($file, 'r');
if ($fh === false) {
    echo "Could not open $file\n";
    exit(2);
}

// read header
$header = fgets($fh);

$unique = [];
$line = 0;
while (!feof($fh)) {
    $l = fgets($fh);
    $line++;
    if ($l === false) continue;
    $l = trim($l);
    if ($l === '') continue;
    $parts = str_getcsv($l, ';', '"', '\\');
    if (isset($parts[1])) {
        $idsls = trim($parts[1], "\" \t\n");
        if ($idsls !== '') $unique[$idsls] = ($unique[$idsls] ?? 0) + 1;
    }
}
fclose($fh);

$uniqueCount = count($unique);

// query DB distinct idsls
try {
    $dbDistinct = $db->table('alokasis')->distinct()->pluck('idsls')->all();
} catch (Exception $e) {
    echo "DB error: " . $e->getMessage() . "\n";
    exit(3);
}

$dbSet = array_flip($dbDistinct);

$inFileNotDb = array_diff_key($unique, $dbSet);
$inDbNotFile = array_diff_key($dbSet, $unique);

echo "Unique idsls in file: $uniqueCount\n";
echo "Distinct idsls in DB: " . count($dbDistinct) . "\n";
echo "Idsls in file but missing in DB: " . count($inFileNotDb) . "\n";
echo "Idsls in DB but not in file: " . count($inDbNotFile) . "\n";

if (count($inFileNotDb) > 0) {
    echo "Sample idsls missing in DB (first 10):\n";
    echo implode("\n", array_slice(array_keys($inFileNotDb), 0, 10)) . "\n";
}

if (count($inDbNotFile) > 0) {
    echo "Sample idsls in DB but not in file (first 10):\n";
    echo implode("\n", array_slice(array_keys($inDbNotFile), 0, 10)) . "\n";
}

$end = microtime(true);
echo "RUNTIME_SECONDS:" . number_format($end - $start, 4) . "\n";

exit(0);
