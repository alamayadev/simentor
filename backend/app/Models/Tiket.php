<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Enums\JenisKeluhanType;

class Tiket extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $casts = [
        'jenis_keluhan' => JenisKeluhanType::class,
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id')->with('pegawai');
    }
    public function responder(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditangani_oleh')->with('pegawai');
    }
    public function handleBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
