<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * App\Models\Link
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $nama
 * @property string|null $link
 * @property-read Link|null $parent
 * @property-read \Illuminate\Database\Eloquent\Collection|Link[] $children
 */
class Link extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Cast columns to native types
     *
     * @var array<string,string>
     */
    protected $casts = [
        'parent_id' => 'integer',
    ];

    /**
     * Parent relation (self-referential)
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * Children relation (self-referential)
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Recursive children relationship (eager load nested children)
     */
    public function childrenRecursive(): HasMany
    {
        return $this->children()->with('childrenRecursive');
    }

    /**
     * Scope to include children count by default when needed
     */
    public function scopeWithChildrenCount($query)
    {
        return $query->withCount('children');
    }

    /**
     * Scope to get root nodes (parent_id is null)
     */
    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id');
    }
}
