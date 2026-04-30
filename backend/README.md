# Mobil Japek - Laravel StarterKit

Mobil Japek is a Laravel 11-based starter kit designed to simplify common web development tasks, offering an elegant and intuitive development experience. It integrates modern tools like Livewire 3, TailwindCSS, and Flowbite UI to enhance productivity and user experience.

## API Server

This project includes a comprehensive RESTful API server built with Laravel 11. The API provides endpoints for user management, authentication, role-based access control, and various business entities.

### Base URL (Development)
```
http://127.0.0.1:8000
```

### Authentication
The API uses Bearer token authentication. To authenticate:
1. Send a POST request to `/api/login` with email and password
2. Use the returned token in the Authorization header for subsequent requests:
   ```
   Authorization: Bearer YOUR_TOKEN_HERE
   ```

### Available API Endpoints

- **Authentication**
  - `POST /api/login` - User login
  - `POST /api/logout` - User logout

- **Users Management**
  - `GET /api/users` - List all users
  - `GET /api/users/{id}` - Show user details
  - `POST /api/users` - Create a new user
  - `PUT /api/users/{id}` - Update a user
  - `DELETE /api/users/{id}` - Delete a user

- **Roles Management** (Super-admin only)
  - `GET /api/admin/roles` - List all roles
  - `GET /api/admin/roles/{id}` - Show role details
  - `POST /api/admin/roles` - Create a new role
  - `PUT /api/admin/roles/{id}` - Update a role
  - `DELETE /api/admin/roles/{id}` - Delete a role

- **Permissions Management** (Super-admin only)
  - `GET /api/admin/permissions` - List all permissions
  - `GET /api/admin/permissions/{id}` - Show permission details
  - `POST /api/admin/permissions` - Create a new permission
  - `PUT /api/admin/permissions/{id}` - Update a permission
  - `DELETE /api/admin/permissions/{id}` - Delete a permission

- **Employees Management**
  - `GET /api/pegawai` - List all employees
  - `GET /api/pegawai/{id}` - Show employee details
  - `POST /api/pegawai` - Create a new employee
  - `PUT /api/pegawai/{id}` - Update an employee
  - `DELETE /api/pegawai/{id}` - Delete an employee
  - `GET /api/pegawai/jabatan-pangkat-list` - Get unique job positions and ranks

- **Partners Management**
  - `GET /api/mitra` - List all partners

- **Activities Management**
  - `GET /api/kegiatan` - List all activities
  - `GET /api/kegiatan/{id}` - Show activity details
  - `POST /api/kegiatan` - Create a new activity
  - `PUT /api/kegiatan/{id}` - Update an activity
  - `DELETE /api/kegiatan/{id}` - Delete an activity

- **Assignments Management**
  - `GET /api/penugasan` - List all assignments
  - `GET /api/penugasan/{id}` - Show assignment details
  - `POST /api/penugasan` - Create a new assignment
  - `PUT /api/penugasan/{id}` - Update an assignment
  - `DELETE /api/penugasan/{id}` - Delete an assignment

For detailed API documentation, please refer to the [API_DOCS.md](API_DOCS.md) file.

## Libraries and Technologies Used

### Backend
- **Laravel 11.31** - PHP framework for web artisans
- **Laravel Jetstream 5.3** - Authentication scaffolding
- **Laravel Sanctum 4.0** - API token authentication
- **Spatie Laravel Permission 6.10** - Associate users with roles and permissions
- **Spatie Laravel Query Builder 6.3** - Easily build Eloquent queries from API requests
- **Barryvdh Laravel DOMPDF 3.0** - HTML to PDF converter
- **Calebporzio Sushi 2.5** - Eloquent models from arrays
- **IIo Libmergepdf 4.0** - PDF merging library
- **PhpOffice PHPWord 1.3** - Library for reading and writing Word documents
- **Smalot PDFParser 2.11** - PDF parser library

### Development Tools
- **Laravel Pint** - PHP code style fixer
- **Laravel Sail** - Docker-based development environment
- **Laravel Debugbar** - Debug toolbar for Laravel
- **Enlightn** - Laravel performance, security and reliability analyzer
- **PestPHP** - PHP testing framework
- **Knuckleswtf Scribe** - Generate API documentation
- **Laravel Lang** - Language files for Laravel
- **Laravel Pail** - Tail Laravel logs in real-time
- **Wire Elements Wire Spy** - Debugging tool for Livewire

