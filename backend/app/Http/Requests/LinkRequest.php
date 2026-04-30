<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class LinkRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Keep default behavior: rely on controller/middleware for auth/permissions
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'nama' => 'required|string|max:255',
            'link' => 'nullable|string|max:2048',
            'parent_id' => 'nullable|integer|exists:links,id',
        ];

        return $rules;
    }

    /**
     * Get the body parameters for Scribe documentation.
     *
     * @return array[]
     */
    public function bodyParameters(): array
    {
        return [
            'nama' => [
                'description' => 'Nama link yang akan ditampilkan',
                'example' => 'Dashboard',
            ],
            'link' => [
                'description' => 'URL tujuan dari link (opsional)',
                'example' => '/dashboard',
            ],
            'parent_id' => [
                'description' => 'ID parent link untuk membuat hirarki menu (opsional)',
                'example' => 1,
            ],
        ];
    }
}
