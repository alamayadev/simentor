<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTiketRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'status' => 'nullable|string',
            'keterangan' => 'nullable|string',
        ];
    }

    /**
     * Provide body parameter metadata for Scribe.
     *
     * @return array
     */
    public static function bodyParameters(): array
    {
        return [
            'status' => [
                'description' => 'Status of the tiket (open, in_progress, closed)',
                'example' => 'in_progress',
                'required' => false,
            ],
            'keterangan' => [
                'description' => 'Optional note or explanation when updating tiket',
                'example' => 'Assigned to technician',
                'required' => false,
            ],
        ];
    }
}
