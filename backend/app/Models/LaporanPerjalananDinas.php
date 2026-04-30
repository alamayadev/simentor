<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanPerjalananDinas extends Model
{
    use HasFactory;

    protected $table = 'laporan_perjalanan_dinas';

    protected $guarded = ['id'];

    public function details(): HasMany
    {
        return $this->hasMany(LaporanPerjalananDinasDetail::class, 'laporan_perjalanan_dinas_id');
    }

    public function dokumentasi(): HasMany
    {
        return $this->hasMany(LaporanPerjalananDinasDokumentasi::class, 'laporan_perjalanan_dinas_id');
    }
}
