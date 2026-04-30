<?php

namespace Tests\Feature\Api\Kantor;

use Tests\Feature\Api\BaseApiTestCase;
use App\Models\DetilConfiguration;

class DetilConfigurationApiTest extends BaseApiTestCase
{
    // GET /api/detil-configuration/get-available-fields Tests

    /** @test */
    public function it_requires_authentication_to_get_available_fields()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/available-fields');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_validates_required_parameters_for_available_fields()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/available-fields');

        $this->assertValidationError($response, ['foreign_key_value', 'foreign_key_type']);
    }

    /** @test */
    public function it_validates_foreign_key_type_for_available_fields()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/available-fields?' . http_build_query([
                'foreign_key_value' => 'test_value',
                'foreign_key_type' => 'invalid_type'
            ]));

        $this->assertValidationError($response, ['foreign_key_type']);
    }

    /** @test */
    public function it_returns_available_fields_for_kegiatan_id()
    {
        // Create a custom config so it is always returned
        DetilConfiguration::factory()->create([
            'foreign_key' => null,
            'field' => [
                'name' => 'target',
                'label' => 'Target',
                'type' => 'number',
                'required' => false,
                'options' => [],
            ]
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/available-fields?' . http_build_query([
                'foreign_key_value' => 'kegiatan_1',
                'foreign_key_type' => 'kegiatan_id'
            ]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'field' => [
                            'name',
                            'label',
                            'type',
                            'required',
                            'options'
                        ]
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Available fields retrieved successfully'
            ]);
    }

    /** @test */
    public function it_returns_available_fields_for_kec_id()
    {
        DetilConfiguration::factory()->create([
            'foreign_key' => null,
            'field' => [
                'name' => 'desa_name',
                'label' => 'Nama Desa',
                'type' => 'text',
                'required' => false,
                'options' => [],
            ]
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/available-fields?' . http_build_query([
                'foreign_key_value' => 'kec_1',
                'foreign_key_type' => 'kec_id'
            ]));

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    /** @test */
    public function it_returns_available_fields_for_desa_id()
    {
        DetilConfiguration::factory()->create([
            'foreign_key' => null,
            'field' => [
                'name' => 'block_number',
                'label' => 'Nomor Blok',
                'type' => 'number',
                'required' => false,
                'options' => [],
            ]
        ]);

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/available-fields?' . http_build_query([
                'foreign_key_value' => 'desa_1',
                'foreign_key_type' => 'desa_id'
            ]));

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertNotEmpty($data);
    }

    // POST /api/detil-configuration/store-configuration Tests

    /** @test */
    public function it_requires_authentication_to_store_configuration()
    {
        $response = $this->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
            'name' => 'Test Configuration'
        ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_validates_required_fields_for_configuration_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', []);

        $this->assertValidationError($response, ['name', 'field']);
    }

    /** @test */
    public function it_validates_field_structure_for_configuration_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Configuration',
                'field' => 'invalid_structure' // Should be array
            ]);

        $this->assertValidationError($response, ['field']);
    }

    /** @test */
    public function it_validates_field_name_for_configuration_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Configuration',
                'field' => [
                    'label' => 'Test Label',
                    'required' => true,
                    'type' => 'text'
                    // Missing 'name'
                ]
            ]);

        $this->assertValidationError($response, ['field.name']);
    }

    /** @test */
    public function it_validates_field_label_for_configuration_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Configuration',
                'field' => [
                    'name' => 'test_field',
                    'required' => true,
                    'type' => 'text'
                    // Missing 'label'
                ]
            ]);

        $this->assertValidationError($response, ['field.label']);
    }

    /** @test */
    public function it_validates_field_type_for_configuration_creation()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Configuration',
                'field' => [
                    'name' => 'test_field',
                    'label' => 'Test Label',
                    'required' => true,
                    'type' => 'invalid_type'
                ]
            ]);

        $this->assertValidationError($response, ['field.type']);
    }

    /** @test */
    public function it_validates_enum_field_requires_options()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Configuration',
                'field' => [
                    'name' => 'test_field',
                    'label' => 'Test Label',
                    'required' => true,
                    'type' => 'enum'
                    // Missing 'options'
                ]
            ]);

        $this->assertValidationError($response, ['field.options']);
    }

    /** @test */
    public function it_validates_enum_options_structure()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Configuration',
                'field' => [
                    'name' => 'test_field',
                    'label' => 'Test Label',
                    'required' => true,
                    'type' => 'enum',
                    'options' => 'invalid_structure' // Should be array
                ]
            ]);

        $this->assertValidationError($response, ['field.options']);
    }

    /** @test */
    public function it_creates_text_field_configuration()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Text Configuration',
                'related_table' => 'test_table',
                'foreign_key' => 'kegiatan_id',
                'field' => [
                    'name' => 'test_text_field',
                    'label' => 'Test Text Field',
                    'required' => true,
                    'type' => 'text'
                ],
                'is_active' => true
            ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'related_table',
                    'foreign_key',
                    'field',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Configuration created successfully'
            ]);

        // Verify database
        $this->assertDatabaseHas('detil_configurations', [
            'name' => 'Test Text Configuration',
            'related_table' => 'test_table',
            'foreign_key' => 'kegiatan_id'
        ]);
    }

    /** @test */
    public function it_creates_number_field_configuration()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Number Configuration',
                'field' => [
                    'name' => 'test_number_field',
                    'label' => 'Test Number Field',
                    'required' => false,
                    'type' => 'number'
                ],
                'is_active' => true
            ]);

        $response->assertStatus(201);

        // Verify field structure
        $field = $response->json('data.field');
        $this->assertEquals('custom', $field['source']);
        $this->assertEquals('test_number_field', $field['name']);
        $this->assertEquals('Test Number Field', $field['label']);
        $this->assertEquals('number', $field['type']);
        $this->assertFalse($field['required']);
    }

    /** @test */
    public function it_creates_date_field_configuration()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Date Configuration',
                'field' => [
                    'name' => 'test_date_field',
                    'label' => 'Test Date Field',
                    'required' => true,
                    'type' => 'date'
                ],
                'is_active' => true
            ]);

        $response->assertStatus(201);

        $field = $response->json('data.field');
        $this->assertEquals('date', $field['type']);
    }

    /** @test */
    public function it_creates_enum_field_configuration()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Enum Configuration',
                'field' => [
                    'name' => 'test_enum_field',
                    'label' => 'Test Enum Field',
                    'required' => true,
                    'type' => 'enum',
                    'options' => ['option1', 'option2', 'option3']
                ],
                'is_active' => true
            ]);

        $response->assertStatus(201);

        $field = $response->json('data.field');
        $this->assertEquals('enum', $field['type']);
        $this->assertEquals(['option1', 'option2', 'option3'], $field['options']);
    }

    // GET /api/detil-configuration Tests

    /** @test */
    public function it_requires_authentication_to_index_configurations()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/detil-configurations');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_lists_all_configurations()
    {
        DetilConfiguration::factory()->count(5)->create();

        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan/monitoring/detil-configurations');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'related_table',
                        'foreign_key',
                        'field',
                        'is_active',
                        'created_at',
                        'updated_at'
                    ]
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully'
            ]);

        $this->assertEquals(5, count($response->json('data')));
    }

    // GET /api/detil-configuration/{id} Tests

    /** @test */
    public function it_requires_authentication_to_show_configuration()
    {
        $response = $this->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/1');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_configuration()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Configuration not found'
            ]);
    }

    /** @test */
    public function it_returns_configuration_details()
    {
        $config = DetilConfiguration::factory()->create([
            'name' => 'Test Configuration',
            'foreign_key' => 'test_foreign_key',
            'field' => [
                'name' => 'test_field',
                'label' => 'Test Label',
                'type' => 'text'
            ]
        ]);

        $response = $this->actingAsAdmin()
            ->getJson("/api/kantor/kegiatan/monitoring/detil-configurations/{$config->id}");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'related_table',
                    'foreign_key',
                    'field',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Data retrieved successfully'
            ]);

        $this->assertEquals($config->id, $response->json('data.id'));
        $this->assertEquals('Test Configuration', $response->json('data.name'));
    }

    // PUT /api/detil-configuration/{id} Tests

    /** @test */
    public function it_requires_authentication_to_update_configuration()
    {
        $response = $this->putJson('/api/kantor/kegiatan/monitoring/detil-configurations/1', [
                'name' => 'Updated Configuration'
            ]);

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_configuration()
    {
        $response = $this->actingAsAdmin()
            ->putJson('/api/kantor/kegiatan/monitoring/detil-configurations/999', [
                'name' => 'Updated Configuration'
            ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Configuration not found'
            ]);
    }

    /** @test */
    public function it_updates_configuration_successfully()
    {
        $config = DetilConfiguration::factory()->create();

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/kegiatan/monitoring/detil-configurations/{$config->id}", [
                'name' => 'Updated Configuration Name',
                'related_table' => 'updated_table',
                'field' => [
                    'name' => 'updated_field',
                    'label' => 'Updated Label',
                    'required' => false,
                    'type' => 'number'
                ],
                'is_active' => false
            ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'id',
                    'name',
                    'related_table',
                    'foreign_key',
                    'field',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ])
            ->assertJson([
                'success' => true,
                'message' => 'Configuration updated successfully'
            ]);

        // Verify database update
        $updatedConfig = $config->fresh();
        $this->assertEquals('Updated Configuration Name', $updatedConfig->name);
        $this->assertEquals('updated_table', $updatedConfig->related_table);
        $this->assertFalse((bool) $updatedConfig->is_active);
    }

    // DELETE /api/detil-configuration/{id} Tests

    /** @test */
    public function it_requires_authentication_to_delete_configuration()
    {
        $response = $this->deleteJson('/api/kantor/kegiatan/monitoring/detil-configurations/1');

        $this->assertUnauthorized($response);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_configuration()
    {
        $response = $this->actingAsAdmin()
            ->deleteJson('/api/kantor/kegiatan/monitoring/detil-configurations/999');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Configuration not found'
            ]);
    }

    /** @test */
    public function it_deletes_configuration_successfully()
    {
        $config = DetilConfiguration::factory()->create();

        $response = $this->actingAsAdmin()
            ->deleteJson("/api/kantor/kegiatan/monitoring/detil-configurations/{$config->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Configuration deleted successfully',
                'data' => null
            ]);

        // Verify database
        $this->assertDatabaseMissing('detil_configurations', ['id' => $config->id]);
    }

    // Additional Security Tests

    /** @test */
    public function it_prevents_mass_assignment_vulnerabilities()
    {
        $config = DetilConfiguration::factory()->create();

        $response = $this->actingAsAdmin()
            ->putJson("/api/kantor/kegiatan/monitoring/detil-configurations/{$config->id}", [
                'id' => 999, // Should not be updatable
                'field' => [
                    'source' => 'should_not_change', // Should always be 'custom'
                    'name' => 'updated_field'
                ]
            ]);

        $response->assertStatus(200);

        $updatedConfig = $config->fresh();
        $this->assertNotEquals(999, $updatedConfig->id);
        
        // Field source should remain 'custom'
        $field = $updatedConfig->field;
        $this->assertEquals('custom', $field['source']);
    }

    /** @test */
    public function it_prevents_sql_injection_in_configuration_operations()
    {
        $response = $this->actingAsAdmin()
            ->getJson('/api/kantor/kegiatan/monitoring/detil-configurations/available-fields?' . http_build_query([
                'foreign_key_value' => "'; DROP TABLE detil_configurations; --",
                'foreign_key_type' => 'kegiatan_id'
            ]));

        // Should handle gracefully without crashing
        $this->assertContains($response->status(), [200, 403]);
        
        // Table should still exist
        $this->assertDatabaseCount('detil_configurations', 0); // Should be 0 after refresh
    }

    /** @test */
    public function it_handles_large_payloads_gracefully()
    {
        $largeName = str_repeat('a', 300);
        $largeField = [
            'name' => str_repeat('b', 300),
            'label' => str_repeat('c', 300),
            'options' => array_fill(0, 100, str_repeat('d', 50))
        ];

        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => $largeName,
                'field' => $largeField,
                'is_active' => true
            ]);

        // Should handle gracefully
        $this->assertContains($response->status(), [201, 422]);
    }

    /** @test */
    public function it_validates_complex_field_structures()
    {
        $response = $this->actingAsAdmin()
            ->postJson('/api/kantor/kegiatan/monitoring/detil-configurations', [
                'name' => 'Test Complex Configuration',
                'field' => [
                    'name' => 'complex_field',
                    'label' => 'Complex Field',
                    'required' => true,
                    'type' => 'enum',
                    'options' => [
                        'option1' => 'Option 1 Description',
                        'option2' => 'Option 2 Description',
                        'option3' => ['invalid' => 'structure'] // Invalid option structure
                    ]
                ],
                'is_active' => true
            ]);

        // Should handle invalid option structure gracefully
        $this->assertContains($response->status(), [201, 422]);
    }
}
