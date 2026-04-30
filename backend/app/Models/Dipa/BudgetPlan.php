<?php

namespace App\Models\Dipa;

use Illuminate\Database\Eloquent\Model;

class BudgetPlan extends Model
{
    protected $table = 'budget_plans';

    public $timestamps = false;

    protected $fillable = [
        'budget_item_key',
        'target_month',
        'description',
        'planned_amount',
    ];

    protected $casts = [
        'target_month' => 'date',
        'planned_amount' => 'double',
        'created_at' => 'datetime',
    ];
}
