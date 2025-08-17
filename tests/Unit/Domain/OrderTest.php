<?php

namespace Tests\Unit\Domain;

use PHPUnit\Framework\TestCase;
use App\Domain\Entities\Order;
use App\Domain\ValueObjects\OrderStatus;
use App\Domain\ValueObjects\Money;
use App\Domain\Exceptions\InvalidOrderStatusTransition;

class OrderTest extends TestCase
{
    public function test_can_create_order_with_items(): void
    {
        $itemsData = [
            [
                'description' => 'Test Item',
                'quantity' => 2,
                'unit_price' => 10.50
            ]
        ];

        $order = Order::create('John Doe', $itemsData);

        $this->assertEquals('John Doe', $order->clientName());
        $this->assertTrue($order->status()->isInitiated());
        $this->assertEquals(21.00, $order->totalAmount()->amount());
        $this->assertCount(1, $order->items());
    }

    public function test_can_advance_order_status(): void
    {
        $order = Order::create('John Doe', []);

        $this->assertTrue($order->canAdvanceStatus());
        $this->assertTrue($order->status()->isInitiated());

        $order->advanceStatus();
        $this->assertTrue($order->status()->isSent());

        $order->advanceStatus();
        $this->assertTrue($order->status()->isDelivered());
    }

    public function test_cannot_advance_delivered_order(): void
    {
        $order = Order::create('John Doe', []);
        
        // Avanzar hasta delivered
        $order->advanceStatus(); // sent
        $order->advanceStatus(); // delivered

        $this->assertFalse($order->canAdvanceStatus());
        
        $this->expectException(InvalidOrderStatusTransition::class);
        $order->advanceStatus();
    }

    public function test_order_calculates_total_correctly(): void
    {
        $itemsData = [
            [
                'description' => 'Item 1',
                'quantity' => 2,
                'unit_price' => 10.00
            ],
            [
                'description' => 'Item 2',
                'quantity' => 3,
                'unit_price' => 5.50
            ]
        ];

        $order = Order::create('John Doe', $itemsData);

        // 2 * 10.00 + 3 * 5.50 = 20.00 + 16.50 = 36.50
        $this->assertEquals(36.50, $order->totalAmount()->amount());
    }

    public function test_order_is_active_when_not_delivered(): void
    {
        $order = Order::create('John Doe', []);

        $this->assertTrue($order->isActive());
        
        $order->advanceStatus(); // sent
        $this->assertTrue($order->isActive());
        
        $order->advanceStatus(); // delivered
        $this->assertFalse($order->isActive());
    }

    public function test_value_objects_are_immutable(): void
    {
        $money1 = new Money(100.0);
        $money2 = $money1->add(new Money(50.0));
        
        $this->assertEquals(100.0, $money1->amount());
        $this->assertEquals(150.0, $money2->amount());
        $this->assertNotSame($money1, $money2);
    }

    public function test_domain_validations(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new Money(-100);
    }
} 