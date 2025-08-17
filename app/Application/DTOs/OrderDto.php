<?php

namespace App\Application\DTOs;

use App\Domain\Entities\Order;

readonly class OrderDto
{
    public function __construct(
        public ?int $id,
        public string $clientName,
        public string $status,
        public float $totalAmount,
        public array $items,
        public string $createdAt,
        public ?string $updatedAt = null
    ) {}

    public static function fromDomain(Order $order): self
    {
        $items = array_map(
            fn($item) => OrderItemDto::fromDomain($item),
            $order->items()
        );

        return new self(
            id: $order->id()?->value(),
            clientName: $order->clientName(),
            status: $order->status()->value(),
            totalAmount: $order->totalAmount()->amount(),
            items: $items,
            createdAt: $order->createdAt()->format('Y-m-d H:i:s'),
            updatedAt: $order->updatedAt()?->format('Y-m-d H:i:s')
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'client_name' => $this->clientName,
            'status' => $this->status,
            'total_amount' => $this->totalAmount,
            'items' => array_map(fn($item) => $item->toArray(), $this->items),
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
} 