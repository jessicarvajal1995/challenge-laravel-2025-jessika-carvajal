<?php

namespace App\Application\UseCases;

use App\Application\DTOs\OrderDto;
use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Contracts\LoggerInterface;
use App\Domain\ValueObjects\OrderId;

class GetOrderByIdUseCase
{
    public function __construct(
        private OrderRepositoryInterface $orderRepository,
        private LoggerInterface $logger
    ) {}

    public function execute(int $orderId): OrderDto
    {
        $orderIdVO = new OrderId($orderId);
        
        $order = $this->orderRepository->findById($orderIdVO);
        
        if (!$order) {
            throw new \DomainException("Order not found with ID: {$orderId}");
        }

        $this->logger->info('Order fetched successfully', [
            'order_id' => $order->id()->value(),
            'client_name' => $order->clientName(),
            'status' => $order->status()->value()
        ]);

        return OrderDto::fromDomain($order);
    }
} 