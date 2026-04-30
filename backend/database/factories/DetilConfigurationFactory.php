<?php

namespace Database\Factories;

use App\Models\DetilConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DetilConfiguration>
 */
class DetilConfigurationFactory extends Factory
{
    protected $model = DetilConfiguration::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'related_table' => $this->faker->randomElement(['penugasan', 'kegiatan', null]),
            'foreign_key' => $this->faker->randomElement(['kegiatan_id', 'penugasan_id', null]),
            'field' => [
                'source' => 'custom',
                'name' => $this->faker->slug(2),
                'label' => $this->faker->words(2, true),
                'type' => 'text',
                'required' => false,
            ],
            'is_active' => true,
        ];
    }
}
