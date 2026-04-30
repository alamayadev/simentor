<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notulensi extends Model
{
    use HasFactory;

    protected $fillable = [
        'instansi',
        'judul',
        'kegiatan',
        'topik',
        'tanggal_rapat',
        'waktu_mulai',
        'waktu_selesai',
        'tempat',
        'jabatan_pimpinan_rapat',
        'pimpinan_id',
        'notulis_id',
        'nip_pimpinan',
        'nip_notulis',
        'peserta',
        'agenda',
        'resume',
        'tanya_jawab',
        'kategori',
    ];

    protected $casts = [
        'tanggal_rapat' => 'date',
        'peserta' => 'array',
        'kategori' => 'integer',
    ];

    /**
     * Relationship to the Chairman (Pimpinan)
     */
    public function pimpinan()
    {
        return $this->belongsTo(Pegawai::class, 'pimpinan_id');
    }

    /**
     * Relationship to the Secretary (Notulis)
     */
    public function notulis()
    {
        return $this->belongsTo(Pegawai::class, 'notulis_id');
    }
}
