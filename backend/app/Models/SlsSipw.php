<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Alokasi;

class SlsSipw extends Model
{
    use HasFactory;

    protected $table = 'sls_sipw';

    protected $guarded = ['id'];

    protected $casts = [
        'j_subsls' => 'integer',
        'flag_perubahan_sls' => 'integer',
        'klas' => 'integer',
    ];

    /**
     * Get the alokasi that owns this SlsSipw record.
     */
    public function alokasi()
    {
        return $this->belongsTo(Alokasi::class, 'idsls', 'idsls');
    }
}
