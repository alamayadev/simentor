# Performance Audit Upgrade Implementation Plan

> **For Claude:** REQUIRED SUB-SKILL: Use superpowers:executing-plans to implement this plan task-by-task.

**Goal:** Optimize critical backend endpoints (`Penugasan`, `SKP`) by fixing missing database indexes and implementing caching for heavy statistics/filter queries.

**Architecture:** Laravel Migration for schema changes; `Cache::remember` for optimized read operations.

**Tech Stack:** Laravel 11, MySQL, PHP 8.2+.

---

### Task 1: Database Indexes (Penugasan & SKP)

**Files:**
- Create: `database/migrations/2026_01_18_110000_add_audit_performance_indexes.php`

**Step 1: Create Migration File**

Run: `php artisan make:migration add_audit_performance_indexes --table=penugasan` (Conceptually, but we'll include the code below).

**Step 2: Write Migration Code**

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('penugasan', function (Blueprint $table) {
            // Check if indexes exist before adding
            $sm = Schema::getConnection()->getDoctrineSchemaManager();
            $indexes = $sm->listTableIndexes('penugasan');
            
            if (!array_key_exists('penugasan_kegiatan_id_index', $indexes)) {
                $table->index('kegiatan_id');
            }
            if (!array_key_exists('penugasan_mitra_id_index', $indexes)) {
                $table->index('mitra_id');
            }
            if (!array_key_exists('penugasan_bln_bayar_index', $indexes)) {
                $table->index('bln_bayar');
            }
            // Composite for common filter
            if (!array_key_exists('penugasan_kegiatan_bln_index', $indexes)) {
               $table->index(['kegiatan_id', 'bln_bayar'], 'penugasan_kegiatan_bln_index');
            }
        });

        Schema::table('skps', function (Blueprint $table) {
             // Composite index for stats query: where types = 'X' and tahun = 'Y' and bulan = 'Z'
             // Note: 'skps' is the table name (inferred from previous viewing, will verify)
             $table->index(['jenis', 'tahun', 'bulan'], 'skp_stats_index');
             $table->index(['jenis', 'tahun'], 'skp_annual_stats_index');
        });
    }

    public function down(): void
    {
        Schema::table('penugasan', function (Blueprint $table) {
            $table->dropIndex(['kegiatan_id']);
            $table->dropIndex(['mitra_id']);
            $table->dropIndex(['bln_bayar']);
            $table->dropIndex('penugasan_kegiatan_bln_index');
        });

        Schema::table('skps', function (Blueprint $table) {
            $table->dropIndex('skp_stats_index');
            $table->dropIndex('skp_annual_stats_index');
        });
    }
};
```

**Step 3: Run Migration**

Run: `php artisan migrate`
Expected: "Migrated: 2026_01_18_110000_add_audit_performance_indexes"

---

### Task 2: Cache Penugasan Filters

**Files:**
- Modify: `app/Http/Controllers/Api/Kantor/PenugasanApiController.php`

**Step 1: Modify `filters` method**

Refactor the `filters` method to wrap the heavy queries in `Cache::remember`.

```php
    public function filters()
    {
        /** @var User $authUser */
        $authUser = Auth::user();
        if (! $authUser) {
            return $this->error('Unauthorized', null, 401);
        }

        // PERFORMANCE OPTIMIZATION: Cache for 24 hours
        $data = Cache::remember('penugasan_filters_list', 86400, function () {
            // ... existing logic ...
            // Get bulan_bayar list ...
            // Get kegiatan list ...
            
            return [
                'blnBayarList' => $blnBayarList,
                'kegiatanList' => $kegiatanList,
            ];
        });

        return $this->success($data, 'Filters retrieved successfully');
    }
```
*Note: Make sure to verify the existing logic is copied correctly inside the closure.*

**Step 2: Add Cache Invalidation**

Modify `store`, `update`, `destroy`, `insert` methods in `PenugasanApiController` to call `$this->invalidatePenugasanCaches()`.

Add private method:
```php
    private function invalidatePenugasanCaches()
    {
        Cache::forget('penugasan_filters_list');
        // Add other related caches if any
    }
```

---

### Task 3: Optimize SKP Stats (Critical)

**Files:**
- Modify: `app/Http/Controllers/Api/Kantor/SkpApiController.php`

**Step 1: Refactor `index` method**

The `index` method calculates `skp_monthly_stats`, `skp_annual_setting_stats`, `skp_annual_determine_stats`, `skp_annual_evaluation_stats` on every request.

Wrap these calculations in `Cache::remember`.

```php
// In SkpApiController::index

$cacheKey = "skp_stats_{$selectedTahun}_{$selectedTahun2}_{$selectedBulan}";

$stats = Cache::remember($cacheKey, 3600, function () use ($selectedTahun, $selectedTahun2, $selectedBulan, $lastMonth) {
    // ... Move all heavy counting logic here ...
    // Calculate $skp_monthly_stats
    // Calculate $skp_annual_setting_stats
    // ...
    return [
       'skp_monthly_stats' => ...,
       'skp_annual_setting_stats' => ...,
       ...
    ];
});

// Merge cached stats with lightweight data (options)
return $this->success(array_merge([
    'tahun_options' => $tahun,
    'bulan_options' => $bulan,
    // ...
], $stats), 'Data retrieved successfully');
```

**Step 2: Add Cache Invalidation**

In `store`, `update`, `destroy` methods of `SkpApiController`, clear the stats cache. Since keys are dynamic (`filtered`), we might need to use tags (if Redis) or just clear a master version/time-based key if using file cache, OR just accept 1-hour stale data (which is acceptable for stats).

*Decision*: Since driver is `database` (no tags), we will use a short TTL (1 hour) or implement a simple "flush all skp stats" by appending a version timestamp to the key if possible. For simplicity in this iteration, we will rely on 1-hour TTL.

---

### Verification

1.  **Run Migrations**: `php artisan migrate`
2.  **Verify Endpoints**:
    -   `GET /api/kantor/penugasan/filters` -> Should work.
    -   `GET /api/kantor/skp` -> Should work.
