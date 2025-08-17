<?php

namespace App\Infrastructure\Cache;

use App\Domain\Contracts\CacheInterface;
use Illuminate\Support\Facades\Cache;

class LaravelCacheAdapter implements CacheInterface
{
    public function get(string $key): mixed
    {
        return Cache::get($key);
    }

    public function put(string $key, mixed $value, int $ttlSeconds): void
    {
        Cache::put($key, $value, $ttlSeconds);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function remember(string $key, int $ttlSeconds, callable $callback): mixed
    {
        return Cache::remember($key, $ttlSeconds, $callback);
    }
} 