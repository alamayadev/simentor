# Run Laravel Queue Worker as Windows Service using NSSM

This guide explains how to set up the Laravel Queue Worker as a background Windows service using NSSM (Non-Sucking Service Manager). This ensures that your queue worker keeps running even if you close the terminal, and automatically restarts if the server reboots or the worker crashes.

## Prerequisites

1.  **NSSM**: Download NSSM from [https://nssm.cc/download](https://nssm.cc/download) and extract it to a known location (e.g., `C:\nssm\win64\nssm.exe`). Add this folder to your system PATH for easier access.
2.  **PHP**: Ensure PHP is installed and added to your system PATH.

## Installation Steps

Open Command Prompt (CMD) or PowerShell as **Administrator**.

### 1. Install the Service

Run the following command to open the NSSM GUI installer for a new service named `Japekv3Queue`:

```powershell
nssm install Japekv3Queue
```

### 2. Configure Application Tab

In the NSSM window that appears, configure the following:

*   **Path**: Path to your PHP executable.
    *   **Recommended**: Use the full absolute path (e.g., `C:\php\php.exe`) to avoid "file not found" errors if the service user account doesn't have the same PATH variables.
    *   **Optional**: If PHP is in your **System PATH** (not just User PATH), you can simply type `php.exe`.
    *   *Check your path*: Run `Get-Command php` in PowerShell to find your current path.
*   **Startup directory**: The root folder of your Laravel project.
    *   *Value*: `e:\Github\japekv3\backend`
*   **Arguments**: The artisan command to run the queue worker.
    *   *Value*: `artisan queue:work --tries=3 --sleep=3 --timeout=90`

### 3. Configure I/O (Logging)

Go to the **I/O** tab to capture logs. This is crucial for debugging if the worker fails.

*   **Output (stdout)**: `e:\Github\japekv3\backend\storage\logs\nssm_queue.log`
*   **Error (stderr)**: `e:\Github\japekv3\backend\storage\logs\nssm_queue_error.log`

### 4. Install Service

Click the **Install service** button. You should see a success message: "Service 'Japekv3Queue' installed successfully!"

## Managing the Service

Once installed, you can manage the service using the command line.

### Start the Service
```powershell
nssm start Japekv3Queue
# OR
net start Japekv3Queue
```

### Check Status
```powershell
nssm status Japekv3Queue
```

### Stop the Service
```powershell
nssm stop Japekv3Queue
# OR
net stop Japekv3Queue
```

### Remove the Service
If you need to uninstall the service:
```powershell
nssm remove Japekv3Queue confirm
```

## Command Line Installation (Alternative)

Instead of using the GUI, you can run these commands directly in an Administrator terminal:

```powershell
# 1. Install Service
nssm install Japekv3Queue "php.exe" "artisan queue:work --tries=3 --sleep=3 --timeout=90"

# 2. Set App Directory
nssm set Japekv3Queue AppDirectory "e:\Github\japekv3\backend"

# 3. Set Description
nssm set Japekv3Queue Description "Laravel Queue Worker for Japekv3 Backend"

# 4. Set Logging
nssm set Japekv3Queue AppStdout "e:\Github\japekv3\backend\storage\logs\nssm_queue.log"
nssm set Japekv3Queue AppStderr "e:\Github\japekv3\backend\storage\logs\nssm_queue_error.log"

# 5. Enable Log Rotation (Optional but recommended)
nssm set Japekv3Queue AppRotateFiles 1
nssm set Japekv3Queue AppRotateOnline 1
nssm set Japekv3Queue AppRotateSeconds 86400
nssm set Japekv3Queue AppRotateBytes 5242880

# 6. Start Service
nssm start Japekv3Queue
```
