<?php
namespace App\Http\Requests\Kantor\Laperdin;

use App\Http\Requests\ApiRequest;

class StoreLaperdinDetailRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tanggal'    => 'required|date',
            'uraian_lhp' => 'required|string',
            'kendala'    => 'nullable|string',
            'solusi'     => 'nullable|string',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'tanggal'    => [
                'description' => 'Date of the activity.',
                'example'     => '2023-01-01',
            ],
            'uraian_lhp' => [
                'description' => 'Description of the activity.',
                'example'     => 'Meeting with client',
            ],
            'kendala'    => [
                'description' => 'Obstacles encountered.',
                'example'     => 'None',
            ],
            'solusi'     => [
                'description' => 'Solutions implemented.',
                'example'     => 'N/A',
            ],
        ];
    }
}
