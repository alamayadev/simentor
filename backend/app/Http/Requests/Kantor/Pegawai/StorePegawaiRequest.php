<?php
namespace App\Http\Requests\Kantor\Pegawai;

class StorePegawaiRequest extends PegawaiRequest
{
    public function rules(): array
    {
        return [
            'nama'    => 'required|string|max:255',
            'pangkat' => 'required|string|max:255',
            'gol'     => 'required|string|max:255',
            'nip'     => 'required|string|max:255|unique:profil_pegawai,nip',
            'jabatan' => 'required|string|max:255',
            'kelas'   => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id',
            'status'  => 'nullable|string|max:255',
        ];
    }

    public function bodyParameters(): array
    {
        return [
            'nama'    => [
                'description' => 'The name of the pegawai.',
                'example'     => 'Andi Wijaya',
            ],
            'pangkat' => [
                'description' => 'The pangkat of the pegawai.',
                'example'     => 'Penata Muda',
            ],
            'gol'     => [
                'description' => 'The gol of the pegawai.',
                'example'     => 'III/a',
            ],
            'nip'     => [
                'description' => 'The nip of the pegawai.',
                'example'     => '199001012015011001',
            ],
            'jabatan' => [
                'description' => 'The jabatan of the pegawai.',
                'example'     => 'Statistisi Ahli Pertama',
            ],
            'kelas'   => [
                'description' => 'The kelas of the pegawai.',
                'example'     => '8',
            ],
            'user_id' => [
                'description' => 'The user_id of the pegawai.',
                'example'     => 1,
            ],
            'status'  => [
                'description' => 'The status of the pegawai.',
                'example'     => 'Aktif',
            ],
        ];
    }
}
