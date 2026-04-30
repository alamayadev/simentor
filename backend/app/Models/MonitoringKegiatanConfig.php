<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonitoringKegiatanConfig extends Model
{
    protected $table = 'monitoring_kegiatan_config';

    protected $fillable = [
        'fungsi',
        'kegiatan_id',
        'detil_configurations',
    ];

    protected $casts = [
        'detil_configurations' => 'array',
    ];

    /**
     * Get all monitoring kegiatans that use this configuration.
     */
    public function monitoringKegiatans(): HasMany
    {
        return $this->hasMany(MonitoringKegiatan::class, 'monitoring_kegiatan_config_id');
    }

    /**
     * Get the associated detil configurations.
     */
    public function detilConfigurations()
    {
        return $this->belongsToMany(
            \App\Models\DetilConfiguration::class,
            'detil_configuration_monitoring_kegiatan',
            'monitoring_kegiatan_config_id',
            'detil_configuration_id'
        );
    }

    /**
     * Get the associated kegiatan.
     */
    public function kegiatan()
    {
        return $this->belongsTo(\App\Models\Kegiatan::class, 'kegiatan_id');
    }
}
