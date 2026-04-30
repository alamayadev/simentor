<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;

/**
 * Cache Service
 *
 * Provides a simple interface for caching query results
 * to improve application performance and reduce database load.
 */
class CacheService
{
    /**
     * Cache duration in seconds (default: 1 hour)
     */
    protected static int $defaultTtl = 3600;

    /**
     * Remember a query result in cache
     *
     * @param string $key Cache key
     * @param int $ttl Time to live in seconds
     * @param callable $callback Query to execute if cache miss
     * @return mixed
     */
    public static function remember(string $key, int $ttl, callable $callback)
    {
        return Cache::remember($key, $ttl, $callback);
    }

    /**
     * Remember a query result forever (until manually cleared)
     *
     * @param string $key Cache key
     * @param callable $callback Query to execute if cache miss
     * @return mixed
     */
    public static function rememberForever(string $key, callable $callback)
    {
        return Cache::rememberForever($key, $callback);
    }

    /**
     * Clear a specific cache key
     *
     * @param string $key Cache key
     * @return bool
     */
    public static function forget(string $key): bool
    {
        return Cache::forget($key);
    }

    /**
     * Clear all application cache
     *
     * @return bool
     */
    public static function flush(): bool
    {
        return Cache::flush();
    }

    /**
     * Generate a cache key for a specific entity
     *
     * @param string $entity Entity name (e.g., 'users', 'settings')
     * @param string|null $identifier Optional identifier (e.g., user ID, slug)
     * @param array $params Optional additional parameters
     * @return string
     */
    public static function key(string $entity, ?string $identifier = null, array $params = []): string
    {
        $key = $entity;

        if ($identifier !== null) {
            $key .= '.' . $identifier;
        }

        if (!empty($params)) {
            $key .= '.' . md5(json_encode($params));
        }

        return $key;
    }

    /**
     * Get default cache TTL
     *
     * @return int
     */
    public static function getDefaultTtl(): int
    {
        return self::$defaultTtl;
    }

    /**
     * Set default cache TTL
     *
     * @param int $ttl Time to live in seconds
     * @return void
     */
    public static function setDefaultTtl(int $ttl): void
    {
        self::$defaultTtl = $ttl;
    }
}
