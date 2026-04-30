<?php
namespace App\Http\Requests\Kantor\MonitoringKegiatan;

use App\Http\Requests\ApiRequest;

class UpdateMonitoringRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'fungsi'                        => 'sometimes|required|string|max:255',
            'kegiatan_id'                   => 'sometimes|required|integer|exists:kegiatan,id',
            'kec_id'                        => 'sometimes|required|string|max:255',
            'desa_id'                       => 'sometimes|required|string|max:255',
            'kode_sampel'                   => 'sometimes|required|string|max:255',
            'monitoring_kegiatan_config_id' => 'sometimes|required|exists:monitoring_kegiatan_config,id',
            'detil_configurations'          => 'nullable|array',
            'detil_configurations.*'        => 'exists:detil_configurations,id',
            'detil_data'                    => 'nullable|array',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'fungsi'                        => [
                'description' => 'Function name.',
                'example'     => 'Social Statistics',
            ],
            'kegiatan_id'                   => [
                'description' => 'ID of the activity.',
                'example'     => 1,
            ],
            'kec_id'                        => [
                'description' => 'District ID.',
                'example'     => '3215010',
            ],
            'desa_id'                       => [
                'description' => 'Village ID.',
                'example'     => '3215010001',
            ],
            'kode_sampel'                   => [
                'description' => 'Sample code.',
                'example'     => 'SMPL001',
            ],
            'monitoring_kegiatan_config_id' => [
                'description' => 'ID of the monitoring configuration.',
                'example'     => 1,
            ],
            'detil_configurations'          => [
                'description' => 'Array of detail configuration IDs.',
                'example'     => [1, 2],
            ],
            'detil_data'                    => [
                'description' => 'Array of detail data.',
                'example'     => [['key' => 'value']],
            ],
        ];
    }
}