## Installation

### Prerequisites
- PHP 8.2 or higher
- Composer
- Node.js 18 or higher
- NPM/Yarn/PNPM
- MySQL or SQLite database

### Steps

1. **Clone the repository**
   ```bash
   git clone https://github.com/budiyunior/japekv3.git backend
   cd backend
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install JavaScript dependencies**
   ```bash
   npm install
   ```

4. **Copy and configure the environment file**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   
   Edit the `.env` file to match your database configuration and other settings.

5. **Run database migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

### Seeder safety: SEEDER_AUTO_TRUNCATE

This project includes a safe seeder workflow to prevent accidental destructive truncation of tables.

- Truncation runs only when BOTH conditions are true:
  1. `SEEDER_AUTO_TRUNCATE=true` is set in `.env`
  2. `APP_ENV` is `local` or `testing`

Example `.env` for local usage:

```bash
SEEDER_AUTO_TRUNCATE=true
APP_ENV=local
```

Run seeders (local/test only):

```powershell
php artisan migrate
php artisan db:seed
```

If you prefer to run seeders without truncation (default), leave `SEEDER_AUTO_TRUNCATE` unset or set to `false`. Always backup your database or run on a local copy when enabling truncation.


6. **Start the development server**
   ```bash
   # In one terminal, start the Laravel development server
   php artisan serve
   
   # In another terminal, start the Vite development server
   npm run dev
   ```

   Alternatively, you can use the combined command:
   ```bash
   composer run dev
   ```

## Google Drive Integration Setup

This project includes Google Drive integration for automatic upload of SKP (Sasaran Kinerja Pegawai) PDF files. The integration uses OAuth 2.0 for secure authentication.

### Prerequisites

1. **Google Cloud Console Account**: You need a Google account with access to Google Cloud Console
2. **Google Drive API Enabled**: Enable the Google Drive API in your Google Cloud project
3. **OAuth 2.0 Credentials**: Create OAuth 2.0 credentials for a desktop application

### Google Cloud Console Setup

1. **Create or select a project** in [Google Cloud Console](https://console.cloud.google.com/)

2. **Enable Google Drive API**:
   - Go to "APIs & Services" > "Library"
   - Search for "Google Drive API"
   - Click "Enable"

3. **Create OAuth 2.0 credentials**:
   - Go to "APIs & Services" > "Credentials"
   - Click "Create Credentials" > "OAuth 2.0 Client IDs"
   - Choose "Desktop application" as the application type
   - Download the JSON file containing your credentials

4. **Configure authorized redirect URIs** (if needed):
   - For development: `http://localhost`
   - For production: Your domain URL

### Laravel Configuration

1. **Copy OAuth credentials file**:
   ```bash
   # Place your downloaded OAuth credentials JSON file in:
   storage/app/google-oauth-credentials.json
   ```

2. **Configure environment variables** in your `.env` file:
   ```bash
   # Google Drive Integration
   GOOGLE_DRIVE_ENABLED=true
   GOOGLE_DRIVE_AUTH_TYPE=oauth
   GOOGLE_DRIVE_OAUTH_CREDENTIALS_PATH=storage/app/google-oauth-credentials.json
   GOOGLE_DRIVE_OAUTH_TOKEN_PATH=storage/app/google-oauth-token.json
   GOOGLE_DRIVE_FOLDER_ID=YOUR_GOOGLE_DRIVE_FOLDER_ID
   ```

3. **Create and share Google Drive folder**:
   - Create a folder in Google Drive where SKP files will be uploaded
   - Copy the folder ID from the URL (the long string after `/folders/`)
   - Share the folder with your Google Cloud service account or ensure it's accessible

### OAuth Authorization

After configuring the credentials, authorize the application to access Google Drive:

```bash
php artisan google:authorize
```

This command will:
1. Display an authorization URL
2. Prompt you to visit the URL in your browser
3. Ask you to grant permission to access Google Drive
4. Provide an authorization code to paste back into the terminal
5. Exchange the code for access and refresh tokens

### Testing the Integration

Test the Google Drive connection:

```bash
# Test basic connection
php artisan google:test-drive

# Test SKP upload functionality (creates test files)
php artisan google:test-skp-upload
```

### Queue Worker Setup

