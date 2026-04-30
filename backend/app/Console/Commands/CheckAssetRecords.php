<?php

namespace App\Console\Commands;

use App\Models\AssetIT;
use App\Models\AssetITMaintenanceSchedule;
use Illuminate\Console\Command;

class CheckAssetRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-asset-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check asset records and maintenance schedules';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $assetCount = AssetIT::count();
        $scheduleCount = AssetITMaintenanceSchedule::count();

        $this->info("Asset IT records count: {$assetCount}");
        $this->info("Asset IT Maintenance Schedule records count: {$scheduleCount}");

        if ($assetCount > 0) {
            $this->info("Sample Asset Records:");
            AssetIT::limit(3)->get()->each(function ($asset) {
                $this->info("ID: {$asset->id}, Kode Asset: {$asset->kode_asset}, Type: {$asset->type}");
            });
        }

        if ($scheduleCount > 0) {
            $this->info("Sample Maintenance Schedule Records:");
            AssetITMaintenanceSchedule::limit(3)->get()->each(function ($schedule) {
                $this->info("ID: {$schedule->id}, Asset ID: {$schedule->asset_id}, Next Maintenance: {$schedule->next_maintenance}");
            });
        }
    }
}
