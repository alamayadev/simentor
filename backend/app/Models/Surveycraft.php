<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Surveycraft extends Model
{
    use HasFactory;

    protected $table = 'surveycraft';

    protected $guarded = ['id'];

    public $timestamps = false;

    protected $casts = [
        'owner_id' => 'integer',
        'timestamp' => 'datetime',
    ];

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
