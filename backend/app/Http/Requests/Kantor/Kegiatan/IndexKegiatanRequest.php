<?php
namespace App\Http\Requests\Kantor\Kegiatan;

use App\Http\Requests\ApiRequest;

class IndexKegiatanRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page'              => 'nullable|integer|min:1|max:100',
            'cursor'                => 'nullable|string',
            'search'                => 'nullable|string|max:255',
            'fungsi'                => 'nullable|string|in:Umum,Distribusi,Produksi,Sosial,Nerwilis,IPDS',
            'sort_by'               => 'nullable|string|in:created_at,id,nama',
            'sort_dir'              => 'nullable|string|in:ASC,DESC',
            'filter'                => 'nullable|array',
            'filter.tahun'          => 'nullable|string|size:4',
            'filter.jenis_kegiatan' => 'nullable|string',
            'filter.status'         => 'nullable|string',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
