<?php

namespace App\Application\UseCases;

use App\Application\Commands\CreateOrderCommand;
use App\Application\DTOs\OrderDto;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Contracts\CacheInterface;
use App\Domain\Contracts\LoggerInterface;
use App\Domain\Entities\Order;

class CreateOrderUseCase
{
    private const CACHE_KEY = 'active_orders';

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {}

    public function execute(CreateOrderCommand $command): OrderDto
    {
        // Crear la entidad de dominio
        $order = Order::create($command->clientName, $command->items);

        // Guardar usando el repositorio
        $this->orderRepository->save($order);

        // Limpiar caché
        $this->cache->forget(self::CACHE_KEY);

        // Log del evento
        $this->logger->info('Order created successfully', [
            'order_id' => $order->id()?->value(),
            'client_name' => $order->clientName(),
            'total_amount' => $order->totalAmount()->amount()
        ]);

        // Retornamos el DTO
        return OrderDto::fromDomain($order);
    }
} 