The Google Drive upload runs asynchronously via Laravel queues. Start the queue worker:

```bash
php artisan queue:work --tries=3 --sleep=3 --timeout=90
```

For production, set up the queue worker as a background service using NSSM (see [QUEUE_WORKER_NSSM.md](QUEUE_WORKER_NSSM.md)).

### Folder Structure

SKP files are automatically organized in Google Drive with this structure:

```
📁 Root Folder (configured in GOOGLE_DRIVE_FOLDER_ID)
├── 📁 2024/
│   ├── 📁 00. Penetapan/
│   ├── 📁 01. Januari/
│   ├── 📁 02. Februari/
│   └── ...
└── 📁 SKP Penilaian dan Evaluasi 2024/
    ├── SKP_Tahunan_Penilaian_2024_UserName.pdf
    └── SKP_Evaluasi_Tahunan_2024_UserName.pdf
```

### Deployment Considerations

When deploying to another machine or server:

1. **Copy credential files**:
   - `storage/app/google-oauth-credentials.json`
   - `storage/app/google-oauth-token.json`

2. **Environment variables**: Ensure all `GOOGLE_DRIVE_*` variables are set

3. **Token refresh**: The system automatically refreshes expired access tokens using the refresh token

4. **Re-authorization**: If the refresh token expires (rare), re-run `php artisan google:authorize`

### Troubleshooting

**SSL Certificate Issues** (Windows):
If you encounter SSL certificate errors, the system will automatically configure PHP's CA certificates. If issues persist:

```bash
# Download CA certificates
curl -o cacert.pem https://curl.se/ca/cacert.pem

# Update php.ini (adjust path to your PHP installation)
curl.cainfo = "C:\php\cacert.pem"
openssl.cafile = "C:\php\cacert.pem"
```

**Permission Errors**:
- Ensure the Google Drive folder is shared with the appropriate account
- Check that the folder ID in `.env` is correct
- Verify OAuth scopes include Google Drive access

**Token Expiration**:
- Access tokens expire after ~1 hour but are automatically refreshed
- Refresh tokens can last months/years but may eventually require re-authorization

### Development Commands

- `php artisan serve` - Start Laravel development server
- `composer run dev` - Run all development servers concurrently
- `php artisan queue:work` - Start queue worker for background jobs
- `php artisan google:authorize` - Authorize Google Drive access
- `php artisan google:test-drive` - Test Google Drive connection
- `php artisan google:test-skp-upload` - Test SKP upload functionality

### Laravel Octane

This project uses Laravel Octane with RoadRunner for improved performance. For detailed information on setting up and running Octane, please refer to the [start_octane.md](start_octane.md) file.

#### Run Octane with RoadRunner (direct, Windows)

If `php artisan octane:start` fails on Windows, you can run RoadRunner directly using the bundled `rr.exe` and the repository `.rr.yaml` configuration file. This is the approach we use when the normal Octane wrapper doesn't work.

1. Ensure `rr.exe` is present in the project root (or in PATH). If Octane didn't download the binary automatically, see `start_octane.md` for manual install steps.

2. Start RoadRunner directly from the project root:

```powershell
.\rr.exe serve -c .rr.yaml
```

3. The repository includes a sample RoadRunner config `.rr.yaml` and helper scripts (`start-rr.php`, `start-octane.ps1`) in the project root — see `start_octane.md` for full examples and Windows service setup.

This direct approach bypasses the Octane wrapper and is useful for Windows environments where signal handling or binary download issues occur.


#### Server Testing and Benchmarking

This project includes comprehensive tools for testing and benchmarking server performance. For detailed information on performance testing, benchmarking methods, and optimization strategies, please refer to the [benchmarking.md](benchmarking.md) file.

#### Running Octane as a Windows Service

For instructions on setting up Laravel Octane as a Windows service using NSSM, please refer to the [octane-windows-service.md](octane-windows-service.md) file.

#### Quick Service Setup Reference

For a quick summary of the service setup process, see [SERVICE_SETUP_SUMMARY.md](SERVICE_SETUP_SUMMARY.md).

#### NSSM (Windows service) — Minimal setup guide

If you want Octane / RoadRunner to run as a background service on Windows, use NSSM (the Non-Sucking Service Manager). The repository already includes helper scripts (`start-octane.ps1`, `start-octane.bat`, `start-rr.php`) — use the one that matches how you run the server locally.

