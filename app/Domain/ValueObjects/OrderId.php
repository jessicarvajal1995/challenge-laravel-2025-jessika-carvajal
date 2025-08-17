<?php

namespace App\Domain\ValueObjects;

readonly class OrderId
{
    public function __construct(
        private int $value
    ) {
        if ($value <= 0) {
            throw new \InvalidArgumentException('Order ID must be a positive integer');
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(OrderId $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
} 