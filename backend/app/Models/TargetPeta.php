<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetPeta extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'target_peta';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'filename',
        'keterangan',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'keterangan' => 'string',
    ];

    /**
     * Get the peta sls that owns the target peta.
     */
    public function petaSls()
    {
        return $this->belongsTo(PetaSls::class, 'filename', 'filename');
    }
}
