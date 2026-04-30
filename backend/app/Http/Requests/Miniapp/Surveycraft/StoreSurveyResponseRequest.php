<?php
namespace App\Http\Requests\Miniapp\Surveycraft;

use App\Http\Requests\ApiRequest;

class StoreSurveyResponseRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'respond_id' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9._-]+$/'],
            'json_file'  => ['required', 'file', 'mimes:json', 'max:2048'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'respond_id' => [
                'description' => 'Unique identifier for the response.',
                'example'     => 'RESPONSE-001',
            ],
            'json_file'  => [
                'description' => 'Survey response file (JSON).',
            ],
        ];
    }
}
