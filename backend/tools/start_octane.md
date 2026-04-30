# Laravel Octane with RoadRunner on Windows 11

This guide provides step-by-step instructions for setting up and running Laravel Octane with RoadRunner on a Windows 11 machine.

## Prerequisites

- PHP 8.2 or higher (PHP 8.3+ recommended for Laravel 12)
- Composer
- Laravel 12 project
- Windows 11 environment

## Installation Steps

### 1. Install Laravel Octane

First, install the Octane package via Composer:

```bash
composer require laravel/octane
```

### 2. Install RoadRunner Dependencies

Install the required RoadRunner packages:

```bash
composer require spiral/roadrunner-http:^3.3.0 spiral/roadrunner-cli:^2.6.0
```

### 3. Install Octane with RoadRunner

Run the Octane install command:

```bash
php artisan octane:install
```

When prompted, select "roadrunner" as your preferred server.

If you encounter issues with the automatic binary download, you can manually install the RoadRunner binary:

```bash
# Download the binary manually
vendor/bin/rr get-binary

# Set permissions (Windows)
icacls rr.exe /grant Everyone:F
```

## Configuration

### Configure Octane Settings

Edit `config/octane.php` to customize your Octane server settings:

```php
<?php

return [
    'host' => '0.0.0.0',
    'port' => 9000,
    'workers' => 4, // Number of worker processes
    'task_workers' => 2, // Number of task worker processes
    'max_requests' => 500, // Restart worker after handling this many requests
    'memory_limit' => 256, // Memory limit in MB
    'rr_config' => [
        'rpc.listen' => 'tcp://127.0.0.1:6001',
        'server.command' => 'php ./vendor/bin/roadrunner-worker',
        'http.pool.num_workers' => 4,
        'http.pool.max_jobs' => 500,
        'http.pool.allocate_timeout' => 60,
        'http.pool.destroy_timeout' => 60,
    ],
];
```

### Environment Configuration

Update your `.env` file with Octane-specific settings:

```env
# Use 'octane' instead of 'file' for better performance
SESSION_DRIVER=cookie
LOG_LEVEL=debug
QUEUE_CONNECTION=sync
```

## Running Octane

### Start the Server

To start the Octane server:

```bash
php artisan octane:start
```

To specify host and port:

```bash
php artisan octane:start --host=0.0.0.0 --port=9000
```

To watch for file changes and automatically restart:

```bash
php artisan octane:start --watch
```

### Stop the Server

To stop the Octane server:

```bash
php artisan octane:stop
```

### Reload the Server

