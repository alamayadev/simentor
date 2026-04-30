<?php
namespace App\Http\Requests\Miniapp\Surveycraft;

use App\Http\Requests\ApiRequest;
use Illuminate\Validation\Rule;

class StoreSurveycraftRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'survey_id'      => ['required', 'string', 'max:255'],
            'title'          => ['required', 'string', 'max:255'],
            'questions'      => ['sometimes', 'nullable', 'integer'],
            'description'    => ['nullable', 'string'],
            'json_file'      => ['required', 'file', 'mimes:json', 'max:2048'],
            'published_link' => ['nullable', 'string', 'max:255'],
            'owner_id'       => ['required', 'integer', Rule::exists('users', 'id')],
            'timestamp'      => ['nullable', 'date'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'survey_id'      => [
                'description' => 'Unique identifier for the survey.',
                'example'     => 'SURVEY-001',
            ],
            'title'          => [
                'description' => 'Title of the survey.',
                'example'     => 'Customer Satisfaction Survey',
            ],
            'questions'      => [
                'description' => 'Number of questions.',
                'example'     => 10,
            ],
            'description'    => [
                'description' => 'Description of the survey.',
                'example'     => 'Survey to measure customer satisfaction.',
            ],
            'json_file'      => [
                'description' => 'Survey configuration file (JSON).',
            ],
            'published_link' => [
                'description' => 'Link to the published survey.',
                'example'     => 'https://example.com/survey/123',
            ],
            'owner_id'       => [
                'description' => 'ID of the survey owner.',
                'example'     => 1,
            ],
            'timestamp'      => [
                'description' => 'Timestamp of creation.',
                'example'     => '2023-01-01 12:00:00',
            ],
        ];
    }
}
