<?php

use App\Http\Controllers\Api\Admin\PermissionsApiController;
use App\Http\Controllers\Api\Admin\RolesApiController;
use App\Http\Controllers\Api\Admin\UserApiController as AdminUserApiController;
use App\Http\Controllers\Api\AuthApiController;
use App\Http\Controllers\Api\DirektoriUsahaController;
use App\Http\Controllers\Api\EnumsApiController;
use App\Http\Controllers\Api\GeminiProxyController;
use App\Http\Controllers\Api\Ipds\AssetITApiController;
use App\Http\Controllers\Api\Ipds\AssetITMaintenanceScheduleApiController;
use App\Http\Controllers\Api\Ipds\RawDataApiController;
use App\Http\Controllers\Api\Ipds\TiketApiController;
use App\Http\Controllers\Api\Kantor\BastApiController;
use App\Http\Controllers\Api\Kantor\DetilConfigurationApiController;
use App\Http\Controllers\Api\Kantor\Dipa\DipaBudgetApiController;
use App\Http\Controllers\Api\Kantor\HolidayApiController;
use App\Http\Controllers\Api\Kantor\KegiatanApiController;
use App\Http\Controllers\Api\Kantor\LaporanPerjalananDinasApiController;
use App\Http\Controllers\Api\Kantor\LinkApiController;
use App\Http\Controllers\Api\Kantor\MitraApiController;
use App\Http\Controllers\Api\Kantor\MonitoringKegiatanApiController;
use App\Http\Controllers\Api\Kantor\MonitoringKegiatanConfigApiController;
use App\Http\Controllers\Api\Kantor\NomorSurat\PermintaanApiController;
use App\Http\Controllers\Api\Kantor\NomorSurat\SuratKeluarApiController;
use App\Http\Controllers\Api\Kantor\NomorSurat\SuratKeputusanApiController;
use App\Http\Controllers\Api\Kantor\NomorSurat\SuratTugasApiController;
use App\Http\Controllers\Api\Kantor\NomorSurat\SurtugDetilApiController;
use App\Http\Controllers\Api\Kantor\NomorSurat\SkDetilApiController;
use App\Http\Controllers\Api\Kantor\NotulensiApiController;
use App\Http\Controllers\Api\Kantor\PegawaiApiController;
use App\Http\Controllers\Api\Kantor\PengaduanApiController;
use App\Http\Controllers\Api\Kantor\PenugasanApiController;
use App\Http\Controllers\Api\Kantor\SettingApiController;
use App\Http\Controllers\Api\Kantor\SkpApiController;
use App\Http\Controllers\Api\Kantor\SpkApiController;
use App\Http\Controllers\Api\Kantor\TamuApiController;
use App\Http\Controllers\Api\Kantor\UuApiController;
use App\Http\Controllers\Api\Kantor\UuTambahApiController;
use App\Http\Controllers\Api\LandingApiController;
use App\Http\Controllers\Api\Meta\WilayahApiController;
use App\Http\Controllers\Api\Miniapp\SurveyController;
use App\Http\Controllers\Api\Miniapp\SurveycraftApiController;
use App\Http\Controllers\Api\Miniapp\Surveys\Result\SurveyResultController;
use App\Http\Controllers\Api\PdfApiController;
use App\Http\Controllers\Api\ProfileApiController;
use App\Http\Controllers\Pdf\LaporanPerjalananDinasPdfController;
use App\Http\Controllers\SuratKeluarController;
use App\Http\Controllers\SuratKeputusanController;
use App\Http\Controllers\SuratTugasMitraController;
use App\Http\Controllers\SuratTugasOrganikController;
use Illuminate\Support\Facades\Route;

// Removed TestDomPdfController import since it's now in web routes

