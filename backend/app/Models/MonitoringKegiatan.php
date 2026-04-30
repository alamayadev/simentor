<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\DetilConfiguration;
use App\Models\Kegiatan;
use App\Models\Kecamatan;
use App\Models\Desa;

class MonitoringKegiatan extends Model
{
    protected $table = 'monitoring_kegiatan';

    protected $fillable = [
        'fungsi', 'kegiatan_id', 'kec_id', 'desa_id',
        'kode_sampel', 'monitoring_kegiatan_config_id', 'detil_data'
    ];

    protected $casts = [
        'detil_data' => 'array',
    ];

    /**
     * Get the detil configurations through the monitoring config.
     */
    public function detilConfigurations()
    {
        return $this->monitoringConfig ? $this->monitoringConfig->detilConfigurations : collect();
    }

    /**
     * Validate all detil data based on the configurations
     */
    public function validateDetil()
    {
        // Get configurations through the monitoring config
        $configurations = $this->monitoringConfig->detilConfigurations;

        if ($configurations && $this->detil_data) {
            foreach ($configurations as $configuration) {
                $fieldDefinition = $configuration->field;
                $fieldName = $fieldDefinition['name'];

                // Check if this field exists in detil_data
                if (isset($this->detil_data[$fieldName])) {
                    $value = $this->detil_data[$fieldName];

                    // Check required field
                    if (isset($fieldDefinition['required']) && $fieldDefinition['required']) {
                        if (empty($value)) {
                            throw new \Exception("Field {$fieldName} is required");
                        }
                    }

                    // Validate data type if field is present
                    $this->validateFieldType($value, $fieldDefinition);
                }
            }
        }

        return true;
    }

    /**
     * Validate field type based on configuration
     */
    private function validateFieldType($value, $fieldDefinition)
    {
        $fieldName = $fieldDefinition['name'];

        if ($fieldDefinition['source'] === 'custom' && isset($fieldDefinition['type'])) {
            switch ($fieldDefinition['type']) {
                case 'number':
                    if (!is_numeric($value)) {
                        throw new \Exception("Field {$fieldName} must be a number");
                    }
                    break;
                case 'text':
                    if (!is_string($value)) {
                        throw new \Exception("Field {$fieldName} must be text");
                    }
                    break;
                case 'date':
                    if (strtotime($value) === false) {
                        throw new \Exception("Field {$fieldName} must be a valid date");
                    }
                    break;
                case 'enum':
                    if (!isset($fieldDefinition['options']) || !in_array($value, $fieldDefinition['options'])) {
                        $options = implode(', ', $fieldDefinition['options'] ?? []);
                        throw new \Exception("Field {$fieldName} must be one of: {$options}");
                    }
                    break;
            }
        }
        // For related table fields, we assume the type matches the source field
    }

    /**
     * Get the kegiatan that owns this monitoring kegiatan.
     * This relationship is based on the kegiatan_id foreign key which references
     * the id field in the Kegiatan model.
     */
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id');
    }
    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kec_id');
    }

    public function desa()
    {
        return $this->belongsTo(Desa::class, 'desa_id');
    }

    /**
     * Get the configuration template for this monitoring kegiatan.
     */
    public function monitoringConfig()
    {
        return $this->belongsTo(MonitoringKegiatanConfig::class, 'monitoring_kegiatan_config_id');
    }

    /**
     * Get the detil configurations directly associated with this monitoring kegiatan.
     * This is for the direct many-to-many relationship via pivot table.
     */
    public function directDetilConfigurations()
    {
        return $this->belongsToMany(DetilConfiguration::class, 'monitoring_kegiatan_detil_configuration');
    }
    
}
