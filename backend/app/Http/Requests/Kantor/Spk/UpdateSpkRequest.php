<?php
namespace App\Http\Requests\Kantor\Spk;

use App\Http\Requests\ApiRequest;

class UpdateSpkRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'no_sk'    => 'sometimes|nullable|string|max:255',
            'tgl_sk'   => 'sometimes|nullable|date_format:Y-m-d',
            'no_bast'  => 'sometimes|nullable|string|max:255',
            'tgl_bast' => 'sometimes|nullable|date_format:Y-m-d',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'mitra_id'  => [
                'description' => 'ID of the partner.',
                'example'     => 1,
            ],
            'tgl_sk'    => [
                'description' => 'Date of SK.',
                'example'     => '2023-01-01',
            ],
            'bln_bayar' => [
                'description' => 'Payment month.',
                'example'     => '2023-01-01',
            ],
            'tgl_bast'  => [
                'description' => 'Date of BAST.',
                'example'     => '2023-01-01',
            ],
        ];
    }
}
