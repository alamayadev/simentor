<?php

namespace Database\Factories;

use App\Models\Tiket;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TiketFactory extends Factory
{
    protected $model = Tiket::class;

    public function definition()
    {
        $jenisKeluhanOptions = [
            'Sistem',
            'Software',
            'Printer',
            'Hardware PC/Laptop',
            'Jaringan',
            'Akun BPS',
        ];

        return [
            'jenis_keluhan' => $this->faker->randomElement($jenisKeluhanOptions),
            'deskripsi' => $this->faker->paragraph(),
            'status' => 'open',
            'user_id' => User::factory(),
        ];
    }
}
