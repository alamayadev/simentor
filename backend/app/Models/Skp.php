<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Skp extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
    /**
     * Search scope for SKP by name.
     *
     * SECURITY NOTE: Laravel's query builder uses parameter binding to prevent SQL injection.
     * The $value parameter is automatically escaped/bound, making this safe from SQL injection attacks.
     * No additional sanitization is needed.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $value
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeSearch($query, $value){
        return $query->where('nama','like',"%{$value}%");
    }
}
