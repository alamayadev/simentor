<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Alokasi;

class CekGeoref extends Model
{
    use HasFactory;

    protected $table = 'cek_georefs';

    protected $fillable = [
        'kec', 'desa', 'filename', 'fullpath', 'created_time', 'jenis', 'lokasi', 'kodename', 'kode',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'created_time' => 'datetime',
    ];

    /**
     * Get the alokasi associated with this cek georef.
     */
    public function alokasi()
    {
        return $this->hasOne(Alokasi::class, 'idsls', 'kode')
                    ->select(['idsls', 'alokasi_georef']);
    }
}
