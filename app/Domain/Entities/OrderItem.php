<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\Money;

class OrderItem
{
    private ?int $id;
    private string $description;
    private int $quantity;
    private Money $unitPrice;

    public function __construct(
        string $description,
        int $quantity,
        Money $unitPrice,
        ?int $id = null
    ) {
        if (empty($description)) {
            throw new \InvalidArgumentException('Item description cannot be empty');
        }
        
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Item quantity must be positive');
        }

        $this->id = $id;
        $this->description = $description;
        $this->quantity = $quantity;
        $this->unitPrice = $unitPrice;
    }

    public function id(): ?int
    {
        return $this->id;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function quantity(): int
    {
        return $this->quantity;
    }

    public function unitPrice(): Money
    {
        return $this->unitPrice;
    }

    public function totalPrice(): Money
    {
        return $this->unitPrice->multiply($this->quantity);
    }

    public function changeQuantity(int $newQuantity): void
    {
        if ($newQuantity <= 0) {
            throw new \InvalidArgumentException('Item quantity must be positive');
        }
        
        $this->quantity = $newQuantity;
    }

    public function changePrice(Money $newPrice): void
    {
        $this->unitPrice = $newPrice;
    }
} 