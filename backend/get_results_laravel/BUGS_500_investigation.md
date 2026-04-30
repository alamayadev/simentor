# 🐛 500 Server Error Investigation Report

Generated: 2026-04-18 21:30

## Summary

Two GET endpoints return **500 Internal Server Error** due to code bugs:

| # | Endpoint | Controller Method | Root Cause |
|---|----------|-------------------|------------|
| 1 | `GET /api/kantor/kegiatan/monitoring/detil-configuration?monitoring_kegiatan_config_id=6` | `MonitoringKegiatanApiController::getDetilConfiguration()` | **Wrong column name** - queries non-existent column `monitoring_kegiatan_config_id` on `detil_configurations` table |
| 2 | `GET /api/kantor/kegiatan/monitoring/detil-configurations/available-fields?foreign_key_value=88&foreign_key_type=kegiatan_id` | `DetilConfigurationApiController::getAvailableFields()` | **TypeError** - passes array of column names to `whereIn()` instead of single column name |

---

## Bug 1: Wrong Column in `getDetilConfiguration()`

### Location
- **File**: `app/Services/MonitoringKegiatanService.php` line 192
- **Method**: `getDetilConfiguration(int $config_id)`

### Code (Buggy)
```php
public function getDetilConfiguration(int $config_id): Collection
{
    return DetilConfiguration::where('monitoring_kegiatan_config_id', $config_id)->get();
}
```

### Root Cause
The `detil_configurations` table does **NOT** have a `monitoring_kegiatan_config_id` column.

**Table structure** (`detil_configurations`):
| Column | Type |
|--------|------|
| id | bigint unsigned |
| name | varchar(255) |
| related_table | varchar(255) |
| foreign_key | varchar(255) |
| field | json |
| is_active | tinyint(1) |
| created_at | timestamp |
| updated_at | timestamp |

The relationship between `monitoring_kegiatan_config` and `detil_configurations` is **many-to-many** via a pivot table `detil_configuration_monitoring_kegiatan`, NOT a direct foreign key.

### SQL Error
```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'monitoring_kegiatan_config_id' in 'where clause'
```

### Verified Working Alternative
Using the Eloquent relationship works correctly:
```php
MonitoringKegiatanConfig::with('detilConfigurations')->find(6);
// Returns config #6 with its associated detilConfigurations via pivot
```

### Suggested Fix
```php
public function getDetilConfiguration(int $config_id): Collection
{
    $config = MonitoringKegiatanConfig::with('detilConfigurations')->find($config_id);
    
    if (!$config) {
        return collect([]);
    }
    
    return $config->detilConfigurations;
}
```

---

## Bug 2: TypeError in `getAvailableFields()` → `getByForeignKey()`

### Location
- **File**: `app/Models/DetilConfiguration.php` line 48
- **Method**: `getByForeignKey($foreignKeyValue, $foreignKeyType)`

### Code (Buggy - lines 47-53)
```php
foreach ($tablesToCheck as $table => $checks) {
    $foreignKey = array_column($checks, 'foreign_key'); // Returns ARRAY of column names
    $foreignKeyValues = array_fill(0, count($foreignKey), $foreignKeyValue);
    
    $existingRecords = DB::table($table)
        ->whereIn($foreignKey, $foreignKeyValues)  // BUG: $foreignKey is array, expects string
        ->pluck($foreignKey)
        ->toArray();
    ...
}
```

### Root Cause
`array_column($checks, 'foreign_key')` returns an **array** of foreign key column names (e.g., `['kegiatan_id']`).

This array is passed as the **first argument** to `whereIn()`, which expects a **string** column name. When there are multiple configurations for the same related table with different foreign keys, the array has multiple elements.

Laravel's `Grammar::wrap()` receives an array instead of a string, causing:
```
TypeError: stripos(): Argument #1 ($haystack) must be of type string, array given
```

### Suggested Fix
The batch logic should iterate per-foreign-key or handle properly:
```php
foreach ($tablesToCheck as $table => $checks) {
    foreach ($checks as $check) {
        $foreignKey = $check['foreign_key']; // String column name
        $exists = DB::table($table)
            ->where($foreignKey, $foreignKeyValue)
            ->exists();
        
        if ($exists) {
            $results[] = $check['config'];
        }
    }
}
```

Or if batching is needed for performance:
```php
foreach ($tablesToCheck as $table => $checks) {
    // Group checks by foreign_key column
    $grouped = [];
    foreach ($checks as $check) {
        $grouped[$check['foreign_key']][] = $check;
    }
    
    foreach ($grouped as $foreignKey => $groupChecks) {
        $existingRecords = DB::table($table)
            ->where($foreignKey, $foreignKeyValue)
            ->pluck($foreignKey)
            ->toArray();
        
        foreach ($groupChecks as $check) {
            if (in_array($foreignKeyValue, $existingRecords)) {
                $results[] = $check['config'];
            }
        }
    }
}
```

---

## Related Pivot Tables Found

The database has these pivot tables for the relationship:
- `detil_configuration_monitoring_kegiatan` - many-to-many pivot
- `monitoring_kegiatan_detil_configuration` - another pivot variant

## Files to Fix

1. `app/Services/MonitoringKegiatanService.php` - line 192 (Bug 1)
2. `app/Models/DetilConfiguration.php` - lines 47-61 (Bug 2)