<?php
namespace App\Http\Requests\Kantor\Skp;

use App\Http\Requests\ApiRequest;

class StoreSkpRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => 'required|string|in:SKP Bulanan,SKP Triwulanan,SKP Tahunan (Penetapan),SKP Tahunan (Penilaian),SKP Evaluasi Tahunan',
            'bulan' => 'nullable|string|max:3',
            'tahun' => 'required|string|min:4|max:4',
            'file'  => 'required|file|mimes:pdf|max:7168',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'jenis' => [
                'description' => 'The type of SKP.',
                'example'     => 'SKP Bulanan',
            ],
            'bulan' => [
                'description' => 'The month of SKP (01-12).',
                'example'     => '01',
            ],
            'tahun' => [
                'description' => 'The year of SKP.',
                'example'     => '2023',
            ],
            'file'  => [
                'description' => 'The SKP file (PDF).',
            ],
        ];
    }
}
