<?php

namespace App\Models\Pivot;

use Illuminate\Database\Eloquent\Relations\Pivot;

class MonitoringKegiatanConfigDetilConfiguration extends Pivot
{
    protected $table = 'detil_configuration_monitoring_kegiatan';

    protected $fillable = [
        'monitoring_kegiatan_config_id',
        'detil_configuration_id',
    ];

    public $timestamps = true;
}
