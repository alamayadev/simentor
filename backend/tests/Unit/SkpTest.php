<?php

namespace Tests\Unit;

use App\Models\Skp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkpTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_escapes_sql_injection()
    {
        // Create a test SKP
        Skp::create([
            'user_id' => 1,
            'jenis' => 'SKP Bulanan',
            'nama' => 'Test SKP',
            'bulan' => '01',
            'tahun' => '2024',
            'link' => 'test.pdf',
            'konten' => 'Test content'
        ]);

        // Try SQL injection
        $maliciousInput = "'; DROP TABLE skps; --";
        $results = Skp::search($maliciousInput)->get();

        // Should return empty collection, not crash
        $this->assertTrue($results->isEmpty());
    }

    public function test_search_works_with_valid_input()
    {
        Skp::create([
            'user_id' => 1,
            'jenis' => 'SKP Bulanan',
            'nama' => 'Test SKP',
            'bulan' => '01',
            'tahun' => '2024',
            'link' => 'test.pdf',
            'konten' => 'Test content'
        ]);

        $results = Skp::search('Test')->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Test SKP', $results->first()->nama);
    }
}
