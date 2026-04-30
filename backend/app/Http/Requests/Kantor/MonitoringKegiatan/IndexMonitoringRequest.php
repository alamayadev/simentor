<?php
namespace App\Http\Requests\Kantor\MonitoringKegiatan;

use App\Http\Requests\ApiRequest;

class IndexMonitoringRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'kegiatan_id' => 'nullable|string',
            'per_page'    => 'nullable|integer|min:1|max:100',
            'cursor'      => 'nullable|string',
        ];
    }

    public function bodyParameters(): array
    {
        return [];
    }
}
