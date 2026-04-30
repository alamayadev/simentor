<?php

namespace App\Models\Dipa;

use Illuminate\Database\Eloquent\Model;

class ImportedFile extends Model
{
    protected $table = 'imported_files';

    public $timestamps = false;

    protected $fillable = [
        'file_path',
        'file_hash',
        'revision_name',
        'tahun_anggaran',
        'imported_at',
    ];

    protected $casts = [
        'imported_at' => 'datetime',
    ];
}
