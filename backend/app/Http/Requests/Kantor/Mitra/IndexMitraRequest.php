<?php
namespace App\Http\Requests\Kantor\Mitra;

use App\Http\Requests\ApiRequest;

class IndexMitraRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page'            => 'nullable|integer|min:1|max:100',
            'cursor'              => 'nullable|string',
            'search'              => 'nullable|string|max:255',
            'filter'              => 'nullable|array',
            'filter.email'        => 'nullable|string',
            'filter.sobat_id'     => 'nullable|string',
            'filter.nama_lengkap' => 'nullable|string',
            'filter.kab'          => 'nullable|string',
            'filter.keca'         => 'nullable|string',
            'filter.desa'         => 'nullable|string',
            'filter.nik'          => 'nullable|string',
            'filter.tgl_lahir'    => 'nullable|date',
            'filter.posisi'       => 'nullable|string',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
