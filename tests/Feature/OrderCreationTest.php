<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class OrderCreationTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_order_with_valid_data()
    {
        $orderData = [
            'client_name' => 'Jessika Carvajal',
            'items' => [
                [
                    'description' => 'Lomo saltado',
                    'quantity' => 1,
                    'unit_price' => 60
                ],
                [
                    'description' => 'Inka Kola',
                    'quantity' => 2,
                    'unit_price' => 10
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'message' => 'Orden creada exitosamente'
                ])
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'client_name',
                        'status',
                        'total_amount',
                        'created_at',
                        'updated_at',
                        'items' => [
                            '*' => [
                                'id',
                                'description',
                                'quantity',
                                'unit_price',
                                'created_at',
                                'updated_at'
                            ]
                        ]
                    ]
                ]);

        $this->assertDatabaseHas('orders', [
            'client_name' => 'Jessika Carvajal',
            'status' => 'initiated',
            'total_amount' => 80.00
        ]);

        $this->assertDatabaseHas('order_items', [
            'description' => 'Lomo saltado',
            'quantity' => 1,
            'unit_price' => 60.00
        ]);

        $this->assertDatabaseHas('order_items', [
            'description' => 'Inka Kola',
            'quantity' => 2,
            'unit_price' => 10.00
        ]);
    }

    public function test_cannot_create_order_without_client_name()
    {
        $orderData = [
            'items' => [
                [
                    'description' => 'Test Item',
                    'quantity' => 1,
                    'unit_price' => 50
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['client_name']);
    }

    public function test_cannot_create_order_without_items()
    {
        $orderData = [
            'client_name' => 'Test Client'
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['items']);
    }

    public function test_cannot_create_order_with_invalid_item_data()
    {
        $orderData = [
            'client_name' => 'Test Client',
            'items' => [
                [
                    'description' => '',
                    'quantity' => 0,
                    'unit_price' => -10
                ]
            ]
        ];

        $response = $this->postJson('/api/orders', $orderData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors([
                    'items.0.description',
                    'items.0.quantity',
                    'items.0.unit_price'
                ]);
    }
} 