<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Alokasi;

class CekScan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'cek_scans';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'kec',
        'desa',
        'filename',
        'fullpath',
        'created_time',
        'jenis',
        'lokasi',
        'kodename',
        'kode',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'created_time' => 'datetime',
    ];

    /**
     * Get the alokasi associated with this cek scan.
     */
    public function alokasi()
    {
        return $this->hasOne(Alokasi::class, 'idsls', 'kode')
                    ->select(['idsls', 'alokasi_scan']);
    }
}
