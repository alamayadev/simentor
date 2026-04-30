<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkDetil extends Model
{
    protected $table = 'surat_sk_detil';

    protected $guarded = ['id'];

    protected $casts = [
        'detil' => 'array',
        'isOrganik' => 'boolean',
    ];
    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class,'pegawai_id');
    }
    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class,'mitra_id');
    }
    public function nomor(): BelongsTo
    {
        return $this->belongsTo(SkBast::class,'sk_id');
    }
    public function penugasan(): BelongsTo
    {
        return $this->belongsTo(Penugasan::class,'penugasan_id');
    }
}
