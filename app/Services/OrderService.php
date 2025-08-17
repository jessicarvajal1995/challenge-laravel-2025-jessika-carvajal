<?php

namespace App\Services;

use App\Models\Order;
use App\Repositories\OrderRepositoryInterface;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class OrderService
{
    private const CACHE_KEY = 'active_orders';
    private const CACHE_TTL = 30; // 30 segundos

    private OrderRepositoryInterface $orderRepository;

    public function __construct(OrderRepositoryInterface $orderRepository)
    {
        $this->orderRepository = $orderRepository;
    }

    public function getActiveOrders(): array
    {
        return Cache::remember(self::CACHE_KEY, self::CACHE_TTL, function () {
            Log::info('Fetching active orders from database');
            return $this->orderRepository->findActiveOrders();
        });
    }

    public function getOrderById(int $id): ?Order
    {
        return $this->orderRepository->findById($id);
    }

    public function createOrder(array $data): Order
    {
        $order = $this->orderRepository->create($data);
        
        // Limpiar caché luego de crear una nueva orden
        $this->clearActiveOrdersCache();
        
        Log::info("Order created successfully", ['order_id' => $order->id]);
        
        return $order;
    }

    public function advanceOrderStatus(Order $order): Order
    {
        if (!$order->canAdvanceStatus()) {
            throw new \InvalidArgumentException('Order status cannot be advanced');
        }

        $nextStatus = $order->getNextStatus();
        $order = $this->orderRepository->update($order, ['status' => $nextStatus]);

        // Si la orden está entregada, eliminarla completamente
        if ($nextStatus === Order::STATUS_DELIVERED) {
            $this->orderRepository->forceDelete($order);
            Log::info("Orden entregada y eliminada", ['order_id' => $order->id]);
        } else {
            Log::info("Order status advanced", [
                'order_id' => $order->id,
                'new_status' => $nextStatus
            ]);
        }

        // Limpiar caché luego de cambiar el estado
        $this->clearActiveOrdersCache();

        return $order;
    }

    private function clearActiveOrdersCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        Log::info('Active orders cache cleared');
    }
} 