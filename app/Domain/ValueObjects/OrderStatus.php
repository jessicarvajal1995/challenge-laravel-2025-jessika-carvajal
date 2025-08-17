<?php

namespace App\Domain\ValueObjects;

use App\Domain\Exceptions\InvalidOrderStatusTransition;

readonly class OrderStatus
{
    private const INITIATED = 'initiated';
    private const SENT = 'sent';
    private const DELIVERED = 'delivered';

    private const VALID_STATUSES = [
        self::INITIATED,
        self::SENT,
        self::DELIVERED,
    ];

    private const STATUS_TRANSITIONS = [
        self::INITIATED => self::SENT,
        self::SENT => self::DELIVERED,
    ];

    public function __construct(
        private string $value
    ) {
        if (!in_array($value, self::VALID_STATUSES)) {
            throw new \InvalidArgumentException("Invalid order status: {$value}");
        }
    }

    public static function initiated(): self
    {
        return new self(self::INITIATED);
    }

    public static function sent(): self
    {
        return new self(self::SENT);
    }

    public static function delivered(): self
    {
        return new self(self::DELIVERED);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function canAdvance(): bool
    {
        return isset(self::STATUS_TRANSITIONS[$this->value]);
    }

    public function getNext(): self
    {
        if (!$this->canAdvance()) {
            throw new InvalidOrderStatusTransition("Cannot advance from status: {$this->value}");
        }

        return new self(self::STATUS_TRANSITIONS[$this->value]);
    }

    public function isInitiated(): bool
    {
        return $this->value === self::INITIATED;
    }

    public function isSent(): bool
    {
        return $this->value === self::SENT;
    }

    public function isDelivered(): bool
    {
        return $this->value === self::DELIVERED;
    }

    public function isActive(): bool
    {
        return !$this->isDelivered();
    }

    public function equals(OrderStatus $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
} 