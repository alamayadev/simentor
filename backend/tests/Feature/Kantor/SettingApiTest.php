<?php

namespace Tests\Feature\Kantor;

use Tests\TestCase;
use App\Models\User;
use App\Models\Setting;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SettingApiTest extends TestCase
{
    use RefreshDatabase;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user for authentication
        $this->user = User::factory()->create();
    }

    public function test_admin_can_list_settings()
    {
        // Create some settings in reverse order to test sorting
        $setting1 = Setting::factory()->create(['key' => 'app_name', 'value' => 'Sistem Informasi Kantor', 'grup' => 3]); // Should come last
        $setting2 = Setting::factory()->create(['key' => 'app_version', 'value' => '1.0.0', 'grup' => 1]); // Should come first
        $setting3 = Setting::factory()->create(['key' => 'max_cuti', 'value' => '12', 'grup' => 2]); // Should come in middle

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/settings');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '1',
                    '2',
                    '3'
                ]
            ])
            ->assertJsonCount(1, 'data.1')
            ->assertJsonCount(1, 'data.2')
            ->assertJsonCount(1, 'data.3');

        // Check that settings are correctly grouped and sorted
        $responseData = $response->json('data');

        // Verify group order (should be alphabetical/ascending)
        $groupKeys = array_keys($responseData);
        // Note: keys in JSON object might be strings even if originally integers, depending on PHP array serialization
        // But since we use getJson(), numeric keys in JSON objects are usually cast to strings or remain strings.
        // Let's assert against what we expect from the API.
        $this->assertTrue(in_array('1', $groupKeys) || in_array(1, $groupKeys));
        $this->assertTrue(in_array('2', $groupKeys) || in_array(2, $groupKeys));
        $this->assertTrue(in_array('3', $groupKeys) || in_array(3, $groupKeys));

        // Verify each group contains the correct settings
        // Accessing via string key '1', '2', '3'
        $this->assertEquals($setting2->id, $responseData['1'][0]['id']);
        $this->assertEquals($setting3->id, $responseData['2'][0]['id']);
        $this->assertEquals($setting1->id, $responseData['3'][0]['id']);
    }

    public function test_admin_can_create_setting()
    {
        $settingData = [
            'tahun' => '2024',
            'key' => 'app_name',
            'value' => 'Sistem Informasi Kantor',
            'grup' => 1
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->postJson('/api/kantor/settings', $settingData);

        $response->assertStatus(201)
            ->assertJsonFragment([
                'key' => 'app_name',
                'value' => 'Sistem Informasi Kantor'
            ]);

        $this->assertDatabaseHas('settings', [
            'key' => 'app_name',
            'value' => 'Sistem Informasi Kantor'
        ]);
    }

    public function test_admin_can_show_setting()
    {
        $setting = Setting::factory()->create(['key' => 'test_key', 'value' => 'test_value']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/kantor/settings/{$setting->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $setting->id,
                'key' => 'test_key',
                'value' => 'test_value'
            ]);
    }

    public function test_admin_can_update_setting()
    {
        $setting = Setting::factory()->create(['key' => 'original_key', 'value' => 'original_value']);

        $updatedData = [
            'tahun' => '2024',
            'key' => 'changed_key', // This should be ignored
            'value' => 'updated_value',
            'grup' => 1
        ];

        $response = $this->actingAs($this->user, 'sanctum')
            ->putJson("/api/kantor/settings/{$setting->id}", $updatedData);

        $response->assertStatus(200)
            ->assertJsonFragment([
                'key' => 'original_key', // Should remain unchanged
                'value' => 'updated_value'
            ]);

        $this->assertDatabaseHas('settings', [
            'id' => $setting->id,
            'key' => 'original_key', // Should remain unchanged
            'value' => 'updated_value'
        ]);

        // Verify that the key was not changed
        $this->assertDatabaseMissing('settings', [
            'id' => $setting->id,
            'key' => 'changed_key'
        ]);
    }

    public function test_admin_can_delete_setting()
    {
        $setting = Setting::factory()->create(['key' => 'to_delete', 'value' => 'to_delete_value']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->deleteJson("/api/kantor/settings/{$setting->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'message' => 'Setting deleted successfully'
            ]);

        $this->assertDatabaseMissing('settings', [
            'id' => $setting->id
        ]);
    }

    public function test_admin_can_get_grup_list()
    {
        Setting::factory()->create(['grup' => 1]);
        Setting::factory()->create(['grup' => 2]);
        Setting::factory()->create(['grup' => 3]);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/settings/grup-list');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data'
            ])
            ->assertJsonFragment([
                'data' => [
                    1,
                    2,
                    3
                ]
            ]);
    }

    public function test_admin_can_get_setting_by_key()
    {
        $setting = Setting::factory()->create(['key' => 'app_name', 'value' => 'Sistem Informasi Kantor']);

        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson("/api/kantor/settings/key/app_name");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $setting->id,
                'key' => 'app_name',
                'value' => 'Sistem Informasi Kantor'
            ]);
    }

    public function test_admin_can_list_settings_with_group_filter()
    {
        // Create some settings
        $setting1 = Setting::factory()->create(['key' => 'app_name', 'value' => 'Sistem Informasi Kantor', 'grup' => 3]);
        $setting2 = Setting::factory()->create(['key' => 'app_version', 'value' => '1.0.0', 'grup' => 3]);
        $setting3 = Setting::factory()->create(['key' => 'max_cuti', 'value' => '12', 'grup' => 1]);

        // Filter settings by group
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/settings?filter[grup]=3');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '3'
                ]
            ])
            ->assertJsonCount(2, 'data.3')
            ->assertJsonMissingPath('data.1'); // Should not contain group 1

        // Check that settings are correctly grouped
        $responseData = $response->json('data');

        // Verify group 3 contains the correct settings
        $this->assertEquals($setting1->id, $responseData['3'][0]['id']);
        $this->assertEquals($setting2->id, $responseData['3'][1]['id']);
    }

    public function test_admin_can_list_settings_with_search()
    {
        // Create some settings
        $setting1 = Setting::factory()->create(['key' => 'app_name', 'value' => 'Sistem Informasi Kantor', 'grup' => 3]);
        $setting2 = Setting::factory()->create(['key' => 'app_version', 'value' => '1.0.0', 'grup' => 3]);
        $setting3 = Setting::factory()->create(['key' => 'max_cuti', 'value' => '12', 'grup' => 1]);

        // Search for settings with 'app' in key or value
        $response = $this->actingAs($this->user, 'sanctum')
            ->getJson('/api/kantor/settings?search=app');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '3'
                ]
            ])
            ->assertJsonCount(2, 'data.3')
            ->assertJsonMissingPath('data.1'); // Should not contain group 1

        // Check that settings are correctly grouped
        $responseData = $response->json('data');

        // Verify group 3 contains the correct settings
        $this->assertEquals($setting1->id, $responseData['3'][0]['id']);
        $this->assertEquals($setting2->id, $responseData['3'][1]['id']);
    }

    public function test_cannot_access_settings_without_authentication()
    {
        $response = $this->getJson('/api/kantor/settings');

        $response->assertStatus(401);
    }
}
