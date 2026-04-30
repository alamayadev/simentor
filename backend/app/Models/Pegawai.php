<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Pegawai extends Model
{
    use HasFactory;

    protected $table = 'profil_pegawai';

    protected $guarded = ['id'];

    // protected $casts = [
    //     'bln_bayar' => 'datetime:Y-m-d',
    //     'tgl_bast'=> 'datetime:Y-m-d',
    //     'tgl_sk'=> 'datetime:Y-m-d',
    //     'jangka_waktu_mulai'=> 'datetime:Y-m-d',
    //     'jangka_waktu_selesai'=> 'datetime:Y-m-d'
    // ];

    public function surtug(): HasOne
    {
        return $this->hasOne(SurtugDetil::class,'pegawai_id');
    }
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
