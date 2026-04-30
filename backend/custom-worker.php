#!/usr/bin/env php
<?php




// custom-worker.php
// Custom RoadRunner worker for Laravel Octane

// Completely suppress all error output to prevent RoadRunner STDOUT protocol issues
// The Google API client and other dependencies output warnings that interfere with goridge
// error_reporting(0);
// ini_set('display_errors', '0');
// ini_set('display_startup_errors', '0');

ini_set('display_errors', 'stderr');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
ini_set('log_errors', '1');
ini_set('error_log', __DIR__.'/storage/logs/worker_debug.log');
error_log("Worker script initialized at " . date('Y-m-d H:i:s'), 3, __DIR__.'/storage/logs/worker_debug.log');


// Capture ALL output to prevent any interference with RoadRunner's protocol
// This includes warnings, deprecations, and other output
// ob_start(function($buffer) {
//     return ''; // Discard ALL output completely
// }, PHP_OUTPUT_HANDLER_CLEANABLE | PHP_OUTPUT_HANDLER_FLUSHABLE | PHP_OUTPUT_HANDLER_REMOVABLE);

// Set error handler to capture any remaining errors
// set_error_handler(function($severity, $message, $file, $line) {
//     return true; // Suppress all errors
// });

// Set exception handler to prevent uncaught exceptions from breaking output
// set_exception_handler(function($exception) {
//     return true; // Suppress all exceptions
// });

// Also suppress all error types that might be output during Laravel boot
// error_reporting(0);

use Laminas\Diactoros\ServerRequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UploadedFileFactory;
use Laravel\Octane\ApplicationFactory;
use Laravel\Octane\RequestContext;
use Laravel\Octane\RoadRunner\RoadRunnerClient;
use Laravel\Octane\Stream;
use Laravel\Octane\Worker;
use Psr\Http\Message\ServerRequestInterface;
use Spiral\Goridge\Exception\RelayException;
use Spiral\Goridge\Relay;
use Spiral\RoadRunner\Http\PSR7Worker;
use Spiral\RoadRunner\Worker as RoadRunnerWorker;

// Set the base path
$basePath = __DIR__;

// Register the autoloader
$autoload_file = "{$basePath}/vendor/autoload.php";
if (! is_file($autoload_file)) {
    fwrite(STDERR, "Composer autoload file was not found. Did you install the project's dependencies?\n");
    exit(10);
}
require_once $autoload_file;

// Apply Octane fixes
require __DIR__.'/vendor/laravel/octane/fixes/fix-symfony-dd.php';

// Set environment variable
$_ENV['APP_RUNNING_IN_CONSOLE'] = false;

/*
|--------------------------------------------------------------------------
| Start The Octane Worker
|--------------------------------------------------------------------------
|
| Next we will start the Octane worker, which is a long running process to
| handle incoming requests to the application. Octane can intercept the
| incoming requests and proxy them to the Laravel application for us.
|
*/

$roadRunnerClient = new RoadRunnerClient($psr7Client = new PSR7Worker(
    new RoadRunnerWorker(Relay::create($_ENV['LARAVEL_OCTANE_ROADRUNNER_RELAY'] ?? 'pipes')),
    new ServerRequestFactory,
    new StreamFactory,
    new UploadedFileFactory,
));

$worker = null;

try {
    while ($psr7Request = $psr7Client->waitRequest()) {
        $worker = $worker ?: tap((new Worker(
            new ApplicationFactory($basePath), $roadRunnerClient
        )))->boot();

        if (! $psr7Request instanceof ServerRequestInterface) {
            break;
        }

        [$request, $context] = $roadRunnerClient->marshalRequest(new RequestContext([
            'psr7Request' => $psr7Request,
        ]));

        $worker->handle($request, $context);
    }
} catch (Throwable $e) {
    file_put_contents(__DIR__.'/storage/logs/last_crash.txt', "Exception: " . $e->getMessage() . "\nStack Trace:\n" . $e->getTraceAsString());
    if (! $e instanceof RelayException) {
        $worker ? report($e) : Stream::shutdown($e);
    }

    exit(1);
} finally {
    if (! is_null($worker)) {
        $worker->terminate();
    }
}
