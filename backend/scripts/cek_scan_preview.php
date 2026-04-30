<?php
require __DIR__ . '/../vendor/autoload.php';
use Illuminate\Support\Facades\File;

$filePath = __DIR__ . '/../database/json_data/cek_scan.txt';
$content = file_get_contents($filePath);
$lines = explode("\n", $content);
array_shift($lines);

foreach (array_slice($lines, 0, 70) as $i => $line) {
    if (trim($line) === '') continue;
    $data = str_getcsv($line, ';', '"', '\\');
    if (count($data) < 13) {
        echo "Line " . ($i+2) . " -> insufficient fields\n";
        continue;
    }
    $operatorRaw = isset($data[12]) ? trim($data[12], '"') : null;
    $operator = ($operatorRaw === '') ? null : $operatorRaw;
    echo ($i+2) . ": operatorRaw='" . $operatorRaw . "' => operator=" . var_export($operator, true) . "\n";
}
