<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratKeluar extends Model
{
    use HasFactory;
    protected $table = 'surat_keluar';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime:Y-m-d',
            'tembusan' => 'array',
        ];
    }
}
