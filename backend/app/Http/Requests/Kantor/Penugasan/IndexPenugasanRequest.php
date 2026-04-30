<?php
namespace App\Http\Requests\Kantor\Penugasan;

use App\Http\Requests\ApiRequest;

class IndexPenugasanRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page'             => 'nullable|integer|min:1|max:100',
            'cursor'               => 'nullable|string',
            'filter'               => 'nullable|array',
            'filter.kegiatan_id'   => 'nullable|integer|exists:kegiatan,id',
            'filter.jabatan_tugas' => 'nullable|string',
            'filter.mitra_id'      => 'nullable|integer|exists:mitra_kepka,id',
            'filter.bln_bayar'     => 'nullable|date_format:Y-m-d',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
