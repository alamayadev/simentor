<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\PetaSls;
use App\Models\TargetPeta;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TargetPetaRelationshipTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_peta_sls_can_have_a_target_peta()
    {
        // Create a PetaSls record
        $petaSls = PetaSls::factory()->create([
            'filename' => 'test_file.geojson'
        ]);

        // Create a TargetPeta record with the same filename
        $targetPeta = TargetPeta::factory()->create([
            'filename' => 'test_file.geojson',
            'keterangan' => 'Test keterangan'
        ]);

        // Assert that the relationship works
        $this->assertTrue(isset($petaSls->targetPeta));
        $this->assertEquals($targetPeta->id, $petaSls->targetPeta->id);
        $this->assertEquals('Test keterangan', $petaSls->targetPeta->keterangan);
    }

    /** @test */
    public function a_target_peta_belongs_to_a_peta_sls()
    {
        // Create a PetaSls record
        $petaSls = PetaSls::factory()->create([
            'filename' => 'test_file2.geojson'
        ]);

        // Create a TargetPeta record with the same filename
        $targetPeta = TargetPeta::factory()->create([
            'filename' => 'test_file2.geojson',
            'keterangan' => 'Another test keterangan'
        ]);

        // Assert that the inverse relationship works
        $this->assertTrue(isset($targetPeta->petaSls));
        $this->assertEquals($petaSls->id, $targetPeta->petaSls->id);
        $this->assertEquals('test_file2.geojson', $targetPeta->petaSls->filename);
    }
}
