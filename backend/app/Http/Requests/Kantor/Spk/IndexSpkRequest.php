<?php
namespace App\Http\Requests\Kantor\Spk;

use App\Http\Requests\ApiRequest;

class IndexSpkRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page'    => 'nullable|integer|min:1|max:100',
            'page'        => 'nullable|integer|min:1',
            'selectedbln' => 'nullable|date_format:Y-m,Y-m-d',
            'sort_by'     => 'nullable|in:created_at,bln_bayar,total,jml_tugas,id',
            'sort_dir'    => 'nullable|in:ASC,DESC',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
