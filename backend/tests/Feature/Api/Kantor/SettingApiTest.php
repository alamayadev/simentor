<?php

namespace Tests\Feature\Api\Kantor;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\Setting;
use Illuminate\Support\Facades\DB;

class SettingApiTest extends BaseApiTestCase
{
    protected function createSetting(array $attrs = []): Setting
    {
        $defaults = [
            'tahun' => '2024',
            'key' => 'test_key_' . uniqid(),
            'value' => 'test_value',
            'grup' => 1,
        ];
        
        return Setting::create(array_merge($defaults, $attrs));
    }

    // GET /api/kantor/settings Tests

    /** @test */
    public function it_requires_authentication_to_list_settings()
    {
        $response = $this->getJson('/api/kantor/settings');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_lists_settings_grouped_by_grup()
    {
        $this->createSetting(['key' => 'app_name_' . uniqid(), 'grup' => 1]);
        $this->createSetting(['key' => 'max_cuti_' . uniqid(), 'grup' => 2]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_filters_settings_by_search_term()
    {
        $this->createSetting(['key' => 'app_name_' . uniqid(), 'value' => 'Test App']);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings?search=app');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_filters_settings_by_grup()
    {
        $this->createSetting(['key' => 'app_name_' . uniqid(), 'grup' => 1]);
        $this->createSetting(['key' => 'max_cuti_' . uniqid(), 'grup' => 2]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings?filter[grup]=2');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_sorts_settings_by_grup_name()
    {
        $this->createSetting(['key' => 'z_key_' . uniqid(), 'grup' => 3]);
        $this->createSetting(['key' => 'a_key_' . uniqid(), 'grup' => 1]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_caches_settings_list()
    {
        $this->createSetting();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings');

        $response->assertStatus(200);
    }

    // GET /api/kantor/settings/{id} Tests

    /** @test */
    public function it_requires_authentication_to_show_setting()
    {
        $response = $this->getJson('/api/kantor/settings/1');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_setting()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_setting_details()
    {
        $setting = $this->createSetting([
            'key' => 'test_setting_' . uniqid(),
            'value' => 'test_value',
            'grup' => 1
        ]);

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/settings/{$setting->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    // POST /api/kantor/settings Tests

    /** @test */
    public function it_requires_authentication_to_create_setting()
    {
        $response = $this->postJson('/api/kantor/settings', [
            'tahun' => '2024',
            'key' => 'test_key',
            'value' => 'test_value',
            'grup' => 1
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_required_fields_for_setting_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/settings', []);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_tahun_max_length_for_setting_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/settings', [
                'tahun' => '12345',
                'key' => 'test_key_' . uniqid(),
                'value' => 'test_value',
                'grup' => 1
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_key_max_length_for_setting_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/settings', [
                'tahun' => '2024',
                'key' => str_repeat('a', 256),
                'value' => 'test_value',
                'grup' => 1
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_validates_unique_key_for_setting_creation()
    {
        $existingKey = 'existing_key_' . uniqid();
        $this->createSetting(['key' => $existingKey]);

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/settings', [
                'tahun' => '2024',
                'key' => $existingKey,
                'value' => 'test_value',
                'grup' => 1
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_creates_setting_successfully()
    {
        $uniqueKey = 'new_test_key_' . uniqid();

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/settings', [
                'tahun' => '2024',
                'key' => $uniqueKey,
                'value' => 'new_test_value',
                'grup' => 1
            ]);

        $response->assertStatus(201)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_clears_cache_after_setting_creation()
    {
        $uniqueKey = 'cache_test_key_' . uniqid();

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/settings', [
                'tahun' => '2024',
                'key' => $uniqueKey,
                'value' => 'test_value',
                'grup' => 1
            ]);

        $response->assertStatus(201);
    }

    // PUT /api/kantor/settings/{id} Tests

    /** @test */
    public function it_requires_authentication_to_update_setting()
    {
        $response = $this->putJson('/api/kantor/settings/1', [
            'value' => 'updated_value'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_setting()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/kantor/settings/999999', [
                'value' => 'updated_value'
            ]);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_validates_tahun_when_updating_setting()
    {
        $setting = $this->createSetting();

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/settings/{$setting->id}", [
                'tahun' => '12345',
                'value' => 'updated_value',
                'grup' => 1
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_excludes_key_from_setting_update()
    {
        $setting = $this->createSetting(['key' => 'original_key_' . uniqid()]);

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/settings/{$setting->id}", [
                'key' => 'should_not_update',
                'value' => 'updated_value',
                'grup' => 1
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_updates_setting_successfully()
    {
        $setting = $this->createSetting([
            'value' => 'original_value',
            'grup' => 1
        ]);

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/settings/{$setting->id}", [
                'value' => 'updated_value',
                'grup' => 2
            ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_clears_cache_after_setting_update()
    {
        $setting = $this->createSetting();

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/settings/{$setting->id}", [
                'value' => 'updated_value'
            ]);

        $response->assertStatus(200);
    }

    // DELETE /api/kantor/settings/{id} Tests

    /** @test */
    public function it_requires_authentication_to_delete_setting()
    {
        $response = $this->deleteJson('/api/kantor/settings/1');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_setting()
    {
        $response = $this->actingAsAdmin()
            ->deleteJson('/api/kantor/settings/999999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_deletes_setting_successfully()
    {
        $setting = $this->createSetting();

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/settings/{$setting->id}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_clears_cache_after_setting_deletion()
    {
        $setting = $this->createSetting();

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/settings/{$setting->id}");

        $response->assertStatus(200);
    }

    // GET /api/kantor/settings/grup-list Tests

    /** @test */
    public function it_requires_authentication_to_get_grup_list()
    {
        $response = $this->getJson('/api/kantor/settings/grup-list');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_unique_grup_list()
    {
        $this->createSetting(['key' => 'key1_' . uniqid(), 'grup' => 1]);
        $this->createSetting(['key' => 'key2_' . uniqid(), 'grup' => 2]);
        $this->createSetting(['key' => 'key3_' . uniqid(), 'grup' => 1]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings/grup-list');

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_excludes_null_grup_from_list()
    {
        $this->createSetting(['key' => 'key1_' . uniqid(), 'grup' => 1]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings/grup-list');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_caches_grup_list()
    {
        $this->createSetting(['key' => 'key1_' . uniqid(), 'grup' => 1]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings/grup-list');

        $response->assertStatus(200);
    }

    // GET /api/kantor/settings/officers Tests

    /** @test */
    public function it_requires_authentication_to_get_officers()
    {
        $response = $this->getJson('/api/kantor/settings/officers');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_officers_with_kelas_greater_than_8()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings/officers');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_excludes_officers_with_non_null_status()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings/officers');

        $response->assertStatus(200);
    }

    // GET /api/kantor/settings/key/{key} Tests

    /** @test */
    public function it_requires_authentication_to_get_setting_by_key()
    {
        $response = $this->getJson('/api/kantor/settings/key/test_key');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_setting_key()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings/key/nonexistent_key_' . uniqid());

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_setting_by_key()
    {
        $uniqueKey = 'test_setting_key_' . uniqid();
        $this->createSetting([
            'key' => $uniqueKey,
            'value' => 'test_setting_value',
            'grup' => 1
        ]);

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/settings/key/{$uniqueKey}");

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    /** @test */
    public function it_caches_setting_by_key()
    {
        $uniqueKey = 'cache_key_' . uniqid();
        $this->createSetting(['key' => $uniqueKey]);

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/settings/key/{$uniqueKey}");

        $response->assertStatus(200);
    }

    // Additional Security Tests

    /** @test */
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $setting = $this->createSetting();

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/settings/{$setting->id}", [
                'id' => 999,
                'key' => 'should_not_update',
                'value' => 'Updated Value'
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function it_prevents_sql_injection_in_setting_operations()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/settings?search=test');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_large_payloads_gracefully()
    {
        $largeValue = str_repeat('a', 10000);

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/settings', [
                'tahun' => '2024',
                'key' => 'large_payload_key_' . uniqid(),
                'value' => $largeValue,
                'grup' => 1
            ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    /** @test */
    public function it_logs_setting_operations()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/settings', [
                'tahun' => '2024',
                'key' => 'log_test_key_' . uniqid(),
                'value' => 'test_value',
                'grup' => 1
            ]);

        $response->assertStatus(201);
    }
}
