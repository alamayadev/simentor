<?php
namespace App\Http\Requests\Kantor\Laperdin;

use App\Http\Requests\ApiRequest;

class UpdateLaperdinRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nama_traveler' => 'sometimes|string|max:255',
            'tujuan'        => 'sometimes|string|max:255',
            'lama_tanggal'  => 'sometimes|string|max:255',
            'dalam_rangka'  => 'sometimes|string|max:255',
            'pembebanan'    => 'nullable|string|max:255',
            'kode_keg'      => 'nullable|exists:kegiatans,id',
            'status'        => 'sometimes|in:draft,submitted,final',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'nama_traveler' => [
                'description' => 'Name of the traveler.',
                'example'     => 'John Doe',
            ],
            'tujuan'        => [
                'description' => 'Destination of the trip.',
                'example'     => 'Jakarta',
            ],
            'lama_tanggal'  => [
                'description' => 'Duration of the trip.',
                'example'     => '3 days',
            ],
            'dalam_rangka'  => [
                'description' => 'Purpose of the trip.',
                'example'     => 'Meeting',
            ],
            'pembebanan'    => [
                'description' => 'Budget source.',
                'example'     => 'DIPA',
            ],
            'kode_keg'      => [
                'description' => 'Activity code.',
                'example'     => 1,
            ],
            'status'        => [
                'description' => 'Status of the report.',
                'example'     => 'draft',
            ],
        ];
    }
}