// Apply CORS middleware to all API routes
Route::middleware(['cors'])->group(function () {
    // Public API endpoints (no authentication required)
    Route::post('/login', [AuthApiController::class, 'login'])
        ->middleware('throttle:5,1'); // 5 attempts per minute
    Route::post('/auth/login', [AuthApiController::class, 'login'])
        ->middleware('throttle:5,1'); // Backward-compatible alias

    // Public Landing Page API
    Route::get('/landing', [LandingApiController::class, 'index']);

    // Wrap all public POST routes with rate limiting
    Route::middleware(['throttle:60,1'])->group(function () {
        // Public Tamu endpoint
        Route::post('/kantor/tamu', [TamuApiController::class, 'store']);

        // Public Pengaduan endpoint
        Route::post('/kantor/pengaduan', [PengaduanApiController::class, 'store']);
    });

    // Meta Public Routes
    Route::prefix('meta')->group(function () {
        Route::get('/wilayah/kecamatan', [WilayahApiController::class, 'kecamatan']);
    });

    // Miniapp Public Routes
    Route::prefix('miniapp')->group(function () {
        // Survey API endpoints (Public for now)
        Route::apiResource('survey', SurveyController::class);
        Route::apiResource('survey-results', SurveyResultController::class);

        // Public Surveycraft file fetch with CORS
        Route::get('/surveycraft/file/{path}', [SurveycraftApiController::class, 'file'])->where('path', '.*');
        Route::options('/surveycraft/file/{path}', function () {
            return response('', 204, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, OPTIONS',
                'Access-Control-Allow-Headers' => '*',
            ]);
        })->where('path', '.*');

        // Public Surveycraft share endpoint - add rate limiting
        Route::post('/surveycraft/respond', [SurveycraftApiController::class, 'respond_store'])
            ->middleware('throttle:60,1');
        Route::get('/surveycraft/share/{surveycraft}', [SurveycraftApiController::class, 'share']);

        // Temporary test endpoint for AI generation (no auth required for testing)
        Route::post('/surveycraft/test-generate-ai', [SurveycraftApiController::class, 'generateWithAI'])
            ->middleware('throttle:10,1'); // SECURITY FIX: Add rate limiting (10 attempts per minute)
    });

    // Protected API endpoints (authentication required)
    Route::middleware(['auth:sanctum'])->group(function () {
        // Logout endpoint
        Route::post('/logout', [AuthApiController::class, 'logout']);

        // Profile API endpoints
        Route::prefix('profile')->group(function () {
            Route::get('/', [ProfileApiController::class, 'show']);
            Route::put('/', [ProfileApiController::class, 'update']);
            Route::put('/password', [ProfileApiController::class, 'updatePassword']);
            Route::get('/pegawai', [ProfileApiController::class, 'showPegawai']);
            Route::put('/pegawai', [ProfileApiController::class, 'updatePegawai']);
        });

        // PDF Generation endpoints
        Route::get('/pdf/spk/{id}', [PdfApiController::class, 'generateSPK']);
        Route::get('/pdf/bast/{id}', [PdfApiController::class, 'generateBAST']);
        Route::post('/spk/pdf/bulk-spks', [PdfApiController::class, 'bulkGenerateSPK']);
        Route::post('/spk/pdf/bulk-basts', [PdfApiController::class, 'bulkGenerateBAST']);

        // Gemini AI Proxy
        Route::post('/ai/generate', [GeminiProxyController::class, 'proxy']);
    });

    // DOCX generation endpoints (parity with web routes)
    // TEMPORARY: Auth disabled for debugging - files should download directly
    Route::get('/surat/surat-tugas/generate-docx/satu/organik/{id}', [SuratTugasOrganikController::class, 'satu']);
    Route::get('/surat/surat-tugas/generate-docx/gab/mitra/{id}', [SuratTugasMitraController::class, 'generateDocx']);
    Route::get('/surat/surat-tugas/generate-docx/gab/organik/{id}', [SuratTugasMitraController::class, 'generateOrganikDocx']);
    Route::get('/surat/surat-tugas/generate-docx/organik/{id}', [SuratTugasOrganikController::class, 'index']);
    Route::get('/surat/surat-keluar/generate-docx/{id}', [SuratKeluarController::class, 'index']);
    Route::get('/surat/surat-keputusan/kpa/generate-docx/mitra/{sk}', [SuratKeputusanController::class, 'generateSkKpaMitra']);
    Route::get('/surat/surat-keputusan/kpa/generate-docx/mitra/manual/{sk}', [SuratKeputusanController::class, 'generateSkKpaMitraManual']);
    Route::get('/surat/surat-keputusan/kepala/generate-docx/organik/{sk}', [SuratKeputusanController::class, 'generateSkKepalaOrganik']);

    Route::middleware(['auth:sanctum'])->group(function () {

        Route::prefix('admin')->group(function () {
            Route::get('/metas/tree', [\App\Http\Controllers\Api\Admin\MetaApiController::class, 'tree']);
            Route::apiResource('/metas', \App\Http\Controllers\Api\Admin\MetaApiController::class);

            Route::get('/roles/permission-options', [RolesApiController::class, 'permissionOptions']);
            Route::get('/roles', [RolesApiController::class, 'index']);
            Route::get('/roles/{id}', [RolesApiController::class, 'show']);
            Route::post('/roles', [RolesApiController::class, 'store']);
            Route::put('/roles/{id}', [RolesApiController::class, 'update']);
            Route::put('/roles/{id}/permissions', [RolesApiController::class, 'updatePermissions']);
            Route::delete('/roles/{id}', [RolesApiController::class, 'destroy']);

            // Admin Permissions API (super-admin only)
            Route::get('/permissions', [PermissionsApiController::class, 'index']);
            Route::get('/permissions/grouped-prefixes', [PermissionsApiController::class, 'groupedByPrefix']);
            Route::get('/permissions/form-options', [PermissionsApiController::class, 'listApiControllers']);
            Route::get('/permissions/list-api-controllers', [PermissionsApiController::class, 'listApiControllers']);
            Route::get('/permissions/{id}', [PermissionsApiController::class, 'show']);
            Route::post('/permissions', [PermissionsApiController::class, 'store']);
            Route::post('/permissions/bulk-create', [PermissionsApiController::class, 'bulkCreate']);
            Route::put('/permissions/{id}', [PermissionsApiController::class, 'update']);
            Route::delete('/permissions/{id}', [PermissionsApiController::class, 'destroy']);

            // Admin Users API (super-admin only)
            Route::get('/users/organik', [AdminUserApiController::class, 'organik']);
            Route::get('/users/mitra', [AdminUserApiController::class, 'mitra']);
            Route::get('/users', [AdminUserApiController::class, 'index']);
            Route::post('/users', [AdminUserApiController::class, 'store']);
            Route::get('/users/{id}', [AdminUserApiController::class, 'show']);
            Route::put('/users/{id}', [AdminUserApiController::class, 'update']);
            Route::put('/users/{id}/permissions', [AdminUserApiController::class, 'updatePermissions']);
            Route::delete('/users/{id}', [AdminUserApiController::class, 'destroy']);
            Route::post('/users/bulk-update-roles', [AdminUserApiController::class, 'bulkUpdateRoles']);
        });

        // Kantor API endpoints - all office management related APIs
        Route::prefix('kantor')->group(function () {
            // Pegawai API
            Route::get('/pegawai', [PegawaiApiController::class, 'index']);
            Route::post('/pegawai', [PegawaiApiController::class, 'store']);
            Route::get('/pegawai/filters', [PegawaiApiController::class, 'filters']);
            Route::get('/pegawai/form-options', [PegawaiApiController::class, 'formOptions']);
            // Removed individual routes: getPangkatList and getJabatanList
            Route::get('/pegawai/jabatan-pangkat-list', [PegawaiApiController::class, 'getJabatanPangkatList']);
            Route::get('/pegawai/{id}', [PegawaiApiController::class, 'show']);
            Route::put('/pegawai/{id}', [PegawaiApiController::class, 'update']);
            Route::delete('/pegawai/{id}', [PegawaiApiController::class, 'destroy']);

            // Setting API
            Route::get('/settings', [SettingApiController::class, 'index']);
            Route::post('/settings', [SettingApiController::class, 'store']);
            Route::get('/settings/grup-list', [SettingApiController::class, 'getGrupList']);
            Route::get('/settings/officers', [SettingApiController::class, 'officers']);
            Route::get('/settings/key/{key}', [SettingApiController::class, 'getByKey']);
            Route::get('/settings/{id}', [SettingApiController::class, 'show']);
            Route::put('/settings/{id}', [SettingApiController::class, 'update']);
            Route::delete('/settings/{id}', [SettingApiController::class, 'destroy']);

            // Mitra API
            Route::get('/mitra', [MitraApiController::class, 'index']);
            Route::get('/mitra/filters', [MitraApiController::class, 'filters']);
            Route::get('/mitra/statistics', [MitraApiController::class, 'statistics']);
            Route::get('/mitra/penugasan-options', [MitraApiController::class, 'penugasanOptions']);
            Route::post('/mitra/penugasan', [MitraApiController::class, 'store']);

            // Direktori Usaha API - Business directory with geocoding
            Route::get('/direktori-usaha', [DirektoriUsahaController::class, 'index']);
            Route::get('/direktori-usaha/rekap-user', [DirektoriUsahaController::class, 'rekapUser']);
            Route::get('/direktori-usaha/progres', [DirektoriUsahaController::class, 'progres']);
            Route::get('/direktori-usaha/filters', [DirektoriUsahaController::class, 'filters']);
            Route::get('/direktori-usaha/desa-options', [DirektoriUsahaController::class, 'desaOptions']);
            Route::get('/direktori-usaha/no-gc-no-loc', [DirektoriUsahaController::class, 'noGcNoLoc']);
            Route::get('/direktori-usaha/invalid', [DirektoriUsahaController::class, 'invalid']);
            Route::get('/direktori-usaha/siap-kirim', [DirektoriUsahaController::class, 'siapKirim']);
            Route::get('/direktori-usaha/siap-kirim/csv', [DirektoriUsahaController::class, 'csvSiapKirim']);
            Route::get('/direktori-usaha/ganda', [DirektoriUsahaController::class, 'ganda']);
            Route::get('/direktori-usaha/export/csv', [DirektoriUsahaController::class, 'csvExport']);
            Route::get('/direktori-usaha/csv-update', [DirektoriUsahaController::class, 'csvUpdate']);
            Route::get('/direktori-usaha/map-desa', [DirektoriUsahaController::class, 'mapDesa']);
            Route::get('/direktori-usaha/{id}', [DirektoriUsahaController::class, 'show']);
            Route::put('/direktori-usaha/{id}', [DirektoriUsahaController::class, 'update']);

            Route::prefix('kegiatan/monitoring')->group(function () {
                Route::get('/monitoring-kegiatan-config/kegiatan-options', [MonitoringKegiatanConfigApiController::class, 'kegiatanOptions']);

                // Detil Configuration API endpoints (nested under kegiatan/monitoring)
                Route::prefix('detil-configurations')->group(function () {
                    Route::get('/available-fields', [DetilConfigurationApiController::class, 'getAvailableFields']);
                    Route::get('/', [DetilConfigurationApiController::class, 'index']);
                    Route::get('/{id}', [DetilConfigurationApiController::class, 'show']);
                    Route::post('/', [DetilConfigurationApiController::class, 'storeConfiguration']);
                    Route::put('/{id}', [DetilConfigurationApiController::class, 'update']);
                    Route::delete('/{id}', [DetilConfigurationApiController::class, 'destroy']);
                });

                Route::get('/kegiatan-options', [MonitoringKegiatanApiController::class, 'kegiatanOptions']);
                Route::get('/all-kegiatan-options', [MonitoringKegiatanApiController::class, 'allKegiatanOptions']);
                Route::get('/filters-options', [MonitoringKegiatanApiController::class, 'filtersOptions']);
                Route::get('/petugas-options', [MonitoringKegiatanApiController::class, 'petugasOptions']);
                Route::get('/pengawas-options', [MonitoringKegiatanApiController::class, 'pengawasOptions']);
                Route::get('/supervisor-options', [MonitoringKegiatanApiController::class, 'supervisorOptions']);
                Route::get('/sls-options', [MonitoringKegiatanApiController::class, 'slsOptions']);
                Route::get('/blok-options', [MonitoringKegiatanApiController::class, 'blokOptions']);
                Route::get('/kec-options', [MonitoringKegiatanApiController::class, 'kecOptions']);
                Route::get('/desa-options', [MonitoringKegiatanApiController::class, 'desaOptions']);
                Route::get('/detil-configuration', [MonitoringKegiatanApiController::class, 'getDetilConfiguration']);

                // Monitoring Kegiatan Config API endpoints
                Route::get('/monitoring-kegiatan-config/{id}/available-detil-configs', [MonitoringKegiatanConfigApiController::class, 'getAvailableDetilConfigurations']);
                Route::apiResource('/monitoring-kegiatan-config', MonitoringKegiatanConfigApiController::class)->where(['monitoring_kegiatan_config' => '[0-9]+']);

                Route::get('/', [MonitoringKegiatanApiController::class, 'index']);
                Route::get('/download', [MonitoringKegiatanApiController::class, 'download']);
                Route::get('/{id}', [MonitoringKegiatanApiController::class, 'show']);
                Route::post('/', [MonitoringKegiatanApiController::class, 'store']);
                Route::put('/{id}', [MonitoringKegiatanApiController::class, 'update']);
                Route::delete('/{id}', [MonitoringKegiatanApiController::class, 'destroy']);
            });

            // Kegiatan API
            Route::get('/kegiatan/filter-list', [KegiatanApiController::class, 'getFilterList']);
            Route::get('/kegiatan/calendar', [KegiatanApiController::class, 'calendar']);
            Route::get('/kegiatan/form-options', [KegiatanApiController::class, 'formOptions']);
            Route::get('/kegiatan/by-year', [KegiatanApiController::class, 'kegiatanByYear']);
            Route::get('/kegiatan/statistics', [KegiatanApiController::class, 'statistics']);
            Route::get('/kegiatan', [KegiatanApiController::class, 'index']);
            Route::get('/kegiatan/{id}', [KegiatanApiController::class, 'show']);
            Route::post('/kegiatan', [KegiatanApiController::class, 'store']);
            Route::put('/kegiatan/{id}', [KegiatanApiController::class, 'update']);
            Route::delete('/kegiatan/{id}', [KegiatanApiController::class, 'destroy']);

            // Penugasan API
            // Penugasan dropdown endpoints (must be before general penugasan routes)
            Route::get('/penugasan/mitra-dropdown', [PenugasanApiController::class, 'mitraDropdown']);
            Route::get('/penugasan/mitra-options', [PenugasanApiController::class, 'mitraOptions']);
            Route::get('/penugasan/filters', [PenugasanApiController::class, 'filters']);
            Route::get('/penugasan/form-options', [PenugasanApiController::class, 'formOptions']);
            Route::get('/penugasan/kegiatan-options', [PenugasanApiController::class, 'kegiatanOptions']);
            Route::get('/penugasan/export', [PenugasanApiController::class, 'export']);

            Route::get('/penugasan', [PenugasanApiController::class, 'index']);
            Route::get('/penugasan/{id}', [PenugasanApiController::class, 'show']);
            Route::post('/penugasan', [PenugasanApiController::class, 'store']);
            Route::post('/penugasan/{id}/insert', [PenugasanApiController::class, 'insert']);
            Route::put('/penugasan/{id}', [PenugasanApiController::class, 'update']);
            Route::delete('/penugasan/{id}', [PenugasanApiController::class, 'destroy']);

            // UU API endpoints
            Route::get('/uu', [UuApiController::class, 'index']);
            Route::post('/uu', [UuApiController::class, 'store']);
            Route::get('/uu/{id}', [UuApiController::class, 'show']);
            Route::put('/uu/{id}', [UuApiController::class, 'update']);
            Route::delete('/uu/{id}', [UuApiController::class, 'destroy']);

            // UU Tambahan API endpoints
            Route::get('/uu-tambahan', [UuTambahApiController::class, 'index']);
            Route::post('/uu-tambahan', [UuTambahApiController::class, 'store']);
            Route::get('/uu-tambahan/{id}', [UuTambahApiController::class, 'show']);
            Route::put('/uu-tambahan/{id}', [UuTambahApiController::class, 'update']);
            Route::delete('/uu-tambahan/{id}', [UuTambahApiController::class, 'destroy']);

            // Bast API endpoints
            Route::get('/bast', [BastApiController::class, 'index']);
            Route::get('/bast/available-months', [BastApiController::class, 'getAvailableMonths']);
            Route::get('/bast/{id}', [BastApiController::class, 'show']);
            Route::put('/bast/{id}', [BastApiController::class, 'update']);

            // Nomor Surat API endpoints
            Route::prefix('surat')->group(function () {
                // Permintaan API
                Route::get('/permintaan', [PermintaanApiController::class, 'index']);
                Route::get('/permintaan/dates', [PermintaanApiController::class, 'getDates']);
                Route::get('/permintaan/years', [PermintaanApiController::class, 'getYears']);
                Route::get('/permintaan/klasifikasi', [PermintaanApiController::class, 'getKlasifikasi']);
                Route::get('/permintaan/form-options', [PermintaanApiController::class, 'formOptions']);
                Route::get('/permintaan/{id}', [PermintaanApiController::class, 'show']);
                Route::post('/permintaan', [PermintaanApiController::class, 'store']);
                Route::post('/permintaan/sisip', [PermintaanApiController::class, 'sisip']);
                Route::put('/permintaan/{id}', [PermintaanApiController::class, 'update']);
                Route::delete('/permintaan/{id}', [PermintaanApiController::class, 'destroy']);
                Route::post('/permintaan/bulk-update-status', [PermintaanApiController::class, 'bulkUpdateStatus']);

                // Surat Keluar API
                Route::get('/surat-keluar', [SuratKeluarApiController::class, 'index']);
                Route::get('/surat-keluar/dates', [SuratKeluarApiController::class, 'getDates']);
                Route::get('/surat-keluar/years', [SuratKeluarApiController::class, 'getYears']);
                Route::get('/surat-keluar/form-options', [SuratKeluarApiController::class, 'formOptions']);
                Route::get('/surat-keluar/{id}', [SuratKeluarApiController::class, 'show']);
                Route::post('/surat-keluar', [SuratKeluarApiController::class, 'store']);
                Route::post('/surat-keluar/sisip', [SuratKeluarApiController::class, 'insert']);
                Route::put('/surat-keluar/{id}', [SuratKeluarApiController::class, 'update']);
                Route::delete('/surat-keluar/{id}', [SuratKeluarApiController::class, 'destroy']);

                // Surat Keputusan API
                Route::get('/surat-keputusan', [SuratKeputusanApiController::class, 'index']);
                Route::get('/surat-keputusan/dates', [SuratKeputusanApiController::class, 'getDates']);
                Route::get('/surat-keputusan/years', [SuratKeputusanApiController::class, 'getYears']);
                Route::get('/surat-keputusan/form-options', [SuratKeputusanApiController::class, 'formOptions']);
                Route::get('/surat-keputusan/{id}', [SuratKeputusanApiController::class, 'show']);
                Route::post('/surat-keputusan', [SuratKeputusanApiController::class, 'store']);
                Route::post('/surat-keputusan/sisip', [SuratKeputusanApiController::class, 'sisip']);
                Route::put('/surat-keputusan/{id}', [SuratKeputusanApiController::class, 'update']);
                Route::delete('/surat-keputusan/{id}', [SuratKeputusanApiController::class, 'destroy']);

                // Surat Tugas API
                Route::get('/surat-tugas', [SuratTugasApiController::class, 'index']);
                Route::get('/surat-tugas/dates', [SuratTugasApiController::class, 'getDates']);
                Route::get('/surat-tugas/years', [SuratTugasApiController::class, 'getYears']);
                Route::get('/surat-tugas/klasifikasi', [SuratTugasApiController::class, 'getKlasifikasi']);
                Route::get('/surat-tugas/form-options', [SuratTugasApiController::class, 'formOptions']);
                Route::get('/surat-tugas/{id}', [SuratTugasApiController::class, 'show']);
                Route::post('/surat-tugas', [SuratTugasApiController::class, 'store']);
                Route::post('/surat-tugas/sisip', [SuratTugasApiController::class, 'insert']);
                Route::put('/surat-tugas/{id}', [SuratTugasApiController::class, 'update']);
                Route::delete('/surat-tugas/{id}', [SuratTugasApiController::class, 'destroy']);

                // Surtug Detil API
                Route::get('/surtug-detil', [SurtugDetilApiController::class, 'index']);
                Route::post('/surtug-detil', [SurtugDetilApiController::class, 'store']);
                Route::post('/surtug-detil/insert', [SurtugDetilApiController::class, 'insert']);
                Route::get('/surtug-detil/kegiatan-options', [SurtugDetilApiController::class, 'kegiatanOptions']);
                Route::get('/surtug-detil/mitra-penugasan-options', [SurtugDetilApiController::class, 'mitraPenugasanOptions']);
                Route::get('/surtug-detil/pegawai-options', [SurtugDetilApiController::class, 'pegawaiOptions']);
                Route::get('/surtug-detil/mitra-options', [SurtugDetilApiController::class, 'mitraOptions']);
                Route::post('/surtug-detil/bulk-mitra', [SurtugDetilApiController::class, 'bulkMitra']);
                Route::get('/surtug-detil/surtug/{surtug_id}', [SurtugDetilApiController::class, 'getBySurtugId']);
                Route::get('/surtug-detil/{id}', [SurtugDetilApiController::class, 'show'])->whereNumber('id');
                Route::put('/surtug-detil/{id}', [SurtugDetilApiController::class, 'update'])->whereNumber('id');
                Route::delete('/surtug-detil/{id}', [SurtugDetilApiController::class, 'destroy'])->whereNumber('id');

                // SK Detil API
                Route::get('/sk-detil', [SkDetilApiController::class, 'index']);
                Route::post('/sk-detil', [SkDetilApiController::class, 'store']);
                Route::get('/sk-detil/kegiatan-options', [SkDetilApiController::class, 'kegiatanOptions']);
                Route::get('/sk-detil/mitra-penugasan-options', [SkDetilApiController::class, 'mitraPenugasanOptions']);
                Route::get('/sk-detil/pegawai-options', [SkDetilApiController::class, 'pegawaiOptions']);
                Route::get('/sk-detil/mitra-options', [SkDetilApiController::class, 'mitraOptions']);
                Route::post('/sk-detil/bulk-mitra', [SkDetilApiController::class, 'bulkMitra']);
                Route::get('/sk-detil/sk/{sk_id}', [SkDetilApiController::class, 'getBySkId']);
                Route::get('/sk-detil/{id}', [SkDetilApiController::class, 'show'])->whereNumber('id');
                Route::put('/sk-detil/{id}', [SkDetilApiController::class, 'update'])->whereNumber('id');
                Route::delete('/sk-detil/{id}', [SkDetilApiController::class, 'destroy'])->whereNumber('id');
            });

            // SKP API endpoints
            Route::get('/skp/dashboard', [SkpApiController::class, 'dashboard']);
            Route::get('/skp/stats', [SkpApiController::class, 'index']);
            Route::get('/skp/stats2', [SkpApiController::class, 'stat2']);
            Route::get('/skp/list', [SkpApiController::class, 'list']);
            Route::get('/skp/{id}', [SkpApiController::class, 'show']);
            Route::post('/skp', [SkpApiController::class, 'store']);
            Route::put('/skp/{id}', [SkpApiController::class, 'update']);
            Route::delete('/skp/{id}', [SkpApiController::class, 'destroy']);

            // SPK API endpoints
            Route::get('/spk', [SpkApiController::class, 'index']);
            Route::get('/spk/{mitra_id}/{bln_bayar}', [SpkApiController::class, 'show']);
            Route::put('/spk/{mitra_id}/{bln_bayar}', [SpkApiController::class, 'update']);
            Route::put('/spk/bulk-update', [SpkApiController::class, 'bulkUpdateSpk']);
            Route::put('/spk/bulk-update-bast', [SpkApiController::class, 'bulkUpdateBast']);
            Route::get('/spk/monitoring', [SpkApiController::class, 'monitoring']);

            // Link API endpoints
            Route::get('/links', [LinkApiController::class, 'index']);
            Route::post('/links', [LinkApiController::class, 'store']);
            Route::get('/links/{id}', [LinkApiController::class, 'show']);
            Route::put('/links/{id}', [LinkApiController::class, 'update']);
            Route::delete('/links/{id}', [LinkApiController::class, 'destroy']);

            // Laporan Perjalanan Dinas API endpoints
            Route::prefix('laporan-perjalanan-dinas')->group(function () {
                // CRUD
                Route::get('/', [LaporanPerjalananDinasApiController::class, 'index']);
                Route::post('/', [LaporanPerjalananDinasApiController::class, 'store']);
                Route::get('/{id}', [LaporanPerjalananDinasApiController::class, 'show']);
                Route::put('/{id}', [LaporanPerjalananDinasApiController::class, 'update']);
                Route::delete('/{id}', [LaporanPerjalananDinasApiController::class, 'destroy']);

                // Details
                Route::post('/{id}/details', [LaporanPerjalananDinasApiController::class, 'storeDetail']);
                Route::put('/{id}/details/{detailId}', [LaporanPerjalananDinasApiController::class, 'updateDetail']);
                Route::delete('/{id}/details/{detailId}', [LaporanPerjalananDinasApiController::class, 'destroyDetail']);

                // Dokumentasi
                Route::post('/{id}/dokumentasi', [LaporanPerjalananDinasApiController::class, 'uploadDokumentasi']);
                Route::post('/{id}/dokumentasi/{docId}', [LaporanPerjalananDinasApiController::class, 'updateDokumentasi']);
                Route::delete('/{id}/dokumentasi/{docId}', [LaporanPerjalananDinasApiController::class, 'deleteDokumentasi']);

                // PDF Generation
                Route::get('/{id}/pdf', [LaporanPerjalananDinasPdfController::class, 'generate']);
            });

            // Notulensi API endpoints
            Route::get('/notulensi/form-options', [NotulensiApiController::class, 'formOptions']);
            Route::apiResource('/notulensi', NotulensiApiController::class);
            Route::get('/notulensi/{id}/pdf', [NotulensiApiController::class, 'generatePdf']);
            Route::get('/notulensi/{id}/docx', [NotulensiApiController::class, 'generateDocx']);

            // Tamu API endpoints (protected)
            Route::get('/tamu', [TamuApiController::class, 'index']);
            Route::get('/tamu/{id}', [TamuApiController::class, 'show']);
            Route::put('/tamu/{id}', [TamuApiController::class, 'update']);
            Route::delete('/tamu/{id}', [TamuApiController::class, 'destroy']);

            // Pengaduan API endpoints (protected, except store)
            Route::get('/pengaduan', [PengaduanApiController::class, 'index']);
            Route::get('/pengaduan/{id}', [PengaduanApiController::class, 'show']);
            Route::put('/pengaduan/{id}', [PengaduanApiController::class, 'update']);
            Route::delete('/pengaduan/{id}', [PengaduanApiController::class, 'destroy']);

            // DIPA Budget API endpoints
            Route::prefix('dipa')->group(function () {
                Route::get('/monitoring', [DipaBudgetApiController::class, 'getMonitoring']);
                Route::get('/monitoring/summary', [DipaBudgetApiController::class, 'getMonitoringSummary']);
                Route::get('/picker', [DipaBudgetApiController::class, 'getPicker']);
                Route::get('/usage/sync-fa-summary', [DipaBudgetApiController::class, 'getSyncFaSummary']);
                Route::post('/usage', [DipaBudgetApiController::class, 'recordUsage']);
                Route::post('/usage/sync-fa', [DipaBudgetApiController::class, 'syncFaUsage'])
                    ->middleware('role:Kasub Umum,sanctum');
                Route::post('/import', [DipaBudgetApiController::class, 'importDipaFile'])
                    ->middleware('role:Kasub Umum,sanctum');
                Route::get('/history', [DipaBudgetApiController::class, 'getHistory']);
                Route::get('/orphans', [DipaBudgetApiController::class, 'getOrphans']);
                Route::post('/map', [DipaBudgetApiController::class, 'saveMapping'])
                    ->middleware('role:Kasub Umum|katim,sanctum');
                Route::get('/files', [DipaBudgetApiController::class, 'getFiles']);
                Route::get('/files/{id}/integrity', [DipaBudgetApiController::class, 'auditImportedFileIntegrity']);
                Route::post('/files/{id}/reimport', [DipaBudgetApiController::class, 'reimportFile'])
                    ->middleware('role:Kasub Umum,sanctum');
                Route::put('/usage/{id}', [DipaBudgetApiController::class, 'updateUsageAmount']);
                Route::delete('/usage/{id}', [DipaBudgetApiController::class, 'deleteUsage']);
                Route::post('/plan', [DipaBudgetApiController::class, 'recordPlan'])
                    ->middleware('role:Kasub Umum|katim,sanctum');
                Route::get('/plan', [DipaBudgetApiController::class, 'getPlans']);
                Route::get('/plan/export', [DipaBudgetApiController::class, 'exportPlans']);
                Route::put('/plan/{id}', [DipaBudgetApiController::class, 'updatePlan'])
                    ->middleware('role:Kasub Umum|katim,sanctum');
                Route::delete('/plan/{id}', [DipaBudgetApiController::class, 'deletePlan'])
                    ->middleware('role:Kasub Umum|katim,sanctum');
                Route::get('/reconciliation', [DipaBudgetApiController::class, 'getReconciliation']);
                Route::post('/import-sakti', [DipaBudgetApiController::class, 'importSakti'])
                    ->middleware('role:Kasub Umum,sanctum');
                Route::get('/dependencies/views', [DipaBudgetApiController::class, 'verifyDependencies']);
            });

            // Holiday API endpoints
            Route::get('/holidays', [HolidayApiController::class, 'index']);
            Route::get('/holidays/{id}', [HolidayApiController::class, 'show']);
            Route::post('/holidays', [HolidayApiController::class, 'store']);
            Route::put('/holidays/{id}', [HolidayApiController::class, 'update']);
            Route::delete('/holidays/{id}', [HolidayApiController::class, 'destroy']);

        });

        // Enums API
        Route::get('/enums', [EnumsApiController::class, 'index']);
        Route::get('/enums/fungsi-types', [EnumsApiController::class, 'fungsiTypes']);
        Route::get('/enums/jabatan-tugas-types', [EnumsApiController::class, 'jabatanTugasTypes']);
        Route::get('/enums/jenis-kegiatan-types', [EnumsApiController::class, 'jenisKegiatanTypes']);
        Route::get('/enums/satuan-types', [EnumsApiController::class, 'satuanTypes']);
        Route::get('/enums/pangkat-types', [EnumsApiController::class, 'pangkatTypes']);
        Route::get('/enums/golongan-types', [EnumsApiController::class, 'golonganTypes']);
        Route::get('/enums/jabatan-types', [EnumsApiController::class, 'jabatanTypes']); // Added this line

        // IPDS routes
        Route::prefix('ipds')->group(function () {
            // IPDS Tikets API (authenticated endpoints)
            // IMPORTANT: Specific routes must come BEFORE parameterized routes
            Route::get('/tikets/keluhan-options', [TiketApiController::class, 'keluhanOptions']);
            /**
             * Get tiket statistics including total count and status breakdown.
             *
             * @group IPDS - Tiket
             * @authenticated
             */
            Route::get('/tikets/statistics', [TiketApiController::class, 'statistics']);
            Route::get('/tikets', [TiketApiController::class, 'index']);
            Route::post('/tikets', [TiketApiController::class, 'store']);
            Route::get('/tikets/{id}', [TiketApiController::class, 'show']);
            Route::put('/tikets/{id}', [TiketApiController::class, 'update']);
            Route::delete('/tikets/{id}', [TiketApiController::class, 'destroy']);

            // IPDS Assets API (authenticated endpoints)
            /**
             * Get unique filter values for asset types and statuses.
             *
             * @group IPDS - Asset IT
             * @authenticated
             */
            Route::get('/assets/filters', [AssetITApiController::class, 'filters']);
            /**
             * Get asset statistics including total count and status breakdown.
             *
             * @group IPDS - Asset IT
             * @authenticated
             */
            Route::get('/assets/statistics', [AssetITApiController::class, 'statistics']);
            Route::apiResource('/assets', AssetITApiController::class);

            // IPDS Asset IT Maintenance Schedule API (authenticated endpoints)
            Route::apiResource('/asset-it-maintenance-schedule', AssetITMaintenanceScheduleApiController::class);

            // IPDS Raw Data API (authenticated endpoints)
            Route::get('/raw-datas', [RawDataApiController::class, 'index'])->middleware('permission:raw-data-view,sanctum');
            Route::post('/raw-datas', [RawDataApiController::class, 'store'])->middleware('permission:raw-data-create,sanctum');
            Route::get('/raw-datas/{id}', [RawDataApiController::class, 'show'])->middleware('permission:raw-data-view,sanctum');
            Route::put('/raw-datas/{id}', [RawDataApiController::class, 'update'])->middleware('permission:raw-data-update,sanctum');
            Route::delete('/raw-datas/{id}', [RawDataApiController::class, 'destroy'])->middleware('permission:raw-data-delete,sanctum');
        });

        // Miniapp API endpoints
        Route::prefix('miniapp')->group(function () {
            // Surveycraft API endpoints
            Route::get('/surveycraft', [SurveycraftApiController::class, 'index']);
            Route::post('/surveycraft', [SurveycraftApiController::class, 'store']);
            Route::post('/surveycraft/generate-ai', [SurveycraftApiController::class, 'generateWithAI']);
            Route::get('/surveycraft/options', [SurveycraftApiController::class, 'surveyOptions']);
            Route::get('/surveycraft/respond', [SurveycraftApiController::class, 'respond']);
            Route::get('/surveycraft/responds', [SurveycraftApiController::class, 'responds']);
            Route::get('/surveycraft/{surveycraft}', [SurveycraftApiController::class, 'show'])
                ->withoutMiddleware('auth:sanctum');
            Route::post('/surveycraft/{surveycraft}', [SurveycraftApiController::class, 'update']);
            Route::put('/surveycraft/{surveycraft}', [SurveycraftApiController::class, 'update']);
            Route::delete('/surveycraft/{surveycraft}', [SurveycraftApiController::class, 'destroy']);
        });

        // Monitoring Kegiatan API endpoints
    });
});
