<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Uu extends Model
{
    protected $table = 'uu';

    protected $fillable = [
        'jenis',
        'nama',
        'detil',
    ];
}

