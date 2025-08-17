<?php

namespace App\Domain\Entities;

use App\Domain\ValueObjects\OrderId;
use App\Domain\ValueObjects\OrderStatus;
use App\Domain\ValueObjects\Money;
use App\Domain\Exceptions\InvalidOrderStatusTransition;

class Order
{
    private ?OrderId $id;
    private string $clientName;
    private OrderStatus $status;
    private Money $totalAmount;
    private array $items;
    private \DateTimeImmutable $createdAt;
    private ?\DateTimeImmutable $updatedAt;

    public function __construct(
        string $clientName,
        array $items = [],
        ?OrderId $id = null,
        ?OrderStatus $status = null,
        ?\DateTimeImmutable $createdAt = null
    ) {
        if (empty($clientName)) {
            throw new \InvalidArgumentException('Client name cannot be empty');
        }

        $this->id = $id;
        $this->clientName = $clientName;
        $this->status = $status ?? OrderStatus::initiated();
        $this->items = [];
        $this->createdAt = $createdAt ?? new \DateTimeImmutable();
        $this->updatedAt = null;

        foreach ($items as $item) {
            $this->addItem($item);
        }

        $this->recalculateTotal();
    }

    public static function create(string $clientName, array $itemsData): self
    {
        $items = [];
        foreach ($itemsData as $itemData) {
            $items[] = new OrderItem(
                $itemData['description'],
                $itemData['quantity'],
                new Money($itemData['unit_price'])
            );
        }

        return new self($clientName, $items);
    }

    public function id(): ?OrderId
    {
        return $this->id;
    }

    public function clientName(): string
    {
        return $this->clientName;
    }

    public function status(): OrderStatus
    {
        return $this->status;
    }

    public function totalAmount(): Money
    {
        return $this->totalAmount;
    }

    public function items(): array
    {
        return $this->items;
    }

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function addItem(OrderItem $item): void
    {
        $this->items[] = $item;
        $this->recalculateTotal();
        $this->touch();
    }

    public function removeItem(int $index): void
    {
        if (!isset($this->items[$index])) {
            throw new \InvalidArgumentException('Item not found');
        }

        unset($this->items[$index]);
        $this->items = array_values($this->items); // Reindex array
        $this->recalculateTotal();
        $this->touch();
    }

    public function advanceStatus(): void
    {
        if (!$this->status->canAdvance()) {
            throw new InvalidOrderStatusTransition(
                "Cannot advance order status from: {$this->status->value()}"
            );
        }

        $this->status = $this->status->getNext();
        $this->touch();
    }

    public function canAdvanceStatus(): bool
    {
        return $this->status->canAdvance();
    }

    public function isActive(): bool
    {
        return $this->status->isActive();
    }

    public function isDelivered(): bool
    {
        return $this->status->isDelivered();
    }

    public function hasItems(): bool
    {
        return !empty($this->items);
    }

    public function itemCount(): int
    {
        return count($this->items);
    }

    private function recalculateTotal(): void
    {
        $total = Money::zero();
        
        foreach ($this->items as $item) {
            $total = $total->add($item->totalPrice());
        }
        
        $this->totalAmount = $total;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function setId(OrderId $id): void
    {
        if ($this->id !== null) {
            throw new \LogicException('Order ID cannot be changed once set');
        }
        
        $this->id = $id;
    }
} 