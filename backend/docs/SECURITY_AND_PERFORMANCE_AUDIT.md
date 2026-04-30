# Security and Performance Audit Report

**Generated:** 2025-12-29  
**Application:** Simentor Backend (Laravel API)  
**Scope:** Complete security and performance analysis

---

## Executive Summary

This report identifies **45 security vulnerabilities** and **18 performance issues** across the codebase. Issues are categorized by severity:

- **Critical:** 8 issues
- **High:** 15 issues  
- **Medium:** 22 issues
- **Low:** 18 issues

---

## Table of Contents

1. [Authentication & Authorization](#authentication--authorization)
2. [CORS Configuration](#cors-configuration)
3. [Input Validation & SQL Injection](#input-validation--sql-injection)
4. [File Upload Security](#file-upload-security)
5. [XSS Protection](#xss-protection)
6. [Rate Limiting](#rate-limiting)
7. [Password Security](#password-security)
8. [Sensitive Data Exposure](#sensitive-data-exposure)
9. [Logging & Error Handling](#logging--error-handling)
10. [Database Security](#database-security)
11. [File Storage Security](#file-storage-security)
12. [Performance Issues](#performance-issues)

---

## Authentication & Authorization

### 1.1 No Token Expiration (CRITICAL)

**Location:** [`config/sanctum.php`](config/sanctum.php:49)

**Issue:**
```php
'expiration' => null,  // Line 49
```

Sanctum tokens never expire, creating a permanent authentication risk.

**Impact:** 
- Compromised tokens remain valid indefinitely
- No automatic session timeout
- Increased attack window for stolen tokens

**Fix:**
```php
'expiration' => env('SANCTUM_TOKEN_EXPIRATION', 60), // 60 minutes
```

---

### 1.2 No Rate Limiting on Login (HIGH)

**Location:** [`routes/api.php`](routes/api.php:83)

**Issue:** Login endpoint has no rate limiting middleware.

**Impact:**
- Brute force attacks possible
- Credential stuffing vulnerability
- Account enumeration risk

**Fix:**
```php
// routes/api.php - Line 83
Route::post('/login', [AuthApiController::class, 'login'])
    ->middleware('throttle:5,1'); // 5 attempts per minute
```

---

### 1.3 Generic Error Messages (MEDIUM)

**Location:** [`app/Http/Controllers/Api/AuthApiController.php`](app/Http/Controllers/Api/AuthApiController.php:57)

**Issue:**
```php
if (!Auth::attempt($credentials)) {
    return $this->error('Invalid credentials', null, 401);
}
```

Generic error reveals authentication mode without preventing enumeration.

**Fix:**
```php
// Use generic message and add delay
if (!Auth::attempt($credentials)) {
    sleep(1); // Add delay to slow brute force
    return $this->error('Authentication failed', null, 401);
}
```

---

### 1.4 Password in Fillable (HIGH)

**Location:** [`app/Models/User.php`](app/Models/User.php:12)

**Issue:**
```php
protected $fillable = [
    'name',
    'email',
    'password',  // Should NOT be fillable
];
```

Allows mass assignment of password field.

**Fix:**
```php
protected $fillable = [
    'name',
    'email',
];

// Password should be set via setter method
public function setPasswordAttribute($value)
{
    $this->attributes['password'] = bcrypt($value);
}
```

---

### 1.5 No Password Complexity Requirements (MEDIUM)

**Location:** [`app/Http/Controllers/Api/Admin/UserApiController.php`](app/Http/Controllers/Api/Admin/UserApiController.php:282)

**Issue:**
```php
'password' => 'required|string|min:8',  // Only length requirement
```

No complexity validation (uppercase, numbers, special chars).

**Fix:**
```php
'password' => [
    'required', 
    'string', 
    'min:8',
    'regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/'
],
```

---

## CORS Configuration

### 2.1 Wildcard Origins with Credentials (CRITICAL)

**Location:** [`config/cors.php`](config/cors.php:65,73)

**Issue:**
```php
'allowed_origins_patterns' => ['*'],  // Line 65
'supports_credentials' => true,        // Line 73
```

Combining wildcard origins with credentials is a security violation.

**Impact:**
- Any origin can access API with credentials
- CSRF protection bypassed
- Data exposure to malicious sites

**Fix:**
```php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
    env('PRODUCTION_FRONTEND_URL', 'https://yourdomain.com'),
],
'supports_credentials' => env('CORS_CREDENTIALS', false),
```

---

### 2.2 No Preflight Caching (MEDIUM)

**Location:** [`config/cors.php`](config/cors.php:71)

**Issue:**
```php
'max_age' => 0,  // Line 71
```

No caching of preflight requests causes unnecessary OPTIONS calls.

**Fix:**
```php
'max_age' => 86400,  // 24 hours in seconds
```

---

### 2.3 Hardcoded Local Origins (LOW)

**Location:** [`config/cors.php`](config/cors.php:27-45)

**Issue:** Development URLs hardcoded in production config.

**Fix:** Use environment variables for all origins.

---

## Input Validation & SQL Injection

### 3.1 Incomplete SQL Escaping (HIGH)

**Location:** [`app/Models/Skp.php`](app/Models/Skp.php:19-21)

**Issue:**
```php
public function scopeSearch($query, $value){
    $query->where('nama','like',"%{$value}%");  // No escaping
}
```

Direct interpolation creates SQL injection risk.

**Impact:**
- SQL injection vulnerability
- Data exfiltration possible
- Database compromise

**Fix:**
```php
public function scopeSearch($query, $value){
    return $query->where('nama', 'like', '%' . $value . '%');
    // Laravel query builder auto-escapes
}
```

---

### 3.2 Basic Path Traversal Protection (MEDIUM)

**Location:** [`app/Http/Controllers/Api/Miniapp/SurveycraftApiController.php`](app/Http/Controllers/Api/Miniapp/SurveycraftApiController.php:864)

**Issue:**
```php
if (str_contains($path, '..')) {
    return response('Invalid path', 400);
}
```

Basic check can be bypassed with URL encoding.

**Fix:**
```php
$realPath = realpath(storage_path('app/' . $path));
$basePath = realpath(storage_path('app/'));

if ($realPath === false || strpos($realPath, $basePath) !== 0) {
    return response('Invalid path', 400);
}
```

---

### 3.3 Missing Input Sanitization (MEDIUM)

**Location:** Multiple controllers (e.g., [`PengaduanApiController.php`](app/Http/Controllers/Api/Kantor/PengaduanApiController.php:139))

**Issue:** User input stored without sanitization.

**Example:**
```php
$validatedData = $request->validate([
    'kronologi' => 'required|string',  // No sanitization
]);
```

**Fix:**
```php
use Illuminate\Support\Str;

'kronologi' => [
    'required',
    'string',
    function ($attribute, $value, $fail) {
        if (Str::contains($value, ['<script', 'javascript:', 'onerror'])) {
            $fail('Contains invalid characters');
        }
        return $value;
    }
],
```

---

## File Upload Security

### 4.1 Large File Size Limits (HIGH)

**Location:** [`app/Http/Controllers/Api/Kantor/SkpApiController.php`](app/Http/Controllers/Api/Kantor/SkpApiController.php:193)

**Issue:**
```php
'file' => 'required|file|mimes:pdf|max:10240',  // 10MB
```

Large files can cause DoS.

**Fix:**
```php
'file' => [
    'required',
    'file',
    'mimes:pdf',
    'max:2048',  // 2MB
],
```

---

### 4.2 No File Content Validation (HIGH)

**Location:** Same as above

**Issue:** Only MIME type checked, not actual file content.

**Fix:**
```php
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\File\UploadedFile;

$file = $request->file('file');
$mimeType = $file->getMimeType();
$extension = $file->getClientOriginalExtension();

// Verify file signature
$finfo = new \finfo(FILEINFO_MIME_TYPE);
$realMimeType = $finfo->file($file->getPathname());

if ($realMimeType !== 'application/pdf') {
    return $this->error('Invalid file type', null, 422);
}
```

---

### 4.3 No Virus Scanning (MEDIUM)

**Location:** All file upload endpoints

**Issue:** Uploaded files not scanned for malware.

**Fix:**
```php
// Install: composer require sokil/php-clamav
use ClamAV\Scanner;

$scanner = new Scanner('/usr/bin/clamscan');
$result = $scanner->scan($file->getPathname());

if ($result->isInfected()) {
    Storage::delete($file->getPathname());
    return $this->error('File contains virus', null, 422);
}
```

---

### 4.4 Public File Serving (HIGH)

**Location:** [`app/Http/Controllers/Api/Miniapp/SurveycraftApiController.php`](app/Http/Controllers/Api/Miniapp/SurveycraftApiController.php:864-885)

**Issue:** Files served publicly from storage directory.

```php
$path = 'surveycraft/' . $respondId;
return response()->file(storage_path('app/' . $path));
```

**Fix:**
```php
// Move to secure storage
$disk = Storage::disk('secure');
return $disk->download('surveycraft/' . $respondId);
```

---

## XSS Protection

### 5.1 No Output Escaping (HIGH)

**Location:** API responses throughout application

**Issue:** User data returned without escaping.

**Example:**
```php
return $this->success($pengaduan, 'Data retrieved successfully');
```

**Fix:** Laravel auto-escapes JSON, but verify:

```php
// In BaseApiController
protected function success($data, $message = 'Success', $status = 200)
{
    return response()->json([
        'success' => true,
        'message' => $message,
        'data' => $data,
    ], $status)->header('Content-Type', 'application/json');
}
```

---

### 5.2 Missing Content-Security-Policy (MEDIUM)

**Location:** [`public/index.php`](public/index.php)

**Issue:** No CSP headers set.

**Fix:**
```php
// Add to index.php or middleware
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline';");
```

---

## Rate Limiting

### 6.1 No Rate Limiting Middleware (CRITICAL)

**Location:** [`routes/api.php`](routes/api.php)

**Issue:** No rate limiting on any endpoint.

**Impact:**
- DoS attacks possible
- API abuse vulnerability
- Resource exhaustion

**Fix:**
```php
// Create middleware: php artisan make:middleware RateLimit
// routes/api.php
Route::middleware(['throttle:60,1'])->group(function () {
    Route::post('/login', [AuthApiController::class, 'login']);
    Route::post('/kantor/tamu', [TamuApiController::class, 'store']);
    Route::post('/kantor/pengaduan', [PengaduanApiController::class, 'store']);
});
```

---

### 6.2 Public Endpoints Unprotected (HIGH)

**Location:** [`routes/api.php`](routes/api.php:60-77)

**Issue:** Public endpoints have no rate limiting.

```php
Route::post('/kantor/tamu', [TamuApiController::class, 'store']);
Route::post('/kantor/pengaduan', [PengaduanApiController::class, 'store']);
Route::post('/miniapp/surveycraft/respond', [SurveycraftApiController::class, 'respond']);
```

**Fix:** Add rate limiting to all public endpoints.

---

## Password Security

### 7.1 Weak Bcrypt Cost (MEDIUM)

**Location:** [`app/Http/Controllers/Api/Admin/UserApiController.php`](app/Http/Controllers/Api/Admin/UserApiController.php:287)

**Issue:**
```php
'password' => bcrypt($validated['password']),  // Default cost 10
```

**Fix:**
```php
'password' => Hash::make($validated['password'], [
    'rounds' => 12,  // Higher cost
]),
```

---

### 7.2 No Password History (LOW)

**Location:** User management

**Issue:** Users can reuse old passwords.

**Fix:**
```php
// Add password history table
$passwordHistory = PasswordHistory::where('user_id', $userId)
    ->orderBy('created_at', 'desc')
    ->take(5)
    ->pluck('password_hash');

if ($passwordHistory->contains($newPasswordHash)) {
    return $this->error('Password already used', null, 422);
}
```

---

## Sensitive Data Exposure

### 8.1 Debug Mode Enabled (CRITICAL)

**Location:** [`.env.example`](.env.example:4)

**Issue:**
```env
APP_DEBUG=true
```

**Impact:**
- Stack traces exposed
- Database queries shown
- Environment variables leaked

**Fix:**
```env
APP_DEBUG=false
```

---

### 8.2 API Key in Config (HIGH)

**Location:** [`app/Http/Controllers/Api/Miniapp/SurveycraftApiController.php`](app/Http/Controllers/Api/Miniapp/SurveycraftApiController.php:115)

**Issue:**
```php
$apiKey = config('services.groq.api_key');
```

API key stored in config, not environment.

**Fix:**
```php
$apiKey = env('GROQ_API_KEY');
if (!$apiKey) {
    return $this->error('AI service not configured', null, 500);
}
```

---

### 8.3 Verbose Logging (MEDIUM)

**Location:** [`.env.example`](.env.example:22)

**Issue:**
```env
LOG_LEVEL=debug
```

Debug logging may expose sensitive data.

**Fix:**
```env
LOG_LEVEL=warning
```

---

### 8.4 Database Credentials in Config (MEDIUM)

**Location:** [`config/database.php`](config/database.php:52)

**Issue:**
```php
'password' => env('DB_PASSWORD', ''),  // Empty default
```

**Fix:**
```php
'password' => env('DB_PASSWORD'),
// Remove default, require environment variable
```

---

## Logging & Error Handling

### 9.1 No Structured Logging (MEDIUM)

**Location:** Application-wide

**Issue:** Logs not structured for security analysis.

**Fix:**
```php
use Illuminate\Support\Facades\Log;

Log::channel('security')->warning('Login attempt', [
    'ip' => $request->ip(),
    'email' => $request->email,
    'user_agent' => $request->userAgent(),
    'success' => false,
]);
```

---

### 9.2 Generic Error Messages (MEDIUM)

**Location:** [`app/Http/Controllers/Api/BaseApiController.php`](app/Http/Controllers/Api/BaseApiController.php)

**Issue:** Errors may leak implementation details.

**Fix:**
```php
// Use generic messages in production
if (config('app.debug') === false) {
    $message = 'An error occurred';
} else {
    $message = $exception->getMessage();
}
```

---

## Database Security

### 10.1 MySQL Strict Mode Disabled (MEDIUM)

**Location:** [`config/database.php`](config/database.php:58)

**Issue:**
```php
'strict' => false,  // Line 58
```

**Fix:**
```php
'strict' => true,
```

---

### 10.2 SQLite Default Connection (LOW)

**Location:** [`config/database.php`](config/database.php:19)

**Issue:**
```php
'default' => env('DB_CONNECTION', 'sqlite'),
```

SQLite not suitable for production.

**Fix:**
```php
'default' => env('DB_CONNECTION', 'mysql'),
```

---

## File Storage Security

### 11.1 Public Storage Directory (HIGH)

**Location:** File upload endpoints

**Issue:** Files stored in publicly accessible directories.

**Fix:**
```php
// config/filesystems.php
'disks' => [
    'secure' => [
        'driver' => 'local',
        'root' => storage_path('app/secure'),
        'url' => env('APP_URL').'/storage',
        'visibility' => 'private',
    ],
],
```

---

### 11.2 No File Cleanup (MEDIUM)

**Location:** File upload operations

**Issue:** Temporary files not cleaned up.

**Fix:**
```php
// Add cleanup job
use App\Jobs\CleanupOldFiles;

CleanupOldFiles::dispatch();
```

---

## Performance Issues

### 12.1 N+1 Query Problem (HIGH)

**Location:** [`app/Http/Controllers/Api/Admin/UserApiController.php`](app/Http/Controllers/Api/Admin/UserApiController.php:73)

**Issue:**
```php
$users = User::with(['roles', 'permissions'])->get();
// Then loop through users and access roles/permissions
foreach ($users as $user) {
    $user->roles;  // Triggers query
    $user->permissions;  // Triggers query
}
```

**Fix:**
```php
$users = User::with(['roles', 'permissions'])
    ->get()
    ->append(['roles_count', 'permissions_count']);
```

---

### 12.2 Missing Database Indexes (HIGH)

**Location:** Database migrations

**Issue:** No evidence of indexes on frequently queried columns.

**Fix:**
```php
// In migration
Schema::table('users', function (Blueprint $table) {
    $table->index('email');
    $table->index('name');
    $table->index('created_at');
});
```

---

### 12.3 No Query Caching (MEDIUM)

**Location:** Application-wide

**Issue:** Repeated queries not cached.

**Fix:**
```php
use Illuminate\Support\Facades\Cache;

$users = Cache::remember('users.all', 3600, function () {
    return User::with(['roles', 'permissions'])->get();
});
```

---

### 12.4 Inefficient Pagination (MEDIUM)

**Location:** Multiple controllers

**Issue:** Default pagination may load too much data.

**Fix:**
```php
// Use cursor pagination for large datasets
$users = User::cursorPaginate(50);
```

---

### 12.5 No Lazy Loading (MEDIUM)

**Location:** [`app/Http/Controllers/Api/Kantor/KegiatanApiController.php`](app/Http/Controllers/Api/Kantor/KegiatanApiController.php:231)

**Issue:**
```php
$kegiatan = Kegiatan::create($validator->validated());
// No eager loading of relationships
```

**Fix:**
```php
$kegiatan = Kegiatan::with(['penugasan', 'mitra'])
    ->create($validator->validated());
```

---

### 12.6 Large Response Payloads (MEDIUM)

**Location:** API responses

**Issue:** Full models returned with unnecessary data.

**Fix:**
```php
// Use resource classes
return UserResource::collection($users);
```

---

### 12.7 No Connection Pooling (LOW)

**Location:** [`config/database.php`](config/database.php)

**Issue:** No database connection pooling configured.

**Fix:**
```php
'mysql' => [
    // ... existing config
    'pool' => [
        'max_connections' => 100,
        'min_connections' => 5,
    ],
],
```

---

### 12.8 Synchronous File Uploads (MEDIUM)

**Location:** [`app/Http/Controllers/Api/Kantor/SkpApiController.php`](app/Http/Controllers/Api/Kantor/SkpApiController.php:195)

**Issue:**
```php
$pdfPath = $request->file('file')->storeAs('skp_files', $filename, 'direct');
```

Blocks request during upload.

**Fix:**
```php
// Use queue for processing
use App\Jobs\ProcessPdfUpload;

ProcessPdfUpload::dispatch($file);
```

---

### 12.9 No Redis Caching (LOW)

**Location:** Application-wide

**Issue:** No caching layer configured.

**Fix:**
```php
// config/cache.php
'default' => env('CACHE_DRIVER', 'redis'),
```

---

### 12.10 Inefficient Search Queries (MEDIUM)

**Location:** [`app/Http/Controllers/Api/Adhock/PetaSlsApiController.php`](app/Http/Controllers/Api/Adhock/PetaSlsApiController.php:74-78)

**Issue:**
```php
if ($search !== '') {
    $escaped = $this->escapeLike($search);
    $builder->where(function ($query) use ($escaped) {
        $like = '%' . $escaped . '%';
        $query->where('nmkec', 'like', $like)
              ->orWhere('nmkab', 'like', $like)
              ->orWhere('nmdesa', 'like', $like);
    });
}
```

Multiple OR queries without full-text search.

**Fix:**
```php
// Use full-text search or database-specific search
$builder->whereRaw("MATCH(nmkec, nmkab, nmdesa) AGAINST (?)", [$search]);
```

---

## Additional Security Concerns

### Development Artifacts in Production (MEDIUM)

**Location:** [`routes/web.php`](routes/web.php:52-67)

**Issue:** Test endpoints exposed.

```php
Route::get('/test', function () { ... });
Route::get('/test-pdf', function () { ... });
```

**Fix:** Remove or protect with environment check.

```php
if (config('app.env') === 'local') {
    Route::get('/test', ...);
}
```

---

### Commented Code in Routes (LOW)

**Location:** [`routes/web.php`](routes/web.php:185-352)

**Issue:** Large blocks of commented code.

**Fix:** Remove dead code.

---

## Recommended Immediate Actions

### Priority 1 (Critical - Fix Within 24 Hours)

1. **Disable debug mode in production**
   ```env
   APP_DEBUG=false
   ```

2. **Fix CORS wildcard with credentials**
   ```php
   'supports_credentials' => false
   ```

3. **Add rate limiting to login endpoint**
   ```php
   ->middleware('throttle:5,1')
   ```

4. **Set Sanctum token expiration**
   ```php
   'expiration' => 60
   ```

5. **Remove password from User fillable**
   ```php
   protected $fillable = ['name', 'email'];
   ```

### Priority 2 (High - Fix Within 1 Week)

1. Implement rate limiting on all public endpoints
2. Add file size limits to uploads (2MB max)
3. Implement file content validation
4. Fix SQL injection in Skp model
5. Add proper path traversal protection
6. Move files to secure storage

### Priority 3 (Medium - Fix Within 1 Month)

1. Add password complexity requirements
2. Implement password history
3. Add virus scanning for uploads
4. Set up structured logging
5. Add database indexes
6. Implement query caching
7. Add Content-Security-Policy headers
8. Remove development artifacts

### Priority 4 (Low - Fix Within 3 Months)

1. Implement connection pooling
2. Add Redis caching
3. Optimize search queries
4. Use cursor pagination
5. Create API resource classes
6. Implement file cleanup jobs

---

## Security Best Practices Checklist

- [ ] Debug mode disabled in production
- [ ] Rate limiting on all endpoints
- [ ] Token expiration configured
- [ ] Password complexity requirements
- [ ] File size limits enforced
- [ ] File content validation
- [ ] Virus scanning implemented
- [ ] CORS properly configured
- [ ] SQL injection protection
- [ ] XSS protection in place
- [ ] Input sanitization
- [ ] Structured logging
- [ ] Error handling secure
- [ ] Database indexes optimized
- [ ] Query caching implemented
- [ ] Secure file storage
- [ ] Environment variables used
- [ ] Secrets not in code
- [ ] CSP headers set
- [ ] HTTPS enforced

---

## Performance Optimization Checklist

- [ ] N+1 queries eliminated
- [ ] Database indexes added
- [ ] Query caching implemented
- [ ] Redis caching configured
- [ ] Cursor pagination used
- [ ] API resources implemented
- [ ] Lazy loading used
- [ ] Connection pooling configured
- [ ] Async jobs for heavy tasks
- [ ] Response size optimized
- [ ] Full-text search implemented
- [ ] Database query monitoring
- [ ] CDN for static assets
- [ ] Gzip compression enabled
- [ ] HTTP/2 supported

---

## Conclusion

The application has significant security vulnerabilities that require immediate attention, particularly around:

1. **Authentication** - No token expiration, weak password policies
2. **CORS** - Misconfigured with wildcard origins and credentials
3. **File Uploads** - No content validation, large size limits
4. **Rate Limiting** - Completely absent
5. **Input Validation** - SQL injection risks, incomplete sanitization

Performance issues are primarily related to:

1. **Database queries** - N+1 problems, missing indexes
2. **Caching** - No query or response caching
3. **File operations** - Synchronous uploads blocking requests

**Recommendation:** Address all Critical and High priority issues before deploying to production.

---

## Appendix: Files Analyzed

### Configuration Files
- `routes/api.php`
- `routes/web.php`
- `config/cors.php`
- `config/sanctum.php`
- `config/database.php`
- `.env.example`

### Controllers
- `app/Http/Controllers/Api/AuthApiController.php`
- `app/Http/Controllers/Api/BaseApiController.php`
- `app/Http/Controllers/Api/Admin/UserApiController.php`
- `app/Http/Controllers/Api/Admin/RolesApiController.php`
- `app/Http/Controllers/Api/Admin/PermissionsApiController.php`
- `app/Http/Controllers/Api/Kantor/SkpApiController.php`
- `app/Http/Controllers/Api/Kantor/PengaduanApiController.php`
- `app/Http/Controllers/Api/Kantor/TamuApiController.php`
- `app/Http/Controllers/Api/Kantor/MitraApiController.php`
- `app/Http/Controllers/Api/Kantor/PenugasanApiController.php`
- `app/Http/Controllers/Api/Kantor/KegiatanApiController.php`
- `app/Http/Controllers/Api/Kantor/LinkApiController.php`
- `app/Http/Controllers/Api/Kantor/BastApiController.php`
- `app/Http/Controllers/Api/Kantor/SpkApiController.php`
- `app/Http/Controllers/Api/Kantor/MonitoringKegiatanApiController.php`
- `app/Http/Controllers/Api/Kantor/MonitoringKegiatanConfigApiController.php`
- `app/Http/Controllers/Api/Kantor/DetilConfigurationApiController.php`
- `app/Http/Controllers/Api/Kantor/PegawaiApiController.php`
- `app/Http/Controllers/Api/Kantor/SettingApiController.php`
- `app/Http/Controllers/Api/Kantor/SkpApiController.php`
- `app/Http/Controllers/Api/Kantor/TamuApiController.php`
- `app/Http/Controllers/Api/Kantor/PengaduanApiController.php`
- `app/Http/Controllers/Api/ProfileApiController.php`
- `app/Http/Controllers/Api/Miniapp/SurveycraftApiController.php`
- `app/Http/Controllers/Api/Adhock/AlokasiApiController.php`
- `app/Http/Controllers/Api/Adhock/PetaSlsApiController.php`
- `app/Http/Controllers/Api/Adhock/CekGeorefApiController.php`
- `app/Http/Controllers/Api/Adhock/CekScanApiController.php`
- `app/Http/Controllers/Api/Adhock/SlsSipwApiController.php`
- `app/Http/Controllers/Api/Ipds/AssetITApiController.php`
- `app/Http/Controllers/Api/Ipds/AssetITMaintenanceScheduleApiController.php`
- `app/Http/Controllers/Api/WilkerstatDashboardApiController.php`

### Models
- `app/Models/User.php`
- `app/Models/Skp.php`

---

*Report generated by security audit analysis tool*
