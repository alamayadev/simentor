<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SuratTugas extends Model
{
    use HasFactory;

    protected $table = 'surat_tugas';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime:Y-m-d',
        ];
    }
    public function scopeSearch($query, $value){
        $query->where('no_surat','like',"%{$value}%")->orWhere('uraian','like',"%{$value}%")->orWhere('kepada','like',"%{$value}%");
    }

    public function surtugDetil(): HasMany
    {
        return $this->hasMany(SurtugDetil::class, 'surtug_id');
    }
}
