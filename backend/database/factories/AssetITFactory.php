<?php

namespace Database\Factories;

use App\Models\AssetIT;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetITFactory extends Factory
{
    protected $model = AssetIT::class;

    public function definition()
    {
        $typeOptions = ['hardware', 'software'];
        $categoryOptions = ['Server', 'Laptop', 'Desktop', 'Printer', 'Network', 'Software License'];
        $statusOptions = ['active', 'inactive', 'maintenance', 'retired'];

        return [
            'kode_asset' => 'AS-' . $this->faker->unique()->numerify('######'),
            'type' => $this->faker->randomElement($typeOptions),
            'category' => $this->faker->randomElement($categoryOptions),
            'brand' => $this->faker->randomElement(['Dell', 'HP', 'Lenovo', 'Asus', 'Apple', 'Microsoft', 'Cisco']),
            'model' => $this->faker->word(),
            'serial_number' => $this->faker->optional()->bothify('??#######'),
            'name' => $this->faker->optional()->words(3, true),
            'license_key' => $this->faker->optional()->bothify('****-****-****-****'),
            'device' => $this->faker->optional()->word(),
            'ip_address' => $this->faker->optional()->ipv4(),
            'location' => $this->faker->optional()->city(),
            'status' => $this->faker->randomElement($statusOptions),
            'assigned_to' => $this->faker->optional()->name(),
            'purchase_date' => $this->faker->optional()->date(),
            'warranty_expiry' => $this->faker->optional()->date(),
            'expiry_date' => $this->faker->optional()->date(),
            'delivery_date' => $this->faker->optional()->date(),
        ];
    }
}
