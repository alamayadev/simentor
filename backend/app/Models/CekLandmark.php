<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CekLandmark extends Model
{
    use HasFactory;

    protected $table = '004_cek_landmark';
    public $timestamps = false;

    protected $fillable = [];


    public function pcl(): BelongsTo
    {
        return $this->BelongsTo(Mitra::class,'pcl_id');
    }
    public function pml(): BelongsTo
    {
        return $this->BelongsTo(Mitra::class,'pml_id');
    }
    public function scopeSearch($query, $value){
        $query->where('nmsls','like',"%{$value}%")->orWhere('deskripsi_project','like',"%{$value}%");
    }
}