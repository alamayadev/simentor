# Running Laravel Octane as a Windows Service with NSSM

This guide explains how to set up Laravel Octane with RoadRunner as a Windows service using the Non-Sucking Service Manager (NSSM).

## Prerequisites

1. Laravel Octane with RoadRunner installed and configured
2. NSSM installed on your Windows system
3. PHP installed and accessible from the command line

## Method 1: Using PowerShell Script (Recommended)

This is the recommended approach as it provides better control over environment variables and error handling.

### 1. Create a Service Using the Existing PowerShell Script

```powershell
# Open PowerShell as Administrator and run:
nssm install LaravelOctane "C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe" "-ExecutionPolicy Bypass -File D:\Documents\GitHub\japekv3\backend\start-octane.ps1"
```

### 2. Configure the Service (Optional)

To configure additional service settings:

```powershell
nssm edit LaravelOctane
```

In the NSSM GUI, you can set:
- Application tab:
  - Path: `C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe`
  - Arguments: `-ExecutionPolicy Bypass -File D:\Documents\GitHub\japekv3\backend\start-octane.ps1`
  - Startup directory: `D:\Documents\GitHub\japekv3\backend`
- Details tab:
  - Display name: `Laravel Octane Server`
  - Description: `Laravel Octane with RoadRunner Server`
- Log on tab:
  - Set appropriate user account if needed
- Log tab:
  - Output file: `D:\Documents\GitHub\japekv3\backend\storage\logs\octane-service-out.log`
  - Error file: `D:\Documents\GitHub\japekv3\backend\storage\logs\octane-service-error.log`

## Method 2: Using Direct PHP Execution

### 1. Create a Service Using Direct PHP Execution

```powershell
# Open PowerShell as Administrator and run:
nssm install LaravelOctane "C:\Users\user\.config\herd\bin\php84\php.exe" "D:\Documents\GitHub\japekv3\backend\start-rr.php"
```

### 2. Configure the Service

```powershell
nssm edit LaravelOctane
```

Set the startup directory to: `D:\Documents\GitHub\japekv3\backend`

## Method 3: Using Batch File

### 1. Create a Service Using the Batch File

```powershell
# Open PowerShell as Administrator and run:
nssm install LaravelOctane "D:\Documents\GitHub\japekv3\backend\start-octane.bat"
```

## Managing the Service

### Start the Service

```powershell
nssm start LaravelOctane
```

### Stop the Service

```powershell
nssm stop LaravelOctane
```

### Restart the Service

```powershell
nssm restart LaravelOctane
```

### Check Service Status

```powershell
nssm status LaravelOctane
```

### Remove the Service

```powershell
nssm remove LaravelOctane confirm
```

### Using the Management Script

For easier service management, you can use the provided batch script:

```cmd
# Start the service
manage-octane-service.bat start

# Stop the service
manage-octane-service.bat stop

# Restart the service
manage-octane-service.bat restart

# Check service status
manage-octane-service.bat status
```

To install or remove the service using the script, run it as Administrator:

```cmd
# Install the service (requires admin)
manage-octane-service.bat install

# Remove the service (requires admin)
manage-octane-service.bat remove
```

## Service Configuration Details

### Environment Variables

