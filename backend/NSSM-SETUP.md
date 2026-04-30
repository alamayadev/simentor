# NSSM Setup Guide for Simentor Backend

This guide explains how to set up a single Windows service using NSSM (Non-Sucking Service Manager) to run both the RoadRunner web server and Laravel Queue worker for the Simentor backend application.

## Prerequisites

1. **NSSM Installed** - Download from [https://nssm.cc/download](https://nssm.cc/download)
2. **Administrative Privileges** - Required to install Windows services
3. **PHP and RoadRunner** - Already configured in your project

## Overview

**🎯 SINGLE SERVICE SOLUTION**

Instead of running two separate services, we'll create **ONE Windows service** that automatically launches both:
- RoadRunner web server (for HTTP requests)
- Laravel Queue worker (for background jobs like Google Drive uploads)

### ✅ Benefits of Single Service Approach:
- **One Service to Manage** - Install, start, stop, restart just one service
- **Coordinated Startup** - Both services start and stop together
- **Simplified Monitoring** - Check status of everything in one place
- **Atomic Operations** - No risk of one service running without the other
- **Resource Efficient** - Single service overhead instead of two
- **Easier Deployment** - One command to start your entire backend

## Setup Files

The setup uses two batch scripts:

### 1. `start-services.bat`
- Launches both RoadRunner and Laravel Queue worker
- Manages logging to `storage/logs/`
- Runs in production mode

### 2. `stop-services.bat`
- Cleanly stops both services
- Useful for manual service management

## Installation Steps

### Step 1: Open Command Prompt as Administrator

Right-click on Command Prompt and select "Run as administrator".

### Step 2: Navigate to Project Directory

```cmd
cd /d "D:\Documents\GitHub\Simentor3215\backend"
```

### Step 3: Install the Single Service

Run this command to create **ONE Windows service** that manages both RoadRunner and Laravel Queue:

```cmd
nssm install SimentorBackend "D:\Documents\GitHub\Simentor3215\backend\start-services.bat"
```

**📝 Note**: This installs a single service called `SimentorBackend` that automatically runs both your web server and queue worker.

### Step 4: Configure Service Properties

```cmd
# Set working directory
nssm set SimentorBackend AppDirectory "D:\Documents\GitHub\Simentor3215\backend"

# Configure logging
nssm set SimentorBackend AppStdout "D:\Documents\GitHub\Simentor3215\backend\storage\logs\nssm_stdout.log"
nssm set SimentorBackend AppStderr "D:\Documents\GitHub\Simentor3215\backend\storage\logs\nssm_stderr.log"
nssm set SimentorBackend AppRotateFiles 1
nssm set SimentorBackend AppRotateOnline 1

# Set restart on failure
nssm set SimentorBackend AppThrottle 5000
nssm set SimentorBackend AppExit Default Restart
nssm set SimentorBackend AppRestartDelay 3000

# Set service description
nssm set SimentorBackend Description "Simentor Backend Service (RoadRunner + Laravel Queue)"

# Set startup type to automatic
nssm set SimentorBackend Start SERVICE_AUTO_START
```

### Step 5: Start the Service

```cmd
nssm start SimentorBackend
```

## Service Management Commands

### Basic Commands

```cmd
# Start the service
nssm start SimentorBackend

# Stop the service
nssm stop SimentorBackend

# Restart the service
nssm restart SimentorBackend

# Check service status
nssm status SimentorBackend

# Edit service properties
nssm edit SimentorBackend
```

### Advanced Commands

```cmd
# View service logs
type "D:\Documents\GitHub\Simentor3215\backend\storage\logs\nssm_stdout.log"

# View application logs
type "D:\Documents\GitHub\Simentor3215\backend\storage\logs\roadrunner.log"
type "D:\Documents\GitHub\Simentor3215\backend\storage\logs\queue.log"

# Remove service (if needed)
nssm remove SimentorBackend confirm
```

## Quick Setup Script

Copy and paste this PowerShell script as administrator to automate the entire setup:

```powershell
# Navigate to project directory
Set-Location "D:\Documents\GitHub\Simentor3215\backend"

# Create log directories
New-Item -ItemType Directory -Force -Path "storage\logs"

# Install the service
nssm install SimentorBackend "D:\Documents\GitHub\Simentor3215\backend\start-services.bat"

# Configure service
nssm set SimentorBackend AppDirectory "D:\Documents\GitHub\Simentor3215\backend"
nssm set SimentorBackend AppStdout "D:\Documents\GitHub\Simentor3215\backend\storage\logs\nssm_stdout.log"
nssm set SimentorBackend AppStderr "D:\Documents\GitHub\Simentor3215\backend\storage\logs\nssm_stderr.log"
nssm set SimentorBackend AppRotateFiles 1
nssm set SimentorBackend AppRotateOnline 1
nssm set SimentorBackend AppThrottle 5000
nssm set SimentorBackend AppExit Default Restart
nssm set SimentorBackend AppRestartDelay 3000
nssm set SimentorBackend Description "Simentor Backend Service (RoadRunner + Laravel Queue)"
nssm set SimentorBackend Start SERVICE_AUTO_START

Write-Host "Service installed successfully!"
Write-Host "Run 'nssm start SimentorBackend' to start the service."
```

## Verification

### 1. Check Service Status

```cmd
nssm status SimentorBackend
```

### 2. Check Web Server

Open your browser and navigate to:
- `http://localhost:8000` (or your configured port)

### 3. Check Queue Processing

```cmd
# Check queue logs
type "storage\logs\queue.log"

# Test queue functionality
php artisan queue:monitor
```

### 4. Check Windows Services

Open Windows Services (`services.msc`) and look for "SimentorBackend".

## Troubleshooting

### Service Won't Start

1. **Check Permissions**: Ensure running as administrator
2. **Check Paths**: Verify all file paths are correct
3. **Check Logs**: Look at `storage\logs\nssm_stderr.log`

```cmd
type "storage\logs\nssm_stderr.log"
```

### Queue Worker Not Processing Jobs

1. **Check Queue Logs**:
   ```cmd
   type "storage\logs\queue.log"
   ```

2. **Test Queue Manually**:
   ```cmd
   php artisan queue:work --timeout=60 --sleep=1 --tries=3
   ```

### Web Server Not Responding

1. **Check RoadRunner Logs**:
   ```cmd
   type "storage\logs\roadrunner.log"
   ```

2. **Check Port Configuration**:
   ```cmd
   netstat -an | findstr :8000
   ```

### Restart Services Manually

If the service gets stuck, you can manually stop all processes:

```cmd
# Stop the NSSM service first
nssm stop SimentorBackend

# Kill any remaining processes
taskkill /F /IM rr.exe
taskkill /F /IM php.exe

# Restart the service
nssm start SimentorBackend
```

## How It Works: Single Service Architecture

The `start-services.bat` script acts as a **service launcher** that:

1. **🚀 Launches RoadRunner** in a background process
2. **🔄 Launches Laravel Queue Worker** in another background process  
3. **📝 Redirects Logs** to `storage/logs/` for both services
4. **⏱️ Keeps Running** to maintain the service status
5. **🔄 Auto-Restarts** if either process fails (NSSM handles this)

## Benefits of Single Service Approach

✅ **Simplified Management** - One service to manage instead of two  
✅ **Coordinated Startup** - Both services start together  
✅ **Unified Logging** - Centralized log management  
✅ **Resource Efficiency** - Single service overhead  
✅ **Easy Deployment** - One command to start everything  

## Production Considerations

1. **Environment Variables**: The service runs in production mode by default
2. **Log Rotation**: NSSM automatically rotates log files
3. **Auto-Restart**: Service restarts automatically if it crashes
4. **Memory Management**: Monitor memory usage in production
5. **Security**: Run with appropriate user permissions

## Migration from Manual Services

**🔄 SWITCHING FROM TWO SERVICES TO ONE**

If you currently have separate services or manual processes (like your current terminals):

1. **Stop existing processes**: Close your current terminal windows running RoadRunner and Queue
2. **Install the single service**: Follow the steps above to install `SimentorBackend`
3. **Remove old services** (if you had them):
   ```cmd
   nssm remove LaravelOctane confirm
   nssm remove LaravelQueue confirm
   ```
4. **Verify everything works**: Check that your web server and Google Drive uploads still function

**Result**: One service (`SimentorBackend`) replaces both individual services - simpler and more reliable!

## Support

For issues with:
- **NSSM**: Check [NSSM documentation](https://nssm.cc/usage)
- **RoadRunner**: Check `.rr.yaml` configuration
- **Laravel Queue**: Check Laravel documentation and logs

---

**Note**: This setup is optimized for Windows environments using NSSM for service management.