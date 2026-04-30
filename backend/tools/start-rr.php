<?php
// start-rr.php
// Simple script to start RoadRunner directly

echo "Starting RoadRunner server...\n";
echo "(This script expects a local rr.exe and a .rr.yaml in the project root.)\n\n";

// Change to the project directory
chdir(__DIR__);

// Paths
$rrPath = __DIR__ . DIRECTORY_SEPARATOR . 'rr.exe';
$configPath = __DIR__ . DIRECTORY_SEPARATOR . '.rr.yaml';
$logPath = __DIR__ . DIRECTORY_SEPARATOR . 'rr.log';

// Basic validation
if (!file_exists($rrPath)) {
	echo "Error: RoadRunner binary not found at: {$rrPath}\n";
	echo "Hint: run `php artisan octane:install --server=roadrunner` or download rr.exe and place it in the project root.\n";
	exit(1);
}

if (!file_exists($configPath)) {
	echo "Error: RoadRunner config .rr.yaml not found at: {$configPath}\n";
	echo "Hint: create a .rr.yaml (see CHANGES.md or Octane docs) in the project root.\n";
	exit(1);
}

// Build safely quoted command (works on Windows)
$rr = escapeshellarg($rrPath);
$cfg = escapeshellarg($configPath);
$cmd = "{$rr} serve -c {$cfg} 2>&1";

echo "Executing: {$cmd}\n";
echo "Logging output to: {$logPath}\n\n";

// Stream output to both console and log file so NSSM and logs show useful info
$fp = fopen($logPath, 'a');
if ($fp === false) {
	echo "Warning: could not open log file {$logPath} for writing. Output will be shown in console only.\n";
	passthru($cmd, $status);
	echo "RoadRunner exited with status: {$status}\n";
	exit($status);
}

// Use proc_open so we can capture output and mirror to log file
$descriptors = [
	1 => ['pipe', 'w'],
	2 => ['pipe', 'w'],
];

$process = proc_open($cmd, $descriptors, $pipes, __DIR__);
if (!is_resource($process)) {
	fwrite($fp, "Failed to start RoadRunner process\n");
	fclose($fp);
	echo "Failed to start RoadRunner process. Check permissions and paths.\n";
	exit(1);
}

// Read stdout/stderr and mirror to log and stdout
stream_set_blocking($pipes[1], false);
stream_set_blocking($pipes[2], false);
while (true) {
	$out = '';
	$err = '';
	$out .= stream_get_contents($pipes[1]);
	$err .= stream_get_contents($pipes[2]);
	if ($out !== '') {
		echo $out;
		fwrite($fp, $out);
	}
	if ($err !== '') {
		echo $err;
		fwrite($fp, $err);
	}

	$status = proc_get_status($process);
	if (!$status['running']) {
		break;
	}

	// small sleep to avoid busy loop
	usleep(100000);
}

// drain remaining pipes
echo stream_get_contents($pipes[1]);
echo stream_get_contents($pipes[2]);
fwrite($fp, stream_get_contents($pipes[1]));
fwrite($fp, stream_get_contents($pipes[2]));

fclose($pipes[1]);
fclose($pipes[2]);
$exitCode = proc_close($process);
fwrite($fp, "RoadRunner exited with code: {$exitCode}\n");
fclose($fp);

echo "RoadRunner exited with code: {$exitCode}\n";
exit($exitCode);
