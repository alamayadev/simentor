<?php

namespace Tests\Feature\Kantor;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Penugasan;
use App\Models\Mitra;
use Illuminate\Support\Facades\Log;

class SpkApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_bulk_update_spk_generates_correct_no_sk_and_tgl_sk()
    {
        // Create mitra
        $mitra1 = Mitra::factory()->create();
        $mitra2 = Mitra::factory()->create();

        // Create penugasan records with existing no_sk values for mitra1
        $penugasan1 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2024-05-01',
            'no_sk' => '0001',
            'tgl_sk' => '2024-04-01'
        ]);

        $penugasan2 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2024-06-01', // Different bln_bayar
            'no_sk' => '0002',
            'tgl_sk' => '2024-04-01'
        ]);

        // Create penugasan records for mitra1 with the target bln_bayar that should be updated
        $penugasan3 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2024-07-01', // This is our target bln_bayar
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $penugasan4 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2024-07-01', // Same target bln_bayar
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        // Create penugasan records for mitra2
        $penugasan5 = Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2024-07-01', // Same target bln_bayar
            'no_sk' => '0005',
            'tgl_sk' => '2024-04-01'
        ]);

        $penugasan6 = Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2024-07-01', // Same target bln_bayar
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/spk/bulk-update', [
                'mitra_ids' => [$mitra1->id, $mitra2->id],
                'bln_bayar' => '2024-07', // Target bln_bayar
                'tgl_sk' => '2024-05-15' // Should be converted to first day of month
            ]);

        $response->assertStatus(200);

        // Verify the updated records for mitra1
        $updatedPenugasan3 = Penugasan::find($penugasan3->id);
        $updatedPenugasan4 = Penugasan::find($penugasan4->id);

        // Both records for mitra1 should have the same no_sk (0006 = max + 1)
        $this->assertEquals('0006', $updatedPenugasan3->no_sk);
        $this->assertEquals('0006', $updatedPenugasan4->no_sk);
        $this->assertEquals('2024-05-01', $updatedPenugasan3->tgl_sk->format('Y-m-d')); // Should be first day of month
        $this->assertEquals('2024-05-01', $updatedPenugasan4->tgl_sk->format('Y-m-d')); // Should be first day of month

        // Verify the updated records for mitra2
        $updatedPenugasan6 = Penugasan::find($penugasan6->id);

        // Record for mitra2 should have no_sk = 0007 (max + 2 for mitra2)
        $this->assertEquals('0007', $updatedPenugasan6->no_sk);
        $this->assertEquals('2024-05-01', $updatedPenugasan6->tgl_sk->format('Y-m-d')); // Should be first day of month

        // Verify that records with different bln_bayar are unchanged
        $unchangedPenugasan1 = Penugasan::find($penugasan1->id);
        $unchangedPenugasan2 = Penugasan::find($penugasan2->id);
        $unchangedPenugasan5 = Penugasan::find($penugasan5->id);

        $this->assertEquals('0001', $unchangedPenugasan1->no_sk);
        $this->assertEquals('0002', $unchangedPenugasan2->no_sk);
        $this->assertEquals('0005', $unchangedPenugasan5->no_sk);
    }

    public function test_bulk_update_bast_generates_correct_no_bast_and_tgl_bast()
    {
        // Create mitra
        $mitra1 = Mitra::factory()->create();
        $mitra2 = Mitra::factory()->create();

        // Create penugasan records with existing no_bast values for mitra1
        $penugasan1 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2024-05-01',
            'no_bast' => '0001',
            'tgl_bast' => '2024-04-01'
        ]);

        $penugasan2 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2024-06-01', // Different bln_bayar
            'no_bast' => '0002',
            'tgl_bast' => '2024-04-01'
        ]);

        // Create penugasan records for mitra1 with the target bln_bayar that should be updated
        $penugasan3 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2024-07-01', // This is our target bln_bayar
            'no_bast' => null,
            'tgl_bast' => null
        ]);

        $penugasan4 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2024-07-01', // Same target bln_bayar
            'no_bast' => null,
            'tgl_bast' => null
        ]);

        // Create penugasan records for mitra2
        $penugasan5 = Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2024-07-01', // Same target bln_bayar
            'no_bast' => '0005',
            'tgl_bast' => '2024-04-01'
        ]);

        $penugasan6 = Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2024-07-01', // Same target bln_bayar
            'no_bast' => null,
            'tgl_bast' => null
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/spk/bulk-update-bast', [
                'mitra_ids' => [$mitra1->id, $mitra2->id],
                'bln_bayar' => '2024-07', // Target bln_bayar
                'tgl_bast' => '2024-05-15' // Should be converted to first day of month
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'BAST records updated successfully'
            ]);

        // Verify the updated records for mitra1
        $updatedPenugasan3 = Penugasan::find($penugasan3->id);
        $updatedPenugasan4 = Penugasan::find($penugasan4->id);

        // Both records for mitra1 should have the same no_bast (0006 = max + 1)
        $this->assertEquals('0006', $updatedPenugasan3->no_bast);
        $this->assertEquals('0006', $updatedPenugasan4->no_bast);
        $this->assertEquals('2024-05-31', $updatedPenugasan3->tgl_bast->format('Y-m-d')); // Should be last day of month
        $this->assertEquals('2024-05-31', $updatedPenugasan4->tgl_bast->format('Y-m-d')); // Should be last day of month

        // Verify the updated records for mitra2
        $updatedPenugasan6 = Penugasan::find($penugasan6->id);

        // Record for mitra2 should have no_bast = 0007 (max + 2 for mitra2)
        $this->assertEquals('0007', $updatedPenugasan6->no_bast);
        $this->assertEquals('2024-05-31', $updatedPenugasan6->tgl_bast->format('Y-m-d')); // Should be last day of month

        // Verify that records with different bln_bayar are unchanged
        $unchangedPenugasan1 = Penugasan::find($penugasan1->id);
        $unchangedPenugasan2 = Penugasan::find($penugasan2->id);
        $unchangedPenugasan5 = Penugasan::find($penugasan5->id);

        $this->assertEquals('0001', $unchangedPenugasan1->no_bast);
        $this->assertEquals('0002', $unchangedPenugasan2->no_bast);
        $this->assertEquals('0005', $unchangedPenugasan5->no_bast);
    }
}
