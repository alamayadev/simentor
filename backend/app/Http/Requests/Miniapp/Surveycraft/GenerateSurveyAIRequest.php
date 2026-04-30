<?php
namespace App\Http\Requests\Miniapp\Surveycraft;

use App\Http\Requests\ApiRequest;

class GenerateSurveyAIRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'prompt' => ['required', 'string', 'max:1000'],
            'lang'   => ['required', 'string', 'in:en,id'],
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'prompt' => [
                'description' => 'Prompt for AI generation.',
                'example'     => 'Create a survey about employee engagement.',
            ],
            'lang'   => [
                'description' => 'Language for the survey (en/id).',
                'example'     => 'en',
            ],
        ];
    }
}
