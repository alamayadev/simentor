<?php
namespace App\Http\Requests\Kantor\Kegiatan;

use App\Http\Requests\ApiRequest;

class StoreKegiatanRequest extends ApiRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'tahun'          => 'required|string|size:4',
            'fungsi'         => 'required|string|in:Umum,Distribusi,Produksi,Sosial,Nerwilis,IPDS',
            'kode_kegiatan'  => 'required|string|size:11',
            'nama'           => 'required|string|min:10|max:255',
            'tgl_mulai'      => 'required|date_format:Y-m-d',
            'tgl_selesai'    => 'required|date_format:Y-m-d',
            'jenis_kegiatan' => 'required|string|in:PERSIAPAN,PENGUMPULAN DATA,PENGOLAHAN,DISEMINASI,PENGAWASAN/SUPERVISI',
            'jml_ptgs'       => 'required|integer|min:1',
            'volume'         => 'required|integer|min:1',
            'satuan'         => 'required|string|max:255',
            'rate_pcl'       => 'nullable|integer|min:0',
            'rate_pml'       => 'nullable|integer|min:0',
            'rate_entri'     => 'nullable|integer|min:0',
            'status'         => 'nullable|string|in:aktif,tidak dicairkan,dibatalkan',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $ratePcl   = $this->input('rate_pcl', 0);
            $ratePml   = $this->input('rate_pml', 0);
            $rateEntri = $this->input('rate_entri', 0);

            if ($ratePcl <= 0 && $ratePml <= 0 && $rateEntri <= 0) {
                $errorMsg = 'At least one of rate_pcl, rate_pml, or rate_entri must have a value greater than 0.';
                $validator->errors()->add('rate_pcl', $errorMsg);
                $validator->errors()->add('rate_pml', $errorMsg);
                $validator->errors()->add('rate_entri', $errorMsg);
            }
        });
    }

    public function bodyParameters(): array
    {
        return [
            'tahun'          => [
                'description' => 'Year of the activity.',
                'example'     => '2023',
            ],
            'fungsi'         => [
                'description' => 'Function responsible for the activity.',
                'example'     => 'IPDS',
            ],
            'kode_kegiatan'  => [
                'description' => 'Code of the activity.',
                'example'     => '12345678901',
            ],
            'nama'           => [
                'description' => 'Name of the activity.',
                'example'     => 'Annual Survey',
            ],
            'tgl_mulai'      => [
                'description' => 'Start date.',
                'example'     => '2023-01-01',
            ],
            'tgl_selesai'    => [
                'description' => 'End date.',
                'example'     => '2023-12-31',
            ],
            'jenis_kegiatan' => [
                'description' => 'Type of activity.',
                'example'     => 'PENGUMPULAN DATA',
            ],
            'jml_ptgs'       => [
                'description' => 'Number of officers.',
                'example'     => 5,
            ],
            'volume'         => [
                'description' => 'Target volume.',
                'example'     => 100,
            ],
            'satuan'         => [
                'description' => 'Unit of measurement.',
                'example'     => 'Documents',
            ],
            'rate_pcl'       => [
                'description' => 'Rate for PCL.',
                'example'     => 50000,
            ],
            'rate_pml'       => [
                'description' => 'Rate for PML.',
                'example'     => 60000,
            ],
            'rate_entri'     => [
                'description' => 'Rate for data entry.',
                'example'     => 45000,
            ],
            'status'         => [
                'description' => 'Status of the activity.',
                'example'     => 'aktif',
            ],
        ];
    }
}
