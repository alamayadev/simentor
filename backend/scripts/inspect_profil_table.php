<?php
// boots Laravel and prints SQLite schema info for profil_pegawai
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

try {
    $info = DB::select("PRAGMA table_info('profil_pegawai')");
    echo "PRAGMA table_info('profil_pegawai'):\n";
    foreach ($info as $col) {
        echo json_encode($col) . "\n";
    }
    $create = DB::select("SELECT sql FROM sqlite_master WHERE type='table' AND name='profil_pegawai'");
    echo "\nCREATE TABLE SQL:\n";
    if (!empty($create) && isset($create[0]->sql)) {
        echo $create[0]->sql . "\n";
    } else {
        echo "(no create SQL found)\n";
    }
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
