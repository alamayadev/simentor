<?php

namespace Tests\Feature\Kantor;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Penugasan;
use App\Models\Mitra;
use Carbon\Carbon;

class SpkApiSequentialNumberingTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
    }

    public function test_bulk_update_spk_resets_sequence_on_new_year_after_max_9999()
    {
        Carbon::setTestNow('2025-12-31 10:00:00');

        $mitras = Mitra::factory()->count(5)->create()->values();

        Penugasan::factory()->create([
            'mitra_id' => $mitras[0]->id,
            'bln_bayar' => '2025-12-01',
            'no_sk' => '9999',
        ]);

        $targets = $mitras->map(function ($mitra) {
            return Penugasan::factory()->create([
                'mitra_id' => $mitra->id,
                'bln_bayar' => '2026-02-01',
                'no_sk' => null,
                'tgl_sk' => null,
            ]);
        })->values();

        Carbon::setTestNow('2026-01-10 08:00:00');

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/spk/bulk-update', [
                'mitra_ids' => $mitras->pluck('id')->all(),
                'bln_bayar' => '2026-02',
                'tgl_sk' => '2026-02-15',
            ]);

        $response->assertStatus(200);

        $expectedNumbers = ['0001', '0002', '0003', '0004', '0005'];
        foreach ($targets as $index => $penugasan) {
            $penugasan->refresh();
            $this->assertEquals($expectedNumbers[$index], $penugasan->no_sk);
            $this->assertEquals('2026-02-02', $penugasan->tgl_sk->format('Y-m-d'));
        }

        Carbon::setTestNow();
    }

    public function test_bulk_update_bast_resets_sequence_on_new_year_after_max_9999()
    {
        Carbon::setTestNow('2025-12-31 10:00:00');

        $mitras = Mitra::factory()->count(5)->create()->values();

        Penugasan::factory()->create([
            'mitra_id' => $mitras[0]->id,
            'bln_bayar' => '2025-12-01',
            'no_bast' => '9999',
        ]);

        $targets = $mitras->map(function ($mitra) {
            return Penugasan::factory()->create([
                'mitra_id' => $mitra->id,
                'bln_bayar' => '2026-02-01',
                'no_bast' => null,
                'tgl_bast' => null,
            ]);
        })->values();

        Carbon::setTestNow('2026-01-10 08:00:00');

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/spk/bulk-update-bast', [
                'mitra_ids' => $mitras->pluck('id')->all(),
                'bln_bayar' => '2026-02',
                'tgl_bast' => '2026-02-15',
            ]);

        $response->assertStatus(200);

        $expectedNumbers = ['0001', '0002', '0003', '0004', '0005'];
        foreach ($targets as $index => $penugasan) {
            $penugasan->refresh();
            $this->assertEquals($expectedNumbers[$index], $penugasan->no_bast);
            $this->assertEquals('2026-02-27', $penugasan->tgl_bast->format('Y-m-d'));
        }

        Carbon::setTestNow();
    }

    public function test_bulk_update_spk_assigns_sequential_numbers_when_max_year_less_than_current_year()
    {
        Carbon::setTestNow('2025-12-31 10:00:00');

        $mitra1 = Mitra::factory()->create();
        $mitra2 = Mitra::factory()->create();
        $mitra3 = Mitra::factory()->create();
        $mitra4 = Mitra::factory()->create();
        $mitra5 = Mitra::factory()->create();

        Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2025-01-01',
            'no_sk' => '9999',
        ]);

        $targetPenugasan1 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2026-01-01',
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $targetPenugasan2 = Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2026-01-01',
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $targetPenugasan3 = Penugasan::factory()->create([
            'mitra_id' => $mitra3->id,
            'bln_bayar' => '2026-01-01',
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $targetPenugasan4 = Penugasan::factory()->create([
            'mitra_id' => $mitra4->id,
            'bln_bayar' => '2026-01-01',
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $targetPenugasan5 = Penugasan::factory()->create([
            'mitra_id' => $mitra5->id,
            'bln_bayar' => '2026-01-01',
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        Carbon::setTestNow('2026-01-10 08:00:00');

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/spk/bulk-update', [
                'mitra_ids' => [$mitra1->id, $mitra2->id, $mitra3->id, $mitra4->id, $mitra5->id],
                'bln_bayar' => '2026-01',
                'tgl_sk' => '2026-01-15'
            ]);

        $response->assertStatus(200);

        $targetPenugasan1->refresh();
        $targetPenugasan2->refresh();
        $targetPenugasan3->refresh();
        $targetPenugasan4->refresh();
        $targetPenugasan5->refresh();

        $this->assertEquals('0001', $targetPenugasan1->no_sk);
        $this->assertEquals('0002', $targetPenugasan2->no_sk);
        $this->assertEquals('0003', $targetPenugasan3->no_sk);
        $this->assertEquals('0004', $targetPenugasan4->no_sk);
        $this->assertEquals('0005', $targetPenugasan5->no_sk);

        $this->assertEquals('2026-01-01', $targetPenugasan1->tgl_sk->format('Y-m-d'));
        $this->assertEquals('2026-01-01', $targetPenugasan2->tgl_sk->format('Y-m-d'));
        $this->assertEquals('2026-01-01', $targetPenugasan3->tgl_sk->format('Y-m-d'));
        $this->assertEquals('2026-01-01', $targetPenugasan4->tgl_sk->format('Y-m-d'));
        $this->assertEquals('2026-01-01', $targetPenugasan5->tgl_sk->format('Y-m-d'));

        Carbon::setTestNow();
    }

    public function test_bulk_update_bast_assigns_sequential_numbers_when_max_year_less_than_current_year()
    {
        Carbon::setTestNow('2025-12-31 10:00:00');

        $mitra1 = Mitra::factory()->create();
        $mitra2 = Mitra::factory()->create();
        $mitra3 = Mitra::factory()->create();
        $mitra4 = Mitra::factory()->create();
        $mitra5 = Mitra::factory()->create();

        Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2025-01-01',
            'no_bast' => '9999',
        ]);

        $targetPenugasan1 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2026-01-01',
            'no_bast' => null,
            'tgl_bast' => null
        ]);

        $targetPenugasan2 = Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2026-01-01',
            'no_bast' => null,
            'tgl_bast' => null
        ]);

        $targetPenugasan3 = Penugasan::factory()->create([
            'mitra_id' => $mitra3->id,
            'bln_bayar' => '2026-01-01',
            'no_bast' => null,
            'tgl_bast' => null
        ]);

        $targetPenugasan4 = Penugasan::factory()->create([
            'mitra_id' => $mitra4->id,
            'bln_bayar' => '2026-01-01',
            'no_bast' => null,
            'tgl_bast' => null
        ]);

        $targetPenugasan5 = Penugasan::factory()->create([
            'mitra_id' => $mitra5->id,
            'bln_bayar' => '2026-01-01',
            'no_bast' => null,
            'tgl_bast' => null
        ]);

        Carbon::setTestNow('2026-01-10 08:00:00');

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/spk/bulk-update-bast', [
                'mitra_ids' => [$mitra1->id, $mitra2->id, $mitra3->id, $mitra4->id, $mitra5->id],
                'bln_bayar' => '2026-01',
                'tgl_bast' => '2026-01-15'
            ]);

        $response->assertStatus(200);

        $targetPenugasan1->refresh();
        $targetPenugasan2->refresh();
        $targetPenugasan3->refresh();
        $targetPenugasan4->refresh();
        $targetPenugasan5->refresh();

        $this->assertEquals('0001', $targetPenugasan1->no_bast);
        $this->assertEquals('0002', $targetPenugasan2->no_bast);
        $this->assertEquals('0003', $targetPenugasan3->no_bast);
        $this->assertEquals('0004', $targetPenugasan4->no_bast);
        $this->assertEquals('0005', $targetPenugasan5->no_bast);

        $this->assertEquals('2026-01-30', $targetPenugasan1->tgl_bast->format('Y-m-d'));
        $this->assertEquals('2026-01-30', $targetPenugasan2->tgl_bast->format('Y-m-d'));
        $this->assertEquals('2026-01-30', $targetPenugasan3->tgl_bast->format('Y-m-d'));
        $this->assertEquals('2026-01-30', $targetPenugasan4->tgl_bast->format('Y-m-d'));
        $this->assertEquals('2026-01-30', $targetPenugasan5->tgl_bast->format('Y-m-d'));

        Carbon::setTestNow();
    }

    public function test_bulk_update_spk_assigns_sequential_numbers_when_max_year_equals_current_year()
    {
        Carbon::setTestNow('2026-01-01 10:00:00');

        $mitra1 = Mitra::factory()->create();
        $mitra2 = Mitra::factory()->create();
        $mitra3 = Mitra::factory()->create();

        Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2026-01-01',
            'no_sk' => '0010',
        ]);

        $targetPenugasan1 = Penugasan::factory()->create([
            'mitra_id' => $mitra1->id,
            'bln_bayar' => '2026-02-01',
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $targetPenugasan2 = Penugasan::factory()->create([
            'mitra_id' => $mitra2->id,
            'bln_bayar' => '2026-02-01',
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $targetPenugasan3 = Penugasan::factory()->create([
            'mitra_id' => $mitra3->id,
            'bln_bayar' => '2026-02-01',
            'no_sk' => null,
            'tgl_sk' => null
        ]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson('/api/kantor/spk/bulk-update', [
                'mitra_ids' => [$mitra1->id, $mitra2->id, $mitra3->id],
                'bln_bayar' => '2026-02',
                'tgl_sk' => '2026-02-15'
            ]);

        $response->assertStatus(200);

        $targetPenugasan1->refresh();
        $targetPenugasan2->refresh();
        $targetPenugasan3->refresh();

        $this->assertEquals('0011', $targetPenugasan1->no_sk);
        $this->assertEquals('0012', $targetPenugasan2->no_sk);
        $this->assertEquals('0013', $targetPenugasan3->no_sk);

        $this->assertEquals('2026-02-02', $targetPenugasan1->tgl_sk->format('Y-m-d'));
        $this->assertEquals('2026-02-02', $targetPenugasan2->tgl_sk->format('Y-m-d'));
        $this->assertEquals('2026-02-02', $targetPenugasan3->tgl_sk->format('Y-m-d'));

        Carbon::setTestNow();
    }
}
