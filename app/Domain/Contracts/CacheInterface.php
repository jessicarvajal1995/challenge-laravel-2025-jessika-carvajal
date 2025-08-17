<?php

namespace App\Domain\Contracts;

interface CacheInterface
{
    public function get(string $key): mixed;
    
    public function put(string $key, mixed $value, int $ttlSeconds): void;
    
    public function forget(string $key): void;
    
    public function remember(string $key, int $ttlSeconds, callable $callback): mixed;
} 