<?php
namespace App\Http\Requests\Kantor\Penugasan;

use App\Http\Requests\ApiRequest;
use App\Models\Penugasan;

class UpdatePenugasanRequest extends ApiRequest
{
    public function authorize(): bool
    {
        $id        = $this->route('id');
        $penugasan = Penugasan::find($id);
        return $penugasan && $penugasan->created_by === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'kegiatan_id'   => 'sometimes|integer|exists:kegiatan,id',
            'jabatan_tugas' => 'sometimes|string|max:255',
            'mitra_id'      => 'nullable|integer|exists:mitra_kepka,id',
            'pegawai_id'    => 'nullable|integer|exists:profil_pegawai,id',
            'volume'        => 'sometimes|integer|min:1',
            'bln_bayar'     => 'nullable|date_format:Y-m-d',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'mitra_id'  => [
                'description' => 'ID of the partner.',
                'example'     => 1,
            ],
            'volume'    => [
                'description' => 'Volume of work.',
                'example'     => 10,
            ],
            'bln_bayar' => [
                'description' => 'Payment month.',
                'example'     => '2023-01-01',
            ],
            'tgl_bast'  => [
                'description' => 'Date of BAST.',
                'example'     => '2023-01-01',
            ],
            'tgl_sk'    => [
                'description' => 'Date of SK.',
                'example'     => '2023-01-01',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $mitraId   = $this->input('mitra_id');
            $pegawaiId = $this->input('pegawai_id');
            if ($mitraId && $pegawaiId) {
                $validator->errors()->add('mitra_id', 'Hanya boleh mengisi salah satu dari mitra_id atau pegawai_id.');
            }
        });
    }

    protected function failedAuthorization()
    {
        throw new \Illuminate\Http\Exceptions\HttpResponseException(response()->json([
            'success' => false,
            'message' => 'Anda tidak memiliki izin untuk mengedit penugasan ini.',
        ], 403));
    }
}
