<?php

namespace App\Application\DTOs;

use App\Domain\Entities\OrderItem;

readonly class OrderItemDto
{
    public function __construct(
        public ?int $id,
        public string $description,
        public int $quantity,
        public float $unitPrice,
        public float $totalPrice
    ) {}

    public static function fromDomain(OrderItem $item): self
    {
        return new self(
            id: $item->id(),
            description: $item->description(),
            quantity: $item->quantity(),
            unitPrice: $item->unitPrice()->amount(),
            totalPrice: $item->totalPrice()->amount()
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'description' => $this->description,
            'quantity' => $this->quantity,
            'unit_price' => $this->unitPrice,
            'total_price' => $this->totalPrice,
        ];
    }
} 