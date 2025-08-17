<?php

namespace App\Application\UseCases;

use App\Application\DTOs\OrderDto;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Contracts\CacheInterface;
use App\Domain\Contracts\LoggerInterface;

class GetActiveOrdersUseCase
{
    private const CACHE_KEY = 'active_orders';
    private const CACHE_TTL = 30; // 30 segundos

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {}

    public function execute(): array
    {
        return $this->cache->remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            function () {
                $this->logger->info('Fetching active orders from database');
                
                $orders = $this->orderRepository->findActiveOrders();
                
                return array_map(
                    fn($order) => OrderDto::fromDomain($order),
                    $orders
                );
            }
        );
    }
} 