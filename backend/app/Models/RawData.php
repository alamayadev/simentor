<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RawData extends Model
{
    use HasFactory;

    protected $table = 'raw_datas';

    protected $fillable = [
        'type',
        'fungsi',
        'nama',
        'keterangan',
        'file',
    ];
}
