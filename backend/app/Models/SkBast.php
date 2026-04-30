<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SkBast extends Model
{
    use HasFactory;
    protected $table = 'surat_sk_bast';

    protected $guarded = ['id'];

    protected $casts = [
        'tanggal' => 'date:Y-m-d',
    ];
}
