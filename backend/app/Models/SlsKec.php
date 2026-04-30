<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SlsKec extends Model
{
    protected $guarded = ['id'];
    protected $table = 'sls_kec';
    public $timestamps = false;

    public function sls(): HasMany
    {
        return $this->hasMany(Sls2024::class,'kdkec','kdkec');
    }
}
