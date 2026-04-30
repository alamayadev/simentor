<?php
namespace App\Http\Requests\Miniapp\Surveycraft;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class UpdateSurveycraftRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'survey_id'      => ['sometimes', 'string', 'max:255'],
            'title'          => ['sometimes', 'string', 'max:255'],
            'description'    => ['sometimes', 'nullable', 'string'],
            'json_file'      => ['sometimes', 'file', 'mimes:json', 'max:10240'],
            'published_link' => ['sometimes', 'nullable', 'string', 'max:255'],
            'owner_id'       => ['sometimes', 'integer', Rule::exists('users', 'id')],
            'timestamp'      => ['sometimes', 'nullable', 'date'],
            'questions'      => ['sometimes', 'nullable', 'integer'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'survey_id'   => [
                'description' => 'Unique identifier for the survey.',
                'example'     => 'SURVEY-001',
            ],
            'title'       => [
                'description' => 'Title of the survey.',
                'example'     => 'Customer Satisfaction Survey',
            ],
            'description' => [
                'description' => 'Description of the survey.',
                'example'     => 'Survey to measure customer satisfaction.',
            ],
            'json_file'   => [
                'description' => 'Survey configuration file (JSON).',
            ],
        ];
    }
}
