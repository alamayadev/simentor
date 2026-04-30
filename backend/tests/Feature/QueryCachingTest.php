<?php

use App\Services\CacheService;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class QueryCachingTest extends TestCase
{
    public function test_cache_service_generates_correct_keys()
    {
        // PERFORMANCE FIX: Verify CacheService generates consistent cache keys
        $key1 = CacheService::key('settings', 'all');
        $key2 = CacheService::key('settings', 'all', ['filter' => 'test']);

        $this->assertEquals('settings.all', $key1);
        $this->assertStringContainsString('settings.all', $key2);
    }

    public function test_cache_service_stores_data()
    {
        // PERFORMANCE FIX: Verify CacheService can store and retrieve data
        Cache::flush();

        $cacheKey = CacheService::key('test', 'data');
        $result = CacheService::remember($cacheKey, 3600, function () {
            return 'test_value';
        });

        $this->assertEquals('test_value', $result);
        $this->assertTrue(Cache::has($cacheKey));
    }

    public function test_cache_service_forget_works()
    {
        // PERFORMANCE FIX: Verify CacheService can clear cache
        Cache::flush();

        $cacheKey = CacheService::key('test', 'forget');
        CacheService::remember($cacheKey, 3600, function () {
            return 'test_value';
        });

        $this->assertTrue(Cache::has($cacheKey));

        CacheService::forget($cacheKey);

        $this->assertFalse(Cache::has($cacheKey));
    }

    public function test_cache_has_default_ttl()
    {
        // PERFORMANCE FIX: Verify CacheService has default TTL set
        $defaultTtl = CacheService::getDefaultTtl();
        $this->assertEquals(3600, $defaultTtl); // 1 hour
    }

    public function test_cache_key_with_parameters()
    {
        // PERFORMANCE FIX: Verify cache key generation with parameters
        $key1 = CacheService::key('test', 'item', ['param1' => 'value1']);
        $key2 = CacheService::key('test', 'item', ['param1' => 'value2']);

        // Different parameters should generate different keys
        $this->assertNotEquals($key1, $key2);
    }

    public function test_cache_key_includes_identifier()
    {
        // PERFORMANCE FIX: Verify cache keys include identifier
        $key1 = CacheService::key('settings', 'all');
        $key2 = CacheService::key('settings', 'single');

        $this->assertEquals('settings.all', $key1);
        $this->assertEquals('settings.single', $key2);
    }
}
