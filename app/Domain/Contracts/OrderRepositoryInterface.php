<?php

namespace App\Domain\Contracts;

use App\Domain\Entities\Order;
use App\Domain\ValueObjects\OrderId;

interface OrderRepositoryInterface
{
    public function findById(OrderId $id): ?Order;
    
    public function findActiveOrders(): array;
    
    public function save(Order $order): void;
    
    public function delete(OrderId $id): bool;
} 