<?php
namespace App\Http\Requests\Kantor\Spk;

use App\Http\Requests\ApiRequest;

class MonitoringSpkRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type'        => 'required|in:tanpa_spk,tanpa_bast,diatas_4jt',
            'per_page'    => 'nullable|integer|min:1|max:100',
            'page'        => 'nullable|integer|min:1',
            'selectedbln' => 'nullable|date_format:Y-m,Y-m-d',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
