<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Meta extends Model
{
    protected $fillable = [
        'id',
        'parent_id',
        'name',
        'name2',
    ];

    /**
     * Get the parent meta that owns this meta.
     */
    public function parent()
    {
        return $this->belongsTo(Meta::class, 'parent_id');
    }

    /**
     * Get the children metas for this meta.
     */
    public function children()
    {
        return $this->hasMany(Meta::class, 'parent_id');
    }
}
