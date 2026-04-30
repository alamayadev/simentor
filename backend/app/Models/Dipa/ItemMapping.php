<?php

namespace App\Models\Dipa;

use Illuminate\Database\Eloquent\Model;

class ItemMapping extends Model
{
    protected $table = 'item_mapping';

    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = null;

    protected $fillable = [
        'old_composite_key',
        'new_composite_key',
    ];
}
