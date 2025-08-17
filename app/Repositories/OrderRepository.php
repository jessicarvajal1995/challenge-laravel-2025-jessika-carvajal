<?php

namespace App\Repositories;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderRepository implements OrderRepositoryInterface
{
    public function findActiveOrders(): array
    {
        return Order::with('items')
            ->active()
            ->orderBy('created_at', 'desc')
            ->get()
            ->toArray();
    }

    public function findById(int $id): ?Order
    {
        return Order::with('items')->find($id);
    }

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            $order = Order::create([
                'client_name' => $data['client_name'],
                'status' => Order::STATUS_INITIATED,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($data['items'] as $itemData) {
                $order->items()->create([
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                ]);
                $totalAmount += $itemData['quantity'] * $itemData['unit_price'];
            }

            $order->update(['total_amount' => $totalAmount]);
            
            return $order->load('items');
        });
    }

    public function update(Order $order, array $data): Order
    {
        $order->update($data);
        return $order->fresh();
    }

    public function delete(Order $order): bool
    {
        return $order->delete();
    }

    public function forceDelete(Order $order): bool
    {
        return $order->forceDelete();
    }
} 