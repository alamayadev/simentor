<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CekScan;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CekScanTest extends TestCase
{
    use RefreshDatabase;

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_create_a_cek_scan_record()
    {
        $cekScan = CekScan::factory()->create([
            'kec' => '010 PANGKALAN',
            'desa' => '001 MEDALSARI',
            'filename' => '32150100012001_WS.JPG',
            'fullpath' => 'C:\Users\user\AppData\Local\Temp\fake123.tmp',
            'created_time' => '2025-01-01 12:00:00',
            'jenis' => 'WS',
            'lokasi' => 'OK',
            'kodename' => '32150100012001_WS',
            'kode' => '32150100012001',
        ]);

        $this->assertDatabaseHas('cek_scans', [
            'kec' => '010 PANGKALAN',
            'desa' => '001 MEDALSARI',
            'filename' => '32150100012001_WS.JPG',
            'fullpath' => 'C:\Users\user\AppData\Local\Temp\fake123.tmp',
            'jenis' => 'WS',
            'lokasi' => 'OK',
            'kodename' => '32150100012001_WS',
            'kode' => '32150100012001',
        ]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function it_can_retrieve_a_cek_scan_record()
    {
        $cekScan = CekScan::factory()->create();

        $found = CekScan::find($cekScan->id);

        $this->assertNotNull($found);
        $this->assertEquals($cekScan->kec, $found->kec);
        $this->assertEquals($cekScan->desa, $found->desa);
        $this->assertEquals($cekScan->filename, $found->filename);
    }
}
