<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SurveyResult extends Model
{
    protected $fillable = [
        'survey_id',
        'survey_result',
    ];

    protected $casts = [
        'survey_result' => 'array',
    ];

    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }
}
