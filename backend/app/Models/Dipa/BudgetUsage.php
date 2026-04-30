<?php

namespace App\Models\Dipa;

use Illuminate\Database\Eloquent\Model;

class BudgetUsage extends Model
{
    protected $table = 'budget_usage';

    public $timestamps = false;

    protected $fillable = [
        'budget_item_key',
        'usage_date',
        'description',
        'amount_spent',
        'data_source',
    ];

    protected $casts = [
        'usage_date' => 'date',
        'amount_spent' => 'double',
        'created_at' => 'datetime',
    ];
}
