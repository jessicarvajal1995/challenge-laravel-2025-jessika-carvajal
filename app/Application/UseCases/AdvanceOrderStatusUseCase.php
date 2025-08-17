<?php

namespace App\Application\UseCases;

use App\Application\Commands\AdvanceOrderStatusCommand;
use App\Application\DTOs\OrderDto;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Contracts\CacheInterface;
use App\Domain\Contracts\LoggerInterface;
use App\Domain\ValueObjects\OrderId;

class AdvanceOrderStatusUseCase
{
    private const CACHE_KEY = 'active_orders';

    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private CacheInterface $cache,
        private LoggerInterface $logger
    ) {}

    public function execute(AdvanceOrderStatusCommand $command): OrderDto
    {
        $orderId = new OrderId($command->orderId);
        
        $order = $this->orderRepository->findById($orderId);
        
        if (!$order) {
            throw new \DomainException("Order not found with ID: {$command->orderId}");
        }

        // Actualiza el estado usando lógica de dominio
        $order->advanceStatus();

        // Guardar usando el repositorio
        $this->orderRepository->save($order);

        // Limpiar caché luego de actualizar el estado
        $this->cache->forget(self::CACHE_KEY);

        // Log del evento
        $this->logger->info('Order status advanced successfully', [
            'order_id' => $order->id()->value(),
            'new_status' => $order->status()->value()
        ]);

        // Retornamos el DTO
        return OrderDto::fromDomain($order);
    }
} 