To reload the Octane server (useful when you've changed configuration):

```bash
php artisan octane:reload
```

## Windows-Specific Workarounds

### Signal Handling Issue

On Windows, you may encounter issues with signal handling. If you get an error like "Undefined constant SIGINT", try these workarounds:

1. Use fewer workers:
   ```bash
   php artisan octane:start --workers=1
   ```

2. Create a RoadRunner configuration file (.rr.yaml):
   ```yaml
   version: '3'
   
   rpc:
     listen: tcp://127.0.0.1:6001
   
   server:
     command: "php ./vendor/bin/roadrunner-worker"
     relay: "pipes"
   
   http:
     address: 0.0.0.0:9000
     middleware: [ "static", "gzip" ]
     static:
       dir: "public"
       forbid: [ ".php", ".htaccess" ]
   
   logs:
     mode: development
     level: debug
   ```

3. Run RoadRunner directly:
   ```bash
   ./rr.exe serve -c .rr.yaml
   ```

## Alternative Approach: Direct RoadRunner Usage

If you continue to have issues with the Octane wrapper on Windows, you can use RoadRunner directly:

### 1. Use the Provided Scripts

This repository includes several scripts to help you start RoadRunner directly:
- [start-rr.php](file:///d:/Documents/GitHub/japekv3/backend/start-rr.php) - Simple PHP script to start RoadRunner
- [custom-worker.php](file:///d:/Documents/GitHub/japekv3/backend/custom-worker.php) - Custom RoadRunner worker that properly handles autoloading
- [.rr.yaml](file:///d:/Documents/GitHub/japekv3/backend/.rr.yaml) - RoadRunner configuration file

### 2. Run RoadRunner Directly

```bash
# Using the PHP script
php start-rr.php

# Or directly
./rr.exe serve -c .rr.yaml
```

## Creating a Windows Service

To run Octane as a Windows service using NSSM:

### 1. Use the Provided Scripts

This repository includes several scripts to help you start Octane on Windows:
- [start-octane.ps1](file:///d:/Documents/GitHub/japekv3/backend/start-octane.ps1) - PowerShell script
- [start-octane.bat](file:///d:/Documents/GitHub/japekv3/backend/start-octane.bat) - Batch script
- [start-octane-simple.php](file:///d:/Documents/GitHub/japekv3/backend/start-octane-simple.php) - Simple PHP script

### 2. Create the Service with NSSM

Open PowerShell as Administrator and run:

```powershell
nssm install LaravelOctane "C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe" "-ExecutionPolicy Bypass -File D:\Documents\GitHub\japekv3\backend\start-octane.ps1"
```

Or for the batch file:

```powershell
nssm install LaravelOctane "D:\Documents\GitHub\japekv3\backend\start-octane.bat"
```

Or for the PHP script:

```powershell
nssm install LaravelOctane "C:\Users\user\.config\herd\bin\php84\php.exe" "D:\Documents\GitHub\japekv3\backend\start-octane-simple.php"
```

Or for direct RoadRunner usage:

```powershell
nssm install LaravelOctane "C:\Users\user\.config\herd\bin\php84\php.exe" "D:\Documents\GitHub\japekv3\backend\start-rr.php"
```

### 3. Configure the Service (Optional)

To configure additional service settings:

```powershell
nssm edit LaravelOctane
```

### 4. Start/Stop the Service

```powershell
nssm start LaravelOctane
nssm stop LaravelOctane
```

For detailed instructions on setting up Laravel Octane as a Windows service with NSSM, including troubleshooting and configuration options, please refer to the [octane-windows-service.md](octane-windows-service.md) file.

## Performance Tuning

### Worker Configuration

Adjust the number of workers based on your system resources:

```php
// config/octane.php
'workers' => shell_exec('nproc') ? (int) shell_exec('nproc') : 4,
'task_workers' => 2,
```

### Memory Management

Set appropriate memory limits to prevent memory leaks:

```php
// config/octane.php
'max_requests' => 500,
'memory_limit' => 256,
```

### File Watching

Enable file watching for development:

```bash
php artisan octane:start --watch
```

Customize watched directories in `config/octane.php`:

```php
'watch' => [
    'app',
    'bootstrap',
    'config',
    'database',
    'public/**/*.php',
    'resources/**/*.php',
    'routes',
    'composer.lock',
    '.env',
],
```

## Troubleshooting

### Common Issues

1. **Port already in use:**
   ```bash
   php artisan octane:start --port=9001
   ```

2. **Missing dependencies:**
   ```bash
   composer install
   ```

3. **Permission issues:**
   Run PowerShell as Administrator when using NSSM

4. **RoadRunner binary not found:**
   Reinstall Octane:
   ```bash
   composer require laravel/octane --force
   php artisan octane:install
   ```

5. **Signal handling errors on Windows:**
   Use fewer workers, run RoadRunner directly, or use the provided scripts

### Logs and Monitoring

Check Octane logs:

```bash
tail -f storage/logs/laravel.log
```

Monitor server status:

```bash
php artisan octane:status
```

## Production Considerations

### Security

1. Don't expose Octane directly to the internet
2. Use a reverse proxy like Nginx or Apache
3. Set proper environment variables:
   ```env
   APP_ENV=production
   APP_DEBUG=false
   ```

### Reverse Proxy Configuration (Nginx Example)

``nginx
server {
    listen 80;
    server_name your-domain.com;
    
    location / {
        proxy_pass http://127.0.0.1:9000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
}
```

### Performance Monitoring

Enable Octane metrics:

```php
// config/octane.php
'metrics' => [
    'enabled' => true,
    'host' => '127.0.0.1',
    'port' => 9001,
],
```

## Useful Commands

| Command | Description |
|---------|-------------|
| `php artisan octane:start` | Start the Octane server |
| `php artisan octane:stop` | Stop the Octane server |
| `php artisan octane:reload` | Reload the Octane server |
| `php artisan octane:status` | Check Octane server status |
| `php artisan octane:inspect` | Inspect the Octane server |

## Conclusion

Laravel Octane with RoadRunner provides significant performance improvements over the traditional `php artisan serve` command. It's well-suited for production use on Windows 11 when properly configured.

For very small traffic applications, Octane still offers benefits:
- Faster response times
- Better memory management
- Automatic worker restarts
- Improved concurrent request handling

Remember to monitor your application's performance and adjust the configuration as needed based on your specific requirements and traffic patterns.

## Known Issues on Windows

1. **Signal Handling**: Windows doesn't support Unix signals, which can cause issues with the Octane command.
2. **File Permissions**: Ensure the RoadRunner binary has proper execution permissions.
3. **Path Issues**: Use absolute paths when possible to avoid issues with directory changes.

For the best experience on Windows, consider using the direct RoadRunner approach with a custom configuration file rather than the Octane wrapper if you encounter persistent issues.

## Included Scripts

This repository includes helper scripts to make running Octane on Windows easier:
- [start-octane.ps1](file:///d:/Documents/GitHub/japekv3/backend/start-octane.ps1) - PowerShell script with environment setup
- [start-octane.bat](file:///d:/Documents/GitHub/japekv3/backend/start-octane.bat) - Batch script for command line usage
- [start-octane-simple.php](file:///d:/Documents/GitHub/japekv3/backend/start-octane-simple.php) - Simple PHP script that bypasses signal handling
- [start-rr.php](file:///d:/Documents/GitHub/japekv3/backend/start-rr.php) - Simple PHP script to start RoadRunner directly
- [custom-worker.php](file:///d:/Documents/GitHub/japekv3/backend/custom-worker.php) - Custom RoadRunner worker that properly handles autoloading
- [.rr.yaml](file:///d:/Documents/GitHub/japekv3/backend/.rr.yaml) - RoadRunner configuration file
