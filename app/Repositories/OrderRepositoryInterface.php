<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    public function findActiveOrders(): array;
    
    public function findById(int $id): ?Order;
    
    public function create(array $data): Order;
    
    public function update(Order $order, array $data): Order;
    
    public function delete(Order $order): bool;
    
    public function forceDelete(Order $order): bool;
} 