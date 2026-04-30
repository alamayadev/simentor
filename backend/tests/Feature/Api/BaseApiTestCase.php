<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

abstract class BaseApiTestCase extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $organik;
    protected User $mitra;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create necessary roles BEFORE assigning them to users
        Role::findOrCreate('admin', 'web');
        Role::findOrCreate('super-admin', 'web');
        Role::findOrCreate('organik', 'web');
        Role::findOrCreate('mitra', 'web');

        // Create users for different roles
        $this->admin = User::factory()->admin()->create();
        $this->organik = User::factory()->organik()->create();
        $this->mitra = User::factory()->mitra()->create();
        $this->regularUser = User::factory()->create();
        
        // Assign roles to users
        $this->admin->assignRole('admin');
        $this->admin->assignRole('super-admin');
        $this->organik->assignRole('organik');
        $this->mitra->assignRole('mitra');

        Permission::create([
            'name' => 'mid-permission',
            'guard_name' => 'web',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);
    }

    /**
     * Create an authenticated user for API requests
     */
    protected function actingAsUser(?User $user = null): self
    {
        $user = $user ?: $this->regularUser;
        return $this->actingAs($user, 'sanctum');
    }

    /**
     * Create an admin user for API requests
     */
    protected function actingAsAdmin(): self
    {
        return $this->actingAs($this->admin, 'sanctum');
    }

    /**
     * Create an organik user for API requests
     */
    protected function actingAsOrganik(): self
    {
        return $this->actingAs($this->organik, 'sanctum');
    }

    /**
     * Create a mitra user for API requests
     */
    protected function actingAsMitra(): self
    {
        return $this->actingAs($this->mitra, 'sanctum');
    }

    /**
     * Assert standard API response structure
     */
    protected function assertStandardApiResponse(
        $response,
        int $status = 200,
        bool $success = true,
        ?string $message = null
    ): void {
        $response->assertStatus($status)
            ->assertJson([
                'success' => $success,
            ]);

        if ($message !== null) {
            $response->assertJsonPath('message', $message);
        }
    }

    /**
     * Assert pagination structure
     */
    protected function assertPaginationStructure($response, string $dataKey = 'data'): void
    {
        $this->assertUnifiedPaginationStructure($response);
    }

    /**
     * Create fake file for upload testing
     */
    protected function createFakeFile(
        string $name = 'test.pdf',
        string $mimeType = 'application/pdf',
        int $size = 1024
    ): UploadedFile {
        return UploadedFile::fake()->create($name, $size, $mimeType);
    }

    /**
     * Create fake image for upload testing
     */
    protected function createFakeImage(
        string $name = 'test.jpg',
        int $width = 640,
        int $height = 480
    ): UploadedFile {
        return UploadedFile::fake()->image($name, $width, $height);
    }

    /**
     * Assert validation error response
     */
    protected function assertValidationError(
        $response,
        array $expectedFields,
        string $message = 'The given data was invalid.'
    ): void {
        $response->assertStatus(422);

        $json = $response->json();
        
        // Check if response has the expected fields in errors
        if (isset($json['errors'])) {
            foreach ($expectedFields as $field) {
                $this->assertArrayHasKey($field, $json['errors']);
            }
        } else {
            $this->fail('Response does not contain errors structure');
        }

        // Check if message is provided (optional, as Laravel might return different messages)
        if (isset($json['message'])) {
            $this->assertIsString($json['message']);
        }
    }

    /**
     * Assert unauthorized response
     */
    protected function assertUnauthorized($response, string $message = 'Unauthenticated.'): void
    {
        $response->assertStatus(401)
            ->assertJson([
                'message' => $message
            ]);
    }

    /**
     * Assert forbidden response
     */
    protected function assertForbidden($response, string $message = 'This action is unauthorized.'): void
    {
        $response->assertStatus(403)
            ->assertJson([
                'message' => $message
            ]);
    }

    /**
     * Assert not found response
     */
    protected function assertNotFound($response, string $message = 'Not found.'): void
    {
        $response->assertStatus(404)
            ->assertJson([
                'message' => $message
            ]);
    }

    /**
     * Assert rate limited response
     */
    protected function assertRateLimited($response): void
    {
        $response->assertStatus(429)
            ->assertHeader('retry-after');
    }

    /**
     * Get standard success response structure
     */
    protected function getSuccessResponse(mixed $data = null, string $message = 'Success'): array
    {
        return [
            'success' => true,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Get standard error response structure
     */
    protected function getErrorResponse(string $message = 'Error', mixed $data = null): array
    {
        return [
            'success' => false,
            'message' => $message,
            'data' => $data
        ];
    }

    /**
     * Set fake storage for file operations
     */
    protected function setUpFakeStorage(): void
    {
        Storage::fake('local');
        Storage::fake('public');
    }

    /**
     * Assert file exists in storage
     */
    protected function assertFileExistsInStorage(string $path, string $disk = 'public'): void
    {
        Storage::disk($disk)->assertExists($path);
    }

    /**
     * Assert file doesn't exist in storage
     */
    protected function assertFileNotExistsInStorage(string $path, string $disk = 'public'): void
    {
        Storage::disk($disk)->assertMissing($path);
    }
}
