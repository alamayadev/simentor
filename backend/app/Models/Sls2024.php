<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Sls2024 extends Model
{
    protected $guarded = ['id'];
    protected $table = 'sls_2024_2';
    public $timestamps = false;

    public function kec(): HasOne
    {
        return $this->hasOne(SlsKec::class,'kdkec','kdkec');
    }
    public function pcl(): BelongsTo
    {
        return $this->belongsTo(Mitra::class,'pcl_id');
    }
    public function pml(): BelongsTo
    {
        return $this->belongsTo(Mitra::class,'pml_id');
    }
}
