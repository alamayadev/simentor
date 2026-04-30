<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use App\Services\WilkerstatDashboardService;

class WilkerstatDashboardServiceTest extends TestCase
{
    public function testComputeWithOverrides()
    {
        $service = new WilkerstatDashboardService();
        $overrides = [
            'total_operator' => 5,
            'target' => 100,
            'progres_scan' => 80,
            'progres_georef' => 60,
            'scan_not_done' => 20,
            'georef_not_done' => 40,
            'georef_last_update' => '2025-09-22 10:00:00',
            'scan_last_update' => '2025-09-22 09:00:00',
        ];

        $result = $service->compute($overrides);

        $this->assertIsArray($result);
        $this->assertEquals(5, $result['total_operator']);
        $this->assertEquals(100, $result['target']);
        $this->assertEquals(80, $result['progres_scan']);
        $this->assertEquals(60, $result['progres_georef']);
        $this->assertEquals(20, $result['scan_not_done']);
        $this->assertEquals(40, $result['georef_not_done']);
        $this->assertEquals('2025-09-22 10:00:00', $result['georef_last_update']);
        $this->assertEquals('2025-09-22 09:00:00', $result['scan_last_update']);
    }
}
