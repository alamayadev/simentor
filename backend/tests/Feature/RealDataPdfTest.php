<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RealDataPdfTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function generate_pdf_from_existing_data()
    {
        // Skip this test as it requires production data to exist
        $this->markTestSkipped('This test requires existing production data and cannot run in parallel test environment.');
    }
}
