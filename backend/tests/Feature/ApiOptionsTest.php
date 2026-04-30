<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ApiOptionsTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => 'password'
        ]);
    }

    /** @test */
    public function test_keluhan_options_endpoint_returns_correct_data()
    {
        // First, authenticate the user - login returns token under data.token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        $this->assertNotEmpty($token, 'Token should not be empty');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data'
            ]);

        // Check that the response contains the expected keluhan options
        $response->assertJsonFragment(['success' => true]);
        $response->assertJsonFragment(['message' => 'Options retrieved successfully']);

        $data = $response->json('data');
        $this->assertIsArray($data);

        // Check that all expected options are present
        $expectedOptions = ['Sistem', 'Software', 'Printer', 'Hardware PC/Laptop', 'Jaringan', 'Akun BPS', 'Lainnya'];
        foreach ($expectedOptions as $option) {
            $this->assertContains($option, $data);
        }
    }

    /** @test */
    public function test_enums_endpoint_returns_all_enum_types()
    {
        // First, authenticate the user - login returns token under data.token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        $this->assertNotEmpty($token, 'Token should not be empty');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/enums');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'fungsi_types',
                    'jabatan_tugas_types',
                    'jenis_kegiatan_types',
                    'satuan_types',
                    'pangkat_types',
                    'golongan_types',
                    'jabatan_types'
                ]
            ]);
    }

    /** @test */
    public function test_kegiatan_form_options_endpoint()
    {
        // First, authenticate the user - login returns token under data.token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        $this->assertNotEmpty($token, 'Token should not be empty');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/kantor/kegiatan/form-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'satuan',
                    'fungsi',
                    'jenis_kegiatan'
                ]
            ]);
    }

    /** @test */
    public function test_penugasan_form_options_endpoint()
    {
        // First, authenticate the user - login returns token under data.token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        $this->assertNotEmpty($token, 'Token should not be empty');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/kantor/penugasan/form-options');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'jabatanTugasOptions',
                    'fungsiOptions'
                ]
            ]);
    }

    /** @test */
    public function test_login_endpoint_works_correctly()
    {
        $response = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'email'
                    ],
                    'token'
                ]
            ]);
    }

    /** @test */
    public function test_keluhan_options_includes_lainnya()
    {
        // First, authenticate the user - login returns token under data.token
        $loginResponse = $this->postJson('/api/login', [
            'email' => 'test@example.com',
            'password' => 'password'
        ]);

        $loginResponse->assertStatus(200);
        $token = $loginResponse->json('data.token');

        $this->assertNotEmpty($token, 'Token should not be empty');

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/ipds/tikets/keluhan-options');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertIsArray($data, 'Data should be an array');

        $this->assertContains('Lainnya', $data, 'Keluhan options should include "Lainnya"');
        $this->assertCount(7, $data, 'Should have exactly 7 keluhan options');
    }
}
