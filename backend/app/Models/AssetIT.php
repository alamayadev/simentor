<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AssetIT extends Model
{
    use HasFactory;

    protected $table = 'asset_it';

    protected $fillable = [
        'id',
        'kode_asset',
        'type',
        'category',
        'brand',
        'model',
        'serial_number',
        'name',
        'license_key',
        'device',
        'ip_address',
        'location',
        'status',
        'assigned_to',
        'purchase_date',
        'warranty_expiry',
        'expiry_date',
        'delivery_date',
        'purchase_value',
    ];

    protected $dates = [
        'purchase_date',
        'warranty_expiry',
        'expiry_date',
        'delivery_date',
    ];

    protected $appends = ['value_depreciation'];

    public function getValueDepreciationAttribute(): ?float
    {
        if (! $this->purchase_value || ! $this->purchase_date) {
            return null;
        }

        $purchaseDate = Carbon::parse($this->purchase_date);
        $now = Carbon::now();

        $usefulLifeYears = 7;
        if ($this->expiry_date) {
            $expiryDate = Carbon::parse($this->expiry_date);
            $diffInYears = $purchaseDate->diffInYears($expiryDate);
            if ($diffInYears > 0) {
                $usefulLifeYears = $diffInYears;
            }
        }

        $yearsElapsed = $purchaseDate->diffInYears($now);
        $annualDepreciation = $this->purchase_value / $usefulLifeYears;
        $accumulatedDepreciation = $annualDepreciation * $yearsElapsed;
        $bookValue = $this->purchase_value - $accumulatedDepreciation;

        return max(0, round($bookValue, 2));
    }

    public function maintenanceSchedules(): HasMany
    {
        return $this->hasMany(AssetITMaintenanceSchedule::class, 'asset_id');
    }
}
