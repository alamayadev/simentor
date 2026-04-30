<?php
namespace App\Http\Requests\Kantor\Skp;

use App\Http\Requests\ApiRequest;

class ListSkpRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => 'nullable|integer|min:1|max:100',
            'cursor'   => 'nullable|string',
            'search'   => 'nullable|string',
            'user_id'  => 'nullable|integer|exists:users,id',
            'tahun'    => 'nullable|string|min:4|max:4',
            'sort_by'  => 'nullable|string|in:nama,jenis,bulan,tahun,created_at',
            'sort_dir' => 'nullable|string|in:ASC,DESC',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
