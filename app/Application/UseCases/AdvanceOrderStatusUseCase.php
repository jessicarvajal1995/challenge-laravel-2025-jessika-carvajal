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

        $order->advanceStatus();

        $orderDto = OrderDto::fromDomain($order);

        // Si la orden llega a "delivered", la elimino de la base de datos y del cache 
        if ($order->isDelivered()) {
            $this->orderRepository->delete($orderId);
            
            $this->logger->info('Order delivered and deleted successfully', [
                'order_id' => $order->id()->value(),
                'client_name' => $order->clientName(),
                'final_status' => $order->status()->value()
            ]);
        } else {
            // aqui guardo mi orden si no está delivered
            $this->orderRepository->save($order);
            
            $this->logger->info('Order status advanced successfully', [
                'order_id' => $order->id()->value(),
                'new_status' => $order->status()->value()
            ]);
        }

        $this->cache->forget(self::CACHE_KEY);

        return $orderDto;
    }
} 