<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tamu extends Model
{
    use HasFactory;

    protected $table = 'tamu';

    protected $guarded = ['id'];

    protected $casts = [
        'tgl_kunjungan' => 'datetime:Y-m-d',
    ];
}
