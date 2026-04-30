<?php
namespace App\Http\Requests\Kantor\Pegawai;

class IndexPegawaiRequest extends PegawaiRequest
{
    public function rules(): array
    {
        return [
            'per_page'       => 'nullable|integer|min:1|max:100',
            'cursor'         => 'nullable|string',
            'search'         => 'nullable|string|max:255',
            'filter'         => 'nullable|array',
            'filter.pangkat' => 'nullable|string',
            'filter.jabatan' => 'nullable|string',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
