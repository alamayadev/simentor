<?php
namespace App\Http\Requests\Kantor\Spk;

use App\Http\Requests\ApiRequest;

class BulkUpdateSpkRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'mitra_ids'   => 'required|array',
            'mitra_ids.*' => 'integer|exists:mitra_kepka,id',
            'tgl_sk'      => 'required|date',
            'bln_bayar'   => 'required|date_format:Y-m',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'mitra_ids'   => [
                'description' => 'List of mitra IDs to update.',
                'example'     => [1, 2, 3],
            ],
            'mitra_ids.*' => [
                'description' => 'Mitra ID.',
                'example'     => 1,
            ],
            'tgl_sk'      => [
                'description' => 'Date of SK.',
                'example'     => '2023-01-01',
            ],
            'bln_bayar'   => [
                'description' => 'Payment month (YYYY-MM).',
                'example'     => '2023-01',
            ],
        ];
    }
}
