<?php
namespace App\Http\Requests\Kantor\Skp;

use App\Http\Requests\ApiRequest;

class IndexSkpRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'  => 'nullable|integer|min:2000|max:' . (now()->year + 1),
            'tahun2' => 'nullable|integer|min:2000|max:' . (now()->year + 1),
            'bulan'  => 'nullable|string|max:2',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
