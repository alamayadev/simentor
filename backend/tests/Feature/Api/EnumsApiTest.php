<?php

namespace Tests\Feature\Api;

use App\Enums\FungsiType;
use App\Enums\JabatanTugasType;
use App\Enums\JenisKegiatanType;
use App\Enums\SatuanType;
use App\Enums\PangkatType;
use App\Enums\GolonganType;
use App\Enums\JabatanType;

class EnumsApiTest extends BaseApiTestCase
{
    // ==================== GET /api/enums (index) ====================

    public function test_unauthenticated_user_cannot_get_all_enums()
    {
        $response = $this->getJson('/api/enums');

        $this->assertUnauthorized($response);
    }

    public function test_authenticated_user_can_get_all_enums()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $this->assertStandardApiResponse($response, 200, true);

        $response->assertJsonStructure([
            'success',
            'message',
            'data' => [
                'fungsi_types',
                'jabatan_tugas_types',
                'jenis_kegiatan_types',
                'satuan_types',
                'pangkat_types',
                'golongan_types',
                'jabatan_types',
            ]
        ]);
    }

    public function test_all_enums_includes_fungsi_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $response->assertJsonPath('data.fungsi_types', function ($value) {
            return is_array($value) && count($value) > 0;
        });
    }

    public function test_all_enums_includes_jabatan_tugas_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $response->assertJsonPath('data.jabatan_tugas_types', function ($value) {
            return is_array($value) && count($value) > 0;
        });
    }

    public function test_all_enums_includes_jenis_kegiatan_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $response->assertJsonPath('data.jenis_kegiatan_types', function ($value) {
            return is_array($value) && count($value) > 0;
        });
    }

    public function test_all_enums_includes_satuan_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $response->assertJsonPath('data.satuan_types', function ($value) {
            return is_array($value) && count($value) > 0;
        });
    }

    public function test_all_enums_includes_pangkat_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $response->assertJsonPath('data.pangkat_types', function ($value) {
            return is_array($value) && count($value) > 0;
        });
    }

    public function test_all_enums_includes_golongan_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $response->assertJsonPath('data.golongan_types', function ($value) {
            return is_array($value) && count($value) > 0;
        });
    }

    public function test_all_enums_includes_jabatan_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $response->assertJsonPath('data.jabatan_types', function ($value) {
            return is_array($value) && count($value) > 0;
        });
    }

    public function test_all_enums_fungsi_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $expectedFungsiTypes = array_column(FungsiType::cases(), 'value');
        $response->assertJsonPath('data.fungsi_types', function ($value) use ($expectedFungsiTypes) {
            return is_array($value) && $value === $expectedFungsiTypes;
        });
    }

    public function test_all_enums_jabatan_tugas_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $expectedTypes = array_column(JabatanTugasType::cases(), 'value');
        $response->assertJsonPath('data.jabatan_tugas_types', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_all_enums_jenis_kegiatan_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $expectedTypes = array_column(JenisKegiatanType::cases(), 'value');
        $response->assertJsonPath('data.jenis_kegiatan_types', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_all_enums_satuan_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $expectedTypes = array_column(SatuanType::cases(), 'value');
        $response->assertJsonPath('data.satuan_types', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_all_enums_pangkat_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $expectedTypes = array_column(PangkatType::cases(), 'value');
        $response->assertJsonPath('data.pangkat_types', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_all_enums_golongan_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $expectedTypes = array_column(GolonganType::cases(), 'value');
        $response->assertJsonPath('data.golongan_types', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_all_enums_jabatan_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $expectedTypes = array_column(JabatanType::cases(), 'value');
        $response->assertJsonPath('data.jabatan_types', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_all_enums_response_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums');

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
                    'jabatan_types',
                ]
            ]);
    }

    public function test_all_enums_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAsUser()
            ->getJson('/api/enums');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000; // Convert to milliseconds

        $this->assertLessThan(200, $responseTime, 'Response time should be less than 200ms');
        $response->assertStatus(200);
    }

    // ==================== GET /api/enums/fungsi-types ====================

    public function test_unauthenticated_user_cannot_get_fungsi_types()
    {
        $response = $this->getJson('/api/enums/fungsi-types');

        $this->assertUnauthorized($response);
    }

    public function test_authenticated_user_can_get_fungsi_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/fungsi-types');

        $this->assertStandardApiResponse($response, 200, true);
    }

    public function test_fungsi_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/fungsi-types');

        $expectedTypes = array_column(FungsiType::cases(), 'value');
        $response->assertJsonPath('data', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_fungsi_types_response_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/fungsi-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_fungsi_types_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAsUser()
            ->getJson('/api/enums/fungsi-types');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Response time should be less than 200ms');
        $response->assertStatus(200);
    }

    // ==================== GET /api/enums/jabatan-tugas-types ====================

    public function test_unauthenticated_user_cannot_get_jabatan_tugas_types()
    {
        $response = $this->getJson('/api/enums/jabatan-tugas-types');

        $this->assertUnauthorized($response);
    }

    public function test_authenticated_user_can_get_jabatan_tugas_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jabatan-tugas-types');

        $this->assertStandardApiResponse($response, 200, true);
    }

    public function test_jabatan_tugas_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jabatan-tugas-types');

        $expectedTypes = array_column(JabatanTugasType::cases(), 'value');
        $response->assertJsonPath('data', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_jabatan_tugas_types_response_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jabatan-tugas-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_jabatan_tugas_types_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAsUser()
            ->getJson('/api/enums/jabatan-tugas-types');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Response time should be less than 200ms');
        $response->assertStatus(200);
    }

    // ==================== GET /api/enums/jenis-kegiatan-types ====================

    public function test_unauthenticated_user_cannot_get_jenis_kegiatan_types()
    {
        $response = $this->getJson('/api/enums/jenis-kegiatan-types');

        $this->assertUnauthorized($response);
    }

    public function test_authenticated_user_can_get_jenis_kegiatan_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jenis-kegiatan-types');

        $this->assertStandardApiResponse($response, 200, true);
    }

    public function test_jenis_kegiatan_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jenis-kegiatan-types');

        $expectedTypes = array_column(JenisKegiatanType::cases(), 'value');
        $response->assertJsonPath('data', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_jenis_kegiatan_types_response_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jenis-kegiatan-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_jenis_kegiatan_types_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAsUser()
            ->getJson('/api/enums/jenis-kegiatan-types');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Response time should be less than 200ms');
        $response->assertStatus(200);
    }

    // ==================== GET /api/enums/satuan-types ====================

    public function test_unauthenticated_user_cannot_get_satuan_types()
    {
        $response = $this->getJson('/api/enums/satuan-types');

        $this->assertUnauthorized($response);
    }

    public function test_authenticated_user_can_get_satuan_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/satuan-types');

        $this->assertStandardApiResponse($response, 200, true);
    }

    public function test_satuan_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/satuan-types');

        $expectedTypes = array_column(SatuanType::cases(), 'value');
        $response->assertJsonPath('data', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_satuan_types_response_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/satuan-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_satuan_types_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAsUser()
            ->getJson('/api/enums/satuan-types');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Response time should be less than 200ms');
        $response->assertStatus(200);
    }

    // ==================== GET /api/enums/pangkat-types ====================

    public function test_unauthenticated_user_cannot_get_pangkat_types()
    {
        $response = $this->getJson('/api/enums/pangkat-types');

        $this->assertUnauthorized($response);
    }

    public function test_authenticated_user_can_get_pangkat_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/pangkat-types');

        $this->assertStandardApiResponse($response, 200, true);
    }

    public function test_pangkat_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/pangkat-types');

        $expectedTypes = array_column(PangkatType::cases(), 'value');
        $response->assertJsonPath('data', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_pangkat_types_response_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/pangkat-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_pangkat_types_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAsUser()
            ->getJson('/api/enums/pangkat-types');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Response time should be less than 200ms');
        $response->assertStatus(200);
    }

    // ==================== GET /api/enums/golongan-types ====================

    public function test_unauthenticated_user_cannot_get_golongan_types()
    {
        $response = $this->getJson('/api/enums/golongan-types');

        $this->assertUnauthorized($response);
    }

    public function test_authenticated_user_can_get_golongan_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/golongan-types');

        $this->assertStandardApiResponse($response, 200, true);
    }

    public function test_golongan_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/golongan-types');

        $expectedTypes = array_column(GolonganType::cases(), 'value');
        $response->assertJsonPath('data', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_golongan_types_response_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/golongan-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_golongan_types_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAsUser()
            ->getJson('/api/enums/golongan-types');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Response time should be less than 200ms');
        $response->assertStatus(200);
    }

    // ==================== GET /api/enums/jabatan-types ====================

    public function test_unauthenticated_user_cannot_get_jabatan_types()
    {
        $response = $this->getJson('/api/enums/jabatan-types');

        $this->assertUnauthorized($response);
    }

    public function test_authenticated_user_can_get_jabatan_types()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jabatan-types');

        $this->assertStandardApiResponse($response, 200, true);
    }

    public function test_jabatan_types_correct_values()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jabatan-types');

        $expectedTypes = array_column(JabatanType::cases(), 'value');
        $response->assertJsonPath('data', function ($value) use ($expectedTypes) {
            return is_array($value) && $value === $expectedTypes;
        });
    }

    public function test_jabatan_types_response_structure()
    {
        $response = $this->actingAsUser()
            ->getJson('/api/enums/jabatan-types');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
            ]);
    }

    public function test_jabatan_types_performance()
    {
        $startTime = microtime(true);

        $response = $this->actingAsUser()
            ->getJson('/api/enums/jabatan-types');

        $endTime = microtime(true);
        $responseTime = ($endTime - $startTime) * 1000;

        $this->assertLessThan(200, $responseTime, 'Response time should be less than 200ms');
        $response->assertStatus(200);
    }
}
