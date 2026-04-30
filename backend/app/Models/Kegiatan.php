<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Enums\FungsiType;
use App\Enums\JenisKegiatanType;
use App\Casts\SatuanTypeCast;
use App\Models\MonitoringKegiatan;
use App\Models\Penugasan;

class Kegiatan extends Model
{
    use HasFactory;

    protected $table = 'kegiatan';

    protected $guarded = ['id'];

    protected $casts = [
        'tgl_mulai' => 'datetime:Y-m-d',
        'tgl_selesai' => 'datetime:Y-m-d',
        'fungsi' => FungsiType::class,
        'jenis_kegiatan' => JenisKegiatanType::class,
        'satuan' => SatuanTypeCast::class,
        'jml_penugasan' => 'integer',
        'penugasan_sum_volume' => 'integer',
        'penugasan_sum_nilai' => 'integer',
    ];

    public function penugasan(): HasMany
    {
        return $this->hasMany(Penugasan::class);
    }

    /**
     * Get all monitoring kegiatans for this kegiatan.
     * This relationship is based on the kegiatan_id foreign key in the MonitoringKegiatan model.
     */
    public function monitoringKegiatans()
    {
        return $this->hasMany(MonitoringKegiatan::class, 'kegiatan_id');
    }
}
