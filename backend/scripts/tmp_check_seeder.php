<?php
require __DIR__ . '/vendor/autoload.php';
$r = new ReflectionClass('Database\\Seeders\\CekScanSeeder');
echo $r->getFileName() . PHP_EOL;
