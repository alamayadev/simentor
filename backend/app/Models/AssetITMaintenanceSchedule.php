<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AssetITMaintenanceSchedule extends Model
{
    use HasFactory;
    protected $table = 'asset_it_maintenance_schedule';

    protected $fillable = [
        'asset_id',
        'next_maintenance',
        'responsible_team',
    ];

    protected $dates = [
        'next_maintenance',
    ];

    public function asset(): BelongsTo
    {
        return $this->belongsTo(AssetIT::class, 'asset_id', 'id');
    }
}
