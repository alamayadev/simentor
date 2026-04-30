<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Foundation\Bootstrap\BootProviders')->bootstrap($app);

echo "=== API Performance Test ===\n";
echo "Testing CekGeoref and CekScan API endpoints\n\n";

// Test function
function testEndpoint($url, $name) {
    $start = microtime(true);

    $response = file_get_contents("http://localhost:9000$url");

    $end = microtime(true);
    $duration = ($end - $start) * 1000; // Convert to milliseconds

    $data = json_decode($response, true);
    $recordCount = isset($data['data']['total']) ? $data['data']['total'] : 'Unknown';

    echo sprintf("%-20s: %8.2f ms (Records: %s)\n", $name, $duration, $recordCount);

    return $duration;
}

try {
    // Test both endpoints
    $cekGeorefTime = testEndpoint('/api/cek-georef', 'CekGeoref API');
    $cekScanTime = testEndpoint('/api/cek-scan', 'CekScan API');

    echo "\n=== Summary ===\n";
    echo sprintf("CekGeoref API: %.2f seconds (%.0fms)\n", $cekGeorefTime/1000, $cekGeorefTime);
    echo sprintf("CekScan API: %.2f seconds (%.0fms)\n", $cekScanTime/1000, $cekScanTime);

    // Compare with previous results
    echo "\n=== Comparison with Previous Results ===\n";
    echo "Previous CekGeoref: 7.28 seconds (7276ms)\n";
    echo "Previous CekScan: 3.78 seconds (3785ms)\n";

    $georefImprovement = ((7276 - $cekGeorefTime) / 7276) * 100;
    $scanImprovement = ((3785 - $cekScanTime) / 3785) * 100;

    echo sprintf("CekGeoref improvement: %.1f%%\n", $georefImprovement);
    echo sprintf("CekScan improvement: %.1f%%\n", $scanImprovement);

} catch (Exception $e) {
    echo "Error testing endpoints: " . $e->getMessage() . "\n";
    echo "Make sure the Laravel server is running on http://localhost:9000\n";
}
