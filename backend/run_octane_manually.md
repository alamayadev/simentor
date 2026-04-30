# How to Manually Start Octane with RoadRunner

This guide explains how to manually start Laravel Octane using RoadRunner directly, which is the most reliable method on Windows systems.

## Prerequisites

1. Ensure you have all dependencies installed:
   ```bash
   composer install
   ```

2. Make sure the `rr.exe` binary is present in your project root

3. Ensure the `.rr.yaml` configuration file exists in your project root

## Method 1: Direct RoadRunner Execution (Recommended)

### Command
```powershell
./rr.exe serve -c .rr.yaml
```

### What this does:
- Starts RoadRunner directly using the project's configuration
- Bypasses Laravel Octane's command-line interface
- Avoids Windows signal handling issues
- Server will be accessible at http://127.0.0.1:9000

### To stop the server:
Press `CTRL+C` in the terminal

## Method 2: Using PowerShell with Error Handling

If you prefer using a PowerShell script with error handling:

```powershell
powershell -ExecutionPolicy Bypass -File .\start-octane.ps1
```

Note: This may still encounter signal handling issues on some Windows systems.

## Method 3: Direct PHP Artisan Command (May have issues on Windows)

```bash
php artisan octane:start --server=roadrunner --host=127.0.0.1 --port=8000
```

Note: This method may encounter signal handling errors on Windows systems.

## Configuration Details

The `.rr.yaml` file configures RoadRunner with:
- Host: 0.0.0.0 (accessible from any IP)
- Port: 9000 (as configured)
- Static file serving from the `public` directory
- Development logging mode

## Troubleshooting

### If you get signal handling errors:
Use the direct RoadRunner method instead of the artisan command:
```powershell
./rr.exe serve -c .rr.yaml
```

### If rr.exe is not found:
Ensure the RoadRunner binary is in your project root, or install it using:
```bash
./vendor/bin/rr get-binary
```

### If you get permission errors:
Make sure the rr.exe file has execute permissions.

## Accessing Your Application

Once started, your application will be available at:
- http://127.0.0.1:9000
- http://localhost:9000
- http://0.0.0.0:9000 (from local machine only)

## Running as a Windows Service with NSSM

To install Laravel Octane as a Windows service using NSSM:

1. Download and install NSSM from https://nssm.cc/download

2. Open Command Prompt as Administrator and run:
   ```cmd
   nssm install LaravelOctane "D:\Github\Simentor3215\backend\rr.exe" "serve -c D:\Github\Simentor3215\backend\.rr.yaml"
   ```

3. Manage the service with these commands:
   ```cmd
   # Start the service
   nssm start LaravelOctane
   
   # Stop the service
   nssm stop LaravelOctane
   
   # Restart the service
   nssm restart LaravelOctane
   
   # Check service status
   nssm status LaravelOctane
   
   # Remove the service
   nssm remove LaravelOctane confirm
   ```

4. For manual configuration, run `nssm install LaravelOctane` (without parameters) and set:
   - **Path**: `E:\Github\japekv3\backend\rr.exe`
   - **Arguments**: `serve -c E:\Github\japekv3\backend\.rr.yaml`
   - **Startup directory**: `E:\Github\japekv3\backend`

5. Set environment variables in the NSSM service configuration:
   - `APP_ENV=production`
   - `APP_DEBUG=false`

6. Configure logging in the NSSM GUI:
   - **Output (stdout)**: `E:\Github\japekv3\backend\storage\logs\octane-stdout.log`
   - **Error (stderr)**: `E:\Github\japekv3\backend\storage\logs\octane-stderr.log`

## Stopping the Server

To stop the server, press `CTRL+C` in the terminal where RoadRunner is running.