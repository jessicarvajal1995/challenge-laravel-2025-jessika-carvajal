<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Crear orden 1 - Estado initiated
        $order1 = Order::create([
            'client_name' => 'Jessika Carvajal',
            'status' => Order::STATUS_INITIATED,
            'total_amount' => 85.00,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'description' => 'Lomo saltado',
            'quantity' => 1,
            'unit_price' => 60.00,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'description' => 'Inka Kola',
            'quantity' => 2,
            'unit_price' => 10.00,
        ]);

        OrderItem::create([
            'order_id' => $order1->id,
            'description' => 'Pan con mantequilla',
            'quantity' => 1,
            'unit_price' => 5.00,
        ]);

        // Crear orden 2 - Estado sent
        $order2 = Order::create([
            'client_name' => 'Carlos Gómez',
            'status' => Order::STATUS_SENT,
            'total_amount' => 45.00,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'description' => 'Ceviche de pescado',
            'quantity' => 1,
            'unit_price' => 35.00,
        ]);

        OrderItem::create([
            'order_id' => $order2->id,
            'description' => 'Chicha morada',
            'quantity' => 1,
            'unit_price' => 10.00,
        ]);

        // Crear orden 3 - Estado initiated
        $order3 = Order::create([
            'client_name' => 'María Rodriguez',
            'status' => Order::STATUS_INITIATED,
            'total_amount' => 28.00,
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'description' => 'Causa limeña',
            'quantity' => 2,
            'unit_price' => 12.00,
        ]);

        OrderItem::create([
            'order_id' => $order3->id,
            'description' => 'Pisco sour',
            'quantity' => 1,
            'unit_price' => 4.00,
        ]);
    }
} 