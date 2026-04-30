<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Enums\JenisKeluhanType;

class StoreTiketRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'jenis_keluhan' => 'required|string|in:' . implode(',', JenisKeluhanType::all()),
            'deskripsi' => 'required|string',
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
            'jenis_keluhan' => [
                'description' => 'Type of complaint. Must be one of: ' . implode(', ', JenisKeluhanType::all()),
                'example' => JenisKeluhanType::SISTEM->value,
                'required' => true,
            ],
            'deskripsi' => [
                'description' => 'Detailed description of the complaint',
                'example' => 'AC in room 101 is not cooling',
                'required' => true,
            ],
        ];
    }
}
