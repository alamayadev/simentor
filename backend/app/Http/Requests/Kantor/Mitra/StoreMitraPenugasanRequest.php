<?php
namespace App\Http\Requests\Kantor\Mitra;

use App\Http\Requests\ApiRequest;

class StoreMitraPenugasanRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kegiatan_id'   => 'required|exists:kegiatan,id',
            'jabatan_tugas' => 'required|in:PCL,PML,OPERATOR',
            'mitra_id'      => 'required|exists:mitra_kepka,id',
            'volume'        => 'required|numeric|min:1',
            'bln_bayar'     => 'required|date',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'kegiatan_id'   => [
                'description' => 'ID of the activity.',
                'example'     => 1,
            ],
            'jabatan_tugas' => [
                'description' => 'Position in the activity (PCL, PML, OPERATOR).',
                'example'     => 'PCL',
            ],
            'mitra_id'      => [
                'description' => 'ID of the partner.',
                'example'     => 1,
            ],
            'volume'        => [
                'description' => 'Volume of work.',
                'example'     => 10,
            ],
            'bln_bayar'     => [
                'description' => 'Payment month.',
                'example'     => '2023-01-01',
            ],
        ];
    }
}
