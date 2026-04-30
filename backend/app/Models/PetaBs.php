<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PetaBs extends Model
{
    use HasFactory;
    protected $table = 'peta_bs';
    protected $guarded = ['id'];
}
