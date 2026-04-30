# Production Deployment Guide for Simentor3215

This guide covers the complete setup for running Simentor3215 in production, including ClamAV file scanning and custom Octane configuration.

## Table of Contents
1. [Environment Setup](#environment-setup)
2. [ClamAV Installation and Configuration](#clamav-installation-and-configuration)
3. [Application Configuration](#application-configuration)
4. [Database Setup](#database-setup)
5. [Custom Octane Setup](#custom-octane-setup)
6. [File Upload Security](#file-upload-security)
7. [Deployment Steps](#deployment-steps)
8. [Monitoring and Maintenance](#monitoring-and-maintenance)

## Environment Setup

### 1. Copy Production Environment
```bash
cp .env.production .env
```

### 2. Generate Application Key
```bash
php artisan key:generate --env=production
```

### 3. Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build
```

## ClamAV Installation and Configuration

### Windows Installation

1. **Download ClamAV for Windows**
   - Download from: https://www.clamav.net/downloads
   - Or use Windows Subsystem for Linux (WSL)

2. **Install via Chocolatey (Recommended)**
   ```powershell
   # Install Chocolatey if not already installed
   Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))
   
   # Install ClamAV
   choco install clamav
   ```

3. **Install via WSL (Alternative)**
   ```bash
   # In WSL Ubuntu/Debian
   sudo apt update
   sudo apt install clamav clamav-freshclam
   
   # Update virus definitions
   sudo freshclam
   ```

### Linux Installation (Ubuntu/Debian)

```bash
sudo apt update
sudo apt install clamav clamav-freshclam

# Update virus definitions
sudo freshclam

# Enable and start ClamAV service
sudo systemctl enable clamav-freshclam
sudo systemctl start clamav-freshclam
```

### Configuration

1. **Update ClamAV Definitions Regularly**
   ```bash
   # Windows (with Chocolatey)
   freshclam
   
   # Linux
   sudo freshclam
   ```

2. **Test ClamAV Installation**
   ```bash
   # Test with a safe file
   echo "Test file" > test.txt
   clamscan test.txt
   
   # Clean up
   rm test.txt
   ```

## Application Configuration

### 1. Environment Variables for ClamAV

Add these to your `.env` file:

```env
# ClamAV Configuration
CLAMAV_ENABLED=true
CLAMAV_SOCKET=/var/run/clamav/clamd.ctl  # Linux
# CLAMAV_TCP_HOST=127.0.0.1  # Alternative TCP connection
# CLAMAV_TCP_PORT=3310       # Alternative TCP connection

# Windows specific (if using TCP)
CLAMAV_TCP_HOST=127.0.0.1
CLAMAV_TCP_PORT=3310
```

### 2. File Upload Configuration

```env
# File Upload Settings
FILE_MAX_SIZE=10240  # KB (10MB)
ALLOWED_FILE_TYPES=pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif
```

## Database Setup

### 1. Update Database Credentials in `.env`
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=your_production_db
DB_USERNAME=your_db_user
DB_PASSWORD=your_secure_password
```

### 2. Run Migrations
```bash
php artisan migrate --force
```

### 3. Optimize Database
```bash
php artisan db:show
php artisan db:optimize
```

## Custom Octane Setup

### Prerequisites

1. **Ensure RoadRunner binary is present**
   ```bash
   ./vendor/bin/rr get-binary
   ```

2. **Verify `.rr.yaml` configuration**
   ```yaml
   # Should exist in project root
   server:
     command: "php worker.php"
     relay: "pipes"
   http:
     address: 0.0.0.0:9000
     static:
       dir: "public"
       forbid: [".php", ".htaccess"]
   logs:
     mode: development
     level: debug
   ```

### Production Octane Commands

#### Method 1: Direct RoadRunner (Recommended for Production)
```powershell
# Windows
./rr.exe serve -c .rr.yaml

# Linux
./rr serve -c .rr.yaml
```

#### Method 2: Windows Service with NSSM
```cmd
# Install as Windows service
nssm install SimentorAPI "D:\path\to\project\rr.exe" "serve -c D:\path\to\project\.rr.yaml"

# Configure environment variables in NSSM:
# APP_ENV=production
# APP_DEBUG=false

# Start service
nssm start SimentorAPI
```

#### Method 3: Systemd Service (Linux)
Create `/etc/systemd/system/simentor-api.service`:

```ini
[Unit]
Description=Simentor API with Octane
After=network.target

[Service]
Type=simple
User=www-data
WorkingDirectory=/var/www/simentor3215/backend
Environment=APP_ENV=production
Environment=APP_DEBUG=false
ExecStart=/var/www/simentor3215/backend/rr serve -c /var/www/simentor3215/backend/.rr.yaml
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
```

```bash
# Enable and start service
sudo systemctl enable simentor-api
sudo systemctl start simentor-api
```

## File Upload Security with ClamAV

### 1. Laravel File Scanner Implementation

The application should use ClamAV for scanning uploaded files. Here's the implementation pattern:

```php
// Example usage in controllers
use App\Services\ClamAvScanner;

public function uploadFile(Request $request)
{
    $scanner = new ClamAvScanner();
    
    if ($scanner->scan($request->file('document'))) {
        // File is clean, proceed with upload
        // Your upload logic here
    } else {
        // File contains virus/malware
        return response()->json(['error' => 'File contains malware'], 422);
    }
}
```

### 2. ClamAV Scanner Service

Create the service if not exists:
```bash
php artisan make:service ClamAvScanner
```

### 3. Configure File Upload Validation

In your file upload controllers:
```php
$request->validate([
    'file' => 'required|file|max:10240|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,jpg,jpeg,png,gif'
]);
```

## Deployment Steps

### 1. Preparation
```bash
# Pull latest code
git pull origin main

# Install dependencies
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build

# Clear caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Optimize for production
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan optimize
```

### 2. File Permissions
```bash
# Linux/Unix
chmod -R 755 storage
chmod -R 755 bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 3. Start Services

#### Start ClamAV (Linux)
```bash
sudo systemctl start clamav-freshclam
sudo systemctl start clamav-daemon
```

#### Start Application with Octane
```bash
# Direct method
./rr serve -c .rr.yaml

# Or via service
sudo systemctl start simentor-api
```

### 4. Reverse Proxy Configuration

#### Nginx Configuration
```nginx
server {
    listen 80;
    server_name portal.bps3215.id;
    
    location / {
        proxy_pass http://127.0.0.1:9000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }
    
    location /static {
        alias /var/www/simentor3215/backend/public;
        expires 1y;
        add_header Cache-Control "public, immutable";
    }
}
```

## Monitoring and Maintenance

### 1. Log Monitoring
```bash
# Application logs
tail -f storage/logs/laravel.log

# Octane logs
tail -f storage/logs/octane.log

# ClamAV logs
tail -f /var/log/clamav/clamav.log
```

### 2. Health Checks
```bash
# Check ClamAV status
sudo systemctl status clamav-freshclam
sudo systemctl status clamav-daemon

# Check Octane status
curl http://127.0.0.1:9000/health

# Update ClamAV definitions
sudo freshclam
```

### 3. Backup Strategy
```bash
# Database backup
mysqldump -u username -p database_name > backup_$(date +%Y%m%d).sql

# File backup
tar -czf files_backup_$(date +%Y%m%d).tar.gz storage/app/uploads/
```

### 4. Security Updates
```bash
# Update ClamAV definitions (daily)
sudo freshclam

# Update system packages
sudo apt update && sudo apt upgrade

# Update PHP dependencies
composer update --no-dev
```

## Troubleshooting

### ClamAV Issues
```bash
# Check if ClamAV is running
sudo systemctl status clamav-daemon

# Test scan
clamscan --infected --recursive /tmp

# Update manually
sudo freshclam
```

### Octane Issues
```bash
# Check RoadRunner status
ps aux | grep rr

# Restart service
sudo systemctl restart simentor-api

# Check logs
tail -f storage/logs/octane-stderr.log
```

### File Upload Issues
```bash
# Check file permissions
ls -la storage/app/uploads/

# Test ClamAV scanning
echo "test" > testfile.txt
clamscan testfile.txt
rm testfile.txt
```

## Security Checklist

- [ ] ClamAV installed and running
- [ ] Virus definitions updated regularly
- [ ] File size limits configured
- [ ] Allowed file types restricted
- [ ] SSL/TLS certificates installed
- [ ] Firewall configured
- [ ] Database credentials secured
- [ ] Application in production mode (`APP_DEBUG=false`)
- [ ] Regular backups configured
- [ ] Monitoring and alerting setup
- [ ] Log rotation configured
- [ ] Security headers configured

## Performance Optimization

- [ ] Octane with RoadRunner configured
- [ ] Redis cache configured
- [ ] Database indexes optimized
- [ ] CDN for static assets
- [ ] Gzip compression enabled
- [ ] PHP OPcache configured
- [ ] Regular performance monitoring

This setup provides a secure, high-performance production environment with file scanning capabilities using ClamAV and the custom Octane configuration.