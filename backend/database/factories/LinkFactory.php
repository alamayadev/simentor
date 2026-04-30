<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Link;

class LinkFactory extends Factory
{
    protected $model = Link::class;

    public function definition()
    {
        return [
            'parent_id' => null,
            'nama' => $this->faker->words(2, true),
            'link' => $this->faker->optional()->url(),
        ];
    }
}
