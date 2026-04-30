<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class DetilConfiguration extends Model
{
    use HasFactory;

    protected $table = 'detil_configurations';

    protected $fillable = [
        'name', 'related_table', 'foreign_key', 'field', 'is_active'
    ];

    protected $casts = [
        'field' => 'array',
    ];

    /**
     * Get configurations based on foreign key
     */
    public static function getByForeignKey($foreignKeyValue, $foreignKeyType)
    {
        $results = [];

        // Get related table configurations
        $relatedConfigs = self::where('foreign_key', $foreignKeyType)
                             ->where('is_active', true)
                             ->get();

        // PERFORMANCE FIX: Batch existence checks to eliminate N+1 queries
        $tablesToCheck = [];
        foreach ($relatedConfigs as $config) {
            if ($config->related_table && $config->foreign_key) {
                $tablesToCheck[$config->related_table][] = [
                    'foreign_key' => $config->foreign_key,
                    'config' => $config
                ];
            }
        }

        // Check existence in batches (reduces queries from N to number of tables)
        foreach ($tablesToCheck as $table => $checks) {
            $foreignKey = array_column($checks, 'foreign_key');
            $foreignKeyValues = array_fill(0, count($foreignKey), $foreignKeyValue);
            
            $existingRecords = DB::table($table)
                ->whereIn($foreignKey, $foreignKeyValues)
                ->pluck($foreignKey)
                ->toArray();
            
            foreach ($checks as $check) {
                if (in_array($check['foreign_key'], $existingRecords)) {
                    $results[] = $check['config'];
                }
            }
        }

        // Get custom field configurations
        $customConfigs = self::whereNull('foreign_key')
                           ->where('is_active', true)
                           ->get();

        $results = array_merge($results, $customConfigs->toArray());

        return collect($results);
    }
}
