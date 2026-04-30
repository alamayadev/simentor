<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UuTambah extends Model
{
    protected $table = 'uu_tambahan';

    protected $fillable = [
        'jenis_surat',
        'surat_id',
        'item',
    ];
}
