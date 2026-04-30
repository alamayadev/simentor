<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Mitra;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mitra>
 */
class MitraFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Mitra::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'sobat_id' => $this->faker->unique()->numerify('######'),
            'posisi' => $this->faker->randomElement(['PCL', 'PML', 'Kortim', 'Koseka']),
            'status_seleksi' => $this->faker->randomElement(['Lulus', 'Tidak Lulus', 'Proses']),
            'posisi_daftar' => $this->faker->randomElement(['PCL', 'PML', 'Kortim', 'Koseka']),
            'nama_lengkap' => $this->faker->name(),
            'alamat_detail' => $this->faker->streetAddress(),
            'alamat_prov' => $this->faker->state(),
            'alamat_kab' => $this->faker->city(),
            'alamat_kec' => $this->faker->city(),
            'alamat_desa' => $this->faker->city(),
            'tgl_lahir' => $this->faker->date(),
            'jenis_kelamin' => $this->faker->randomElement(['Laki-laki', 'Perempuan']),
            'agama' => $this->faker->randomElement(['Islam', 'Kristen Protestan', 'Kristen Katolik', 'Hindu', 'Buddha']),
            'status_kawin' => $this->faker->randomElement(['Kawin', 'Belum Kawin', 'Cerai Hidup', 'Cerai Mati']),
            'pendidikan' => $this->faker->randomElement(['SMA', 'D3', 'S1', 'S2']),
            'pekerjaan' => $this->faker->randomElement(['Swasta', 'PNS', 'Wiraswasta', 'Pelajar/Mahasiswa']),
            'no_telp' => $this->faker->phoneNumber(),
            'nik' => $this->faker->unique()->numerify('################'),
        ];
    }
}