The PowerShell script ([start-octane.ps1](file:///d:/Documents/GitHub/japekv3/backend/start-octane.ps1)) automatically sets these environment variables:
- `APP_ENV=production`
- `APP_DEBUG=false`

If using other methods, you may need to set these manually in the NSSM configuration:
1. In the NSSM GUI, go to the "Environment" tab
2. Add these variables:
   - `APP_ENV` = `production`
   - `APP_DEBUG` = `false`

### Working Directory

Ensure the working directory is set to your Laravel project root:
`D:\Documents\GitHub\japekv3\backend`

### Log Files

NSSM can redirect stdout and stderr to log files:
- Output log: `D:\Documents\GitHub\japekv3\backend\storage\logs\octane-service-out.log`
- Error log: `D:\Documents\GitHub\japekv3\backend\storage\logs\octane-service-error.log`

## Troubleshooting

### Common Issues

1. **Service fails to start:**
   - Check that all file paths are correct
   - Ensure the user account has proper permissions
   - Verify PHP is accessible from the command line
   - Check the error logs for specific error messages

2. **Permission denied errors:**
   - Run PowerShell as Administrator
   - Ensure the service user has read/write permissions to the project directory
   - Check that the RoadRunner binary has execute permissions

3. **Port already in use:**
   - Check if another instance is running:
     ```powershell
     netstat -ano | findstr :9000
     ```
   - Kill the conflicting process if needed:
     ```powershell
     taskkill /PID <process_id> /F
     ```

4. **Configuration issues:**
   - Verify the RoadRunner configuration in [.rr.yaml](file:///d:/Documents/GitHub/japekv3/backend/.rr.yaml)
   - Ensure the custom worker script ([custom-worker.php](file:///d:/Documents/GitHub/japekv3/backend/custom-worker.php)) is properly configured

### Checking Service Logs

View the service logs to troubleshoot issues:

```powershell
# View output log
Get-Content -Path "D:\Documents\GitHub\japekv3\backend\storage\logs\octane-service-out.log" -Tail 20

# View error log
Get-Content -Path "D:\Documents\GitHub\japekv3\backend\storage\logs\octane-service-error.log" -Tail 20
```

## Performance Considerations

When running Octane as a service:

1. **Memory Management:**
   - Configure appropriate max requests in [.rr.yaml](file:///d:/Documents/GitHub/japekv3/backend/.rr.yaml) to prevent memory leaks:
     ```yaml
     http:
       pool:
         max_jobs: 500
     ```

2. **Worker Configuration:**
   - Adjust the number of workers based on your system resources in [.rr.yaml](file:///d:/Documents/GitHub/japekv3/backend/.rr.yaml):
     ```yaml
     http:
       pool:
         num_workers: 4
     ```

3. **Monitoring:**
   - Use the provided monitoring scripts:
     - [monitor-resources.ps1](file:///d:/Documents/GitHub/japekv3/backend/monitor-resources.ps1) - Monitor system resources
     - [test-server.ps1](file:///d:/Documents/GitHub/japekv3/backend/test-server.ps1) - Test server responsiveness

## Security Considerations

1. **Service Account:**
   - Run the service with the minimum required permissions
   - Don't use an administrator account unless absolutely necessary

2. **Network Security:**
   - Consider using a reverse proxy (Nginx/Apache) in front of Octane
   - Restrict access to the Octane port (9000) if needed

3. **File Permissions:**
   - Ensure log files are properly secured
   - Restrict access to configuration files

## Verification

After setting up the service, verify it's working correctly:

1. Start the service:
   ```powershell
   nssm start LaravelOctane
   ```

2. Check the service status:
   ```powershell
   nssm status LaravelOctane
   ```

3. Test the application:
   ```powershell
   curl http://localhost:9000
   ```

4. Check the logs for any errors:
   ```powershell
   Get-Content -Path "D:\Documents\GitHub\japekv3\backend\storage\logs\octane-service-error.log" -Tail 10
   ```

## Related Documentation

- [start_octane.md](file:///d:/Documents/GitHub/japekv3/backend/start_octane.md) - Complete guide to setting up Laravel Octane
- [benchmarking.md](file:///d:/Documents/GitHub/japekv3/backend/benchmarking.md) - Server testing and benchmarking procedures
- [SERVER_TESTING.md](file:///d:/Documents/GitHub/japekv3/backend/SERVER_TESTING.md) - Additional server testing information

## Scripts Reference

This project includes several helper scripts:
- [start-octane.ps1](file:///d:/Documents/GitHub/japekv3/backend/start-octane.ps1) - PowerShell script with environment setup
- [start-octane.bat](file:///d:/Documents/GitHub/japekv3/backend/start-octane.bat) - Batch script for command line usage
- [start-octane-simple.php](file:///d:/Documents/GitHub/japekv3/backend/start-octane-simple.php) - Simple PHP script that bypasses signal handling
- [start-rr.php](file:///d:/Documents/GitHub/japekv3/backend/start-rr.php) - Simple PHP script to start RoadRunner directly
- [custom-worker.php](file:///d:/Documents/GitHub/japekv3/backend/custom-worker.php) - Custom RoadRunner worker that properly handles autoloading
- [.rr.yaml](file:///d:/Documents/GitHub/japekv3/backend/.rr.yaml) - RoadRunner configuration file