Recommended NSSM configuration (example uses the PowerShell script):

1. Open PowerShell as Administrator and install the service (adjust paths to match your environment):

```powershell
nssm install LaravelOctane "C:\Windows\System32\WindowsPowerShell\v1.0\powershell.exe" "-ExecutionPolicy Bypass -File D:\Documents\GitHub\japekv3\backend\start-octane.ps1"
```

2. Configure the service entirely from the terminal using `nssm set` (no GUI required). Example commands (run in an Administrator PowerShell):

# set working directory
nssm set LaravelOctane AppDirectory "D:\\Documents\\GitHub\\japekv3\\backend"

# set command-line arguments when the Path is powershell.exe or php.exe
nssm set LaravelOctane AppParameters "-ExecutionPolicy Bypass -File D:\\Documents\\GitHub\\japekv3\\backend\\start-octane.ps1"

# redirect stdout / stderr to log files
nssm set LaravelOctane AppStdout "D:\\Documents\\GitHub\\japekv3\\backend\\storage\\logs\\nssm_stdout.log"
nssm set LaravelOctane AppStderr "D:\\Documents\\GitHub\\japekv3\\backend\\storage\\logs\\nssm_stderr.log"

# enable log rotation and automatic restart policy
nssm set LaravelOctane AppRotateFiles 1
nssm set LaravelOctane AppRestartDelay 2000

# add environment variables (one per command). Use AppEnvironmentExtra for extra entries:
nssm set LaravelOctane AppEnvironmentExtra "APP_ENV=production"
nssm set LaravelOctane AppEnvironmentExtra "APP_DEBUG=false"

# If you need to point NSSM to rr.exe directly, install then set the parameters:
nssm set LaravelOctane AppParameters "serve -c D:\\Documents\\GitHub\\japekv3\\backend\\.rr.yaml"
nssm set LaravelOctane AppDirectory "D:\\Documents\\GitHub\\japekv3\\backend"

Notes:
- Use absolute paths for all NSSM settings to avoid path resolution issues when the service starts under a system account.
- Verify the log file paths exist and the service account has write permission.
- To review current settings from the terminal, use `nssm get <ServiceName> <Key>` (for example `nssm get LaravelOctane AppDirectory`).
- To remove an extra environment entry previously set with `AppEnvironmentExtra`, use `nssm set LaravelOctane AppEnvironmentExtra ""` and re-add the entries you want.

3. Example using direct RoadRunner start script (if you prefer rr.exe):

```powershell
nssm install LaravelOctane "D:\Documents\GitHub\japekv3\backend\rr.exe" "serve -c D:\Documents\GitHub\japekv3\backend\.rr.yaml"
nssm set LaravelOctane AppDirectory D:\Documents\GitHub\japekv3\backend
```

4. Start/Stop the service

```powershell
nssm start LaravelOctane
nssm stop LaravelOctane
```

Notes & best practices
- Use absolute paths in NSSM configuration to avoid issues when the service starts under a different working directory.
- Ensure the user account that runs the service has permission to read/write the project files and write logs.
- If you use a packaged PHP (e.g. herd/php) or a custom PHP binary, point NSSM Path to that `php.exe` and Arguments to the script you want to run (for example `start-rr.php` or `start-octane-simple.php`).
- Configure stdout/stderr capture inside NSSM to files under `storage/logs` so Laravel log rotation/backups can be used.
- When using RoadRunner directly, point `rr.exe` to `.rr.yaml` in the repository root so it loads the proper HTTP, RPC, and worker config.


## Features

- **Authentication & Authorization**
  - User registration and login
  - Role-based access control (RBAC)
  - Permission management
  - Password reset functionality

- **Admin Dashboard**
  - Livewire components for dynamic UI
  - User management interface
  - Role and permission management
  - Activity and assignment tracking

- **Business Entities**
  - Employee management
  - Partner management
  - Activity management
  - Assignment management
  - Document generation (PDF, Word)

- **API Documentation**
  - Comprehensive API documentation with examples
  - Role-based endpoint access
  - Pagination and filtering support

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is open-sourced software licensed under the [MIT license](LICENSE).

### To seed Individual Seeder
run `php artisan db:seed --class=Database\Seeders\SlsSipwTableSeeder -v`
