<?php
// start-octane-simple.php
// Simple PHP script to start Laravel Octane without signal handling issues

// Change to the project directory
chdir(__DIR__);

// Set environment variables
putenv('APP_ENV=production');
putenv('APP_DEBUG=false');

echo "Starting Laravel Octane with RoadRunner...\n";
echo "Access your application at http://0.0.0.0:9000\n";
echo "Press CTRL+C to stop the server\n\n";

// Execute Octane with minimal configuration and suppress signal handling errors
$command = 'php artisan octane:start --host=0.0.0.0 --port=9000 --workers=1 --max-requests=500 --server=roadrunner 2>&1';
$handle = popen($command, 'r');

if ($handle) {
    while (!feof($handle)) {
        $line = fgets($handle);
        // Filter out the SIGINT error message
        if (strpos($line, 'Undefined constant "Laravel\Octane\Commands\Concerns\SIGINT"') === false) {
            echo $line;
        }
    }
    pclose($handle);
}

echo "Octane server stopped.\n";
