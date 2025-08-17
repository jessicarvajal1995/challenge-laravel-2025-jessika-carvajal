<?php

namespace App\Infrastructure\Persistence;

use App\Domain\Contracts\OrderRepositoryInterface;
use App\Domain\Entities\Order;
use App\Domain\Entities\OrderItem;
use App\Domain\ValueObjects\OrderId;
use App\Domain\ValueObjects\OrderStatus;
use App\Domain\ValueObjects\Money;
use App\Models\Order as OrderModel;
use App\Models\OrderItem as OrderItemModel;
use Illuminate\Support\Facades\DB;

class EloquentOrderRepository implements OrderRepositoryInterface
{
    public function findById(OrderId $id): ?Order
    {
        $model = OrderModel::with('items')->find($id->value());
        
        return $model ? $this->toDomain($model) : null;
    }

    public function findActiveOrders(): array
    {
        $models = OrderModel::with('items')
            ->active()
            ->orderBy('created_at', 'desc')
            ->get();

        return $models->map(fn($model) => $this->toDomain($model))->toArray();
    }

    public function save(Order $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->id() === null) {
                // Nueva orden
                $this->createOrder($order);
            } else {
                // Actualizar orden existente
                $this->updateOrder($order);
            }
        });
    }

    public function delete(OrderId $id): bool
    {
        $model = OrderModel::find($id->value());
        
        if (!$model) {
            return false;
        }

        return $model->delete();
    }

    private function createOrder(Order $order): void
    {
        $model = OrderModel::create([
            'client_name' => $order->clientName(),
            'status' => $order->status()->value(),
            'total_amount' => $order->totalAmount()->amount(),
            'created_at' => $order->createdAt(),
            'updated_at' => $order->updatedAt(),
        ]);

        // Establecer el ID en la entidad de dominio
        $order->setId(new OrderId($model->id));

        // Crear los items
        foreach ($order->items() as $item) {
            OrderItemModel::create([
                'order_id' => $model->id,
                'description' => $item->description(),
                'quantity' => $item->quantity(),
                'unit_price' => $item->unitPrice()->amount(),
            ]);
        }
    }

    private function updateOrder(Order $order): void
    {
        $model = OrderModel::find($order->id()->value());
        
        if (!$model) {
            throw new \DomainException('Order not found for update');
        }

        $model->update([
            'client_name' => $order->clientName(),
            'status' => $order->status()->value(),
            'total_amount' => $order->totalAmount()->amount(),
            'updated_at' => $order->updatedAt(),
        ]);
    }

    private function toDomain(OrderModel $model): Order
    {
        $items = [];
        foreach ($model->items as $itemModel) {
            $items[] = new OrderItem(
                $itemModel->description,
                $itemModel->quantity,
                new Money($itemModel->unit_price),
                $itemModel->id
            );
        }

        return new Order(
            $model->client_name,
            $items,
            new OrderId($model->id),
            new OrderStatus($model->status),
            $model->created_at ? new \DateTimeImmutable($model->created_at->toDateTimeString()) : null
        );
    }
} 