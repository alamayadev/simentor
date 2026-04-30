<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PetaSls extends Model
{
    use HasFactory;

    protected $table = 'peta_sls';

    protected $guarded = ['id'];

    protected $casts = [
        'jml' => 'integer',
    ];

    /**
     * Get the target peta associated with the peta sls.
     */
    public function targetPeta()
    {
        return $this->hasOne(TargetPeta::class, 'filename', 'filename');
    }
}
