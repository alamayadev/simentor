<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRawDataRequest extends FormRequest
{
    public function authorize()
    {
        return $this->user() !== null;
    }

    public function rules()
    {
        return [
            'type' => 'sometimes|required|string|max:255',
            'fungsi' => 'sometimes|required|string|max:255',
            'nama' => 'sometimes|required|string|max:255',
            'keterangan' => 'sometimes|nullable|string',
            'file' => 'sometimes|required|file|mimetypes:text/csv,text/plain,application/csv,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/zip,application/x-rar-compressed|max:51200',
        ];
    }

    /**
     * Get body parameters for Scribe documentation.
     *
     * @return array
     */
    public function bodyParameters()
    {
        return [
            'type' => [
                'description' => 'Tipe data mentah',
                'example' => 'Survei',
                'type' => 'string',
                'required' => false,
                'max_length' => 255,
            ],
            'fungsi' => [
                'description' => 'Fungsi dari data mentah',
                'example' => 'IPDS',
                'type' => 'string',
                'required' => false,
                'max_length' => 255,
            ],
            'nama' => [
                'description' => 'Nama dari data mentah',
                'example' => 'Data Survei Sosial Ekonomi 2024',
                'type' => 'string',
                'required' => false,
                'max_length' => 255,
            ],
            'keterangan' => [
                'description' => 'Keterangan tambahan tentang data',
                'example' => 'Data ini mencakup survei di 10 provinsi',
                'type' => 'string',
                'required' => false,
            ],
            'file' => [
                'description' => 'File data mentah (ZIP, RAR, CSV, atau XLSX)',
                'example' => 'data_survei.zip',
                'type' => 'file',
                'required' => false,
                'mimes' => ['zip', 'rar', 'csv', 'xlsx'],
                'max_size' => '51200', // 50MB in KB
            ],
        ];
    }
}
