<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SuratPermintaan extends Model
{
    use HasFactory;
    protected $table = 'surat_permintaan';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'tanggal' => 'datetime:Y-m-d',
        ];
    }

    // Accessor for bln attribute (maps to bulan column)
    public function getBlnAttribute()
    {
        return $this->attributes['bulan'] ?? null;
    }

    // Mutator for bln attribute (maps to bulan column)
    public function setBlnAttribute($value)
    {
        $this->attributes['bulan'] = $value;
    }
    // public function scopeSearch($query, $value){
    //     $query->where('no_surat','like',"%{$value}%")->orWhere('perihal','like',"%{$value}%")->orWhere('tujuan','like',"%{$value}%");
    // }
}
