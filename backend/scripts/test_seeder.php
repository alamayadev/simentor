<?php

require 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Foundation\Bootstrap\BootProviders')->bootstrap($app);

use App\Models\CekGeoref;

// Check current count
$beforeCount = CekGeoref::count();
echo "Records before seeding: $beforeCount" . PHP_EOL;

// Run the seeder
$seeder = new Database\Seeders\CekGeorefSeeder();
$seeder->run();

// Check count after
$afterCount = CekGeoref::count();
echo "Records after seeding: $afterCount" . PHP_EOL;
echo "Records added: " . ($afterCount - $beforeCount) . PHP_EOL;

// Show a sample record
$sample = CekGeoref::first();
if ($sample) {
    echo "Sample record:" . PHP_EOL;
    echo "  kec: {$sample->kec}" . PHP_EOL;
    echo "  desa: {$sample->desa}" . PHP_EOL;
    echo "  filename: {$sample->filename}" . PHP_EOL;
    echo "  created_time: {$sample->created_time}" . PHP_EOL;
    echo "  jenis: {$sample->jenis}" . PHP_EOL;
    echo "  kode: {$sample->kode}" . PHP_EOL;
}