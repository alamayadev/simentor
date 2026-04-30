<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Penugasan extends Model
{
    use HasFactory;

    protected $table = 'penugasan';

    protected $fillable = ['kegiatan_id','jabatan_tugas', 'mitra_id', 'pegawai_id', 'volume', 'nilai', 'bln_bayar', 'created_by','no_bast','tgl_bast', 'no_sk', 'tgl_sk', 'jangka_waktu_mulai', 'jangka_waktu_selesai','nilai_pulsa'];

    protected $casts = [
        'bln_bayar' => 'datetime:Y-m-d',
        'tgl_bast'=> 'datetime:Y-m-d',
        'tgl_sk'=> 'datetime:Y-m-d',
        'jangka_waktu_mulai'=> 'datetime:Y-m-d',
        'jangka_waktu_selesai'=> 'datetime:Y-m-d'
    ];

    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class);
    }
    public function mitra(): BelongsTo
    {
        return $this->belongsTo(Mitra::class);
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function pegawai(): BelongsTo
    {
        return $this->belongsTo(Pegawai::class);
    }
}
