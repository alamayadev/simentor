<?php

namespace Database\Factories;

use App\Models\AssetITMaintenanceSchedule;
use App\Models\AssetIT;
use Illuminate\Database\Eloquent\Factories\Factory;

class AssetITMaintenanceScheduleFactory extends Factory
{
    protected $model = AssetITMaintenanceSchedule::class;

    public function definition()
    {
        return [
            'asset_id' => AssetIT::factory(),
            'next_maintenance' => $this->faker->dateTimeBetween('now', '+1 year'),
            'responsible_team' => $this->faker->randomElement([
                'IT Team',
                'Infrastructure Team',
                'Network Team',
                'Support Team',
                'External Vendor'
            ]),
        ];
    }
}
