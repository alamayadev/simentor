<?php
namespace App\Http\Requests\Kantor\Laperdin;

use App\Http\Requests\ApiRequest;

class StoreLaperdinDokumentasiRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'file'      => 'required|file|image|max:10240',
            'deskripsi' => 'nullable|string|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'file'      => [
                'description' => 'Documentation file (image).',
            ],
            'deskripsi' => [
                'description' => 'Description of the documentation.',
                'example'     => 'Photo of the meeting',
            ],
        ];
    }
}
