<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LaporanPerjalananDinasDetail extends Model
{
    use HasFactory;

    protected $table = 'laporan_perjalanan_dinas_detail';

    protected $guarded = ['id'];
}
