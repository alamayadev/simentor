<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Survey extends Model
{
    protected $fillable = [
        'survey_name',
        'survey_description',
        'json_file',
        'source_json',
        'validation_rules',
        'start_at',
        'end_at',
    ];

    protected $casts = [
        'json_file' => 'array',
        'source_json' => 'array',
        'validation_rules' => 'array',
        'start_at' => 'datetime',
        'end_at' => 'datetime',
    ];

    public function results()
    {
        return $this->hasMany(SurveyResult::class);
    }
}
