<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SurtugDetil extends Model
{
    use HasFactory;

    protected $table = 'surat_tugas_detil';
    protected $guarded = ['id'];

    protected $casts = [
        'tgl_mulai' => 'datetime:Y-m-d',
        'isOrganik' => 'boolean',
        'sppd' => 'boolean',
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
        return $this->belongsTo(SuratTugas::class,'surtug_id');
    }
}
