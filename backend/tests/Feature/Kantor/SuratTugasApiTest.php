<?php

namespace Tests\Feature\Kantor;

use App\Models\SuratTugas;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuratTugasApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_includes_menimbang_and_uraian(): void
    {
        $user = User::factory()->create();

        $suratTugas = SuratTugas::factory()->create([
            'tahun' => date('Y'),
            'nomor' => '0001',
            'no_mix' => '0001',
            'no_surat' => '0001/ST-100/' . date('Y'),
            'menimbang' => 'Dasar pertimbangan kegiatan',
            'uraian' => 'Melaksanakan pendataan lapangan',
        ]);

        $response = $this->actingAs($user, 'sanctum')
            ->getJson('/api/kantor/surat/surat-tugas?per_page=10&filter[tahun]=' . $suratTugas->tahun);

        $response->assertOk()
            ->assertJsonPath('data.0.id', $suratTugas->id)
            ->assertJsonPath('data.0.menimbang', 'Dasar pertimbangan kegiatan')
            ->assertJsonPath('data.0.uraian', 'Melaksanakan pendataan lapangan');
    }
}
