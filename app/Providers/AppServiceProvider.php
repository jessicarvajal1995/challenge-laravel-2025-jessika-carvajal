<?php

namespace App\Providers;

use App\Repositories\OrderRepository;
use App\Repositories\OrderRepositoryInterface;
use Illuminate\Support\ServiceProvider;

// Dominios 
use App\Domain\Contracts\OrderRepositoryInterface as DomainOrderRepositoryInterface;
use App\Domain\Contracts\CacheInterface;
use App\Domain\Contracts\LoggerInterface;

// Infrastructure Adapters
use App\Infrastructure\Persistence\EloquentOrderRepository;
use App\Infrastructure\Cache\LaravelCacheAdapter;
use App\Infrastructure\Logging\LaravelLoggerAdapter;

class AppServiceProvider extends ServiceProvider
{

    public function register(): void
    {
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(DomainOrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(CacheInterface::class, LaravelCacheAdapter::class);
        $this->app->bind(LoggerInterface::class, LaravelLoggerAdapter::class);
    }

}
