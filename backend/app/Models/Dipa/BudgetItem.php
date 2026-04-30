<?php

namespace App\Models\Dipa;

use Illuminate\Database\Eloquent\Model;

class BudgetItem extends Model
{
    protected $table = 'budget_items';

    public $timestamps = false;

    protected $fillable = [
        'revision_name',
        'revision_date',
        'program_code',
        'program_name',
        'activity_code',
        'activity_name',
        'output_code',
        'output_name',
        'component_code',
        'component_name',
        'sub_component_code',
        'sub_component_name',
        'account_code',
        'account_name',
        'description',
        'volume',
        'unit',
        'unit_price',
        'total_amount',
        'funding_source',
        'composite_key',
        'tahun_anggaran',
    ];

    protected $casts = [
        'revision_date' => 'date',
        'volume' => 'double',
        'unit_price' => 'double',
        'total_amount' => 'double',
        'created_at' => 'datetime',
    ];
}
