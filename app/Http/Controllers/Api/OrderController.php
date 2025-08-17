<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateOrderRequest;
use App\Application\UseCases\CreateOrderUseCase;
use App\Application\UseCases\GetActiveOrdersUseCase;
use App\Application\UseCases\AdvanceOrderStatusUseCase;
use App\Application\Commands\CreateOrderCommand;
use App\Application\Commands\AdvanceOrderStatusCommand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

#[OA\Info(
    version: '1.0.0',
    title: 'OlaClick Restaurant Orders API',
    description: 'API RESTful para la gestión de órdenes de un restaurante, implementada en Laravel 10 siguiendo Arquitectura Hexagonal (Ports & Adapters). Utiliza PostgreSQL como base de datos, Redis para caché, y está completamente desacoplada de Laravel/Eloquent mediante Domain-Driven Design (DDD).',
    contact: new OA\Contact(
        name: 'Jessika Carvajal',
        email: 'jessikacarvajal29@gmail.com'
    )
)]
#[OA\Server(
    url: 'http://localhost:8000',
    description: 'Servidor Local de Desarrollo'
)]
#[OA\Tag(
    name: 'Orders',
    description: 'Operaciones para la gestión de órdenes del restaurante'
)]
class OrderController extends Controller
{
    public function __construct(
        private CreateOrderUseCase $createOrderUseCase,
        private GetActiveOrdersUseCase $getActiveOrdersUseCase,
        private AdvanceOrderStatusUseCase $advanceOrderStatusUseCase
    ) {}

    #[OA\Get(
        path: '/api/orders',
        operationId: 'getActiveOrders',
        summary: 'Obtener órdenes activas',
        description: 'Retorna todas las órdenes activas (status != \'delivered\'). Utiliza Redis para cachear el resultado con TTL de 30 segundos.',
        tags: ['Orders']
    )]
    #[OA\Response(
        response: 200,
        description: 'Lista de órdenes activas obtenida exitosamente',
        content: new OA\JsonContent(
            properties: [
                'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                'message' => new OA\Property(property: 'message', type: 'string', example: 'Órdenes activas obtenidas exitosamente'),
                'data' => new OA\Property(
                    property: 'data',
                    type: 'array',
                    items: new OA\Items(ref: '#/components/schemas/Order')
                ),
                'total' => new OA\Property(property: 'total', type: 'integer', example: 2)
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Error interno del servidor',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    public function index(): JsonResponse
    {
        try {
            $orders = $this->getActiveOrdersUseCase->execute();
            
            return response()->json([
                'success' => true,
                'message' => 'Órdenes activas obtenidas exitosamente',
                'data' => array_map(fn($order) => $order->toArray(), $orders),
                'total' => count($orders)
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las órdenes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Post(
        path: '/api/orders',
        operationId: 'createOrder',
        summary: 'Crear nueva orden',
        description: 'Crea una nueva orden con estado inicial \'initiated\'. La orden debe incluir el nombre del cliente y al menos un item.',
        tags: ['Orders']
    )]
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(ref: '#/components/schemas/CreateOrderRequest')
    )]
    #[OA\Response(
        response: 201,
        description: 'Orden creada exitosamente',
        content: new OA\JsonContent(
            properties: [
                'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                'message' => new OA\Property(property: 'message', type: 'string', example: 'Orden creada exitosamente'),
                'data' => new OA\Property(property: 'data', ref: '#/components/schemas/Order')
            ]
        )
    )]
    #[OA\Response(
        response: 422,
        description: 'Error de validación',
        content: new OA\JsonContent(ref: '#/components/schemas/ValidationErrorResponse')
    )]
    #[OA\Response(
        response: 500,
        description: 'Error interno del servidor',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    public function store(CreateOrderRequest $request): JsonResponse
    {
        try {
            $command = new CreateOrderCommand(
                $request->input('client_name'),
                $request->input('items')
            );
            
            $orderDto = $this->createOrderUseCase->execute($command);
            
            return response()->json([
                'success' => true,
                'message' => 'Orden creada exitosamente',
                'data' => $orderDto->toArray()
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al crear la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    #[OA\Get(
        path: '/api/orders/{id}',
        operationId: 'getOrderById',
        summary: 'Obtener detalle de una orden',
        description: 'Muestra el detalle completo de una orden específica. **Nota: Este endpoint aún no está implementado con la nueva arquitectura hexagonal.**',
        tags: ['Orders']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'ID único de la orden',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Detalle de la orden obtenido exitosamente',
        content: new OA\JsonContent(
            properties: [
                'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                'message' => new OA\Property(property: 'message', type: 'string', example: 'Orden obtenida exitosamente'),
                'data' => new OA\Property(property: 'data', ref: '#/components/schemas/Order')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Orden no encontrada',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    #[OA\Response(
        response: 501,
        description: 'Método no implementado',
        content: new OA\JsonContent(
            properties: [
                'success' => new OA\Property(property: 'success', type: 'boolean', example: false),
                'message' => new OA\Property(property: 'message', type: 'string', example: 'Método no implementado con la nueva arquitectura')
            ]
        )
    )]
    public function show(int $id): JsonResponse
    {
        // TODO: Implementar GetOrderByIdUseCase
        return response()->json([
            'success' => false,
            'message' => 'Método no implementado con la nueva arquitectura'
        ], 501);
    }

    #[OA\Post(
        path: '/api/orders/{id}/advance',
        operationId: 'advanceOrderStatus',
        summary: 'Avanzar estado de orden',
        description: 'Avanza el estado de una orden siguiendo el flujo: initiated → sent → delivered. Cuando una orden llega al estado \'delivered\', se elimina automáticamente de la base de datos y del caché.',
        tags: ['Orders']
    )]
    #[OA\Parameter(
        name: 'id',
        description: 'ID único de la orden',
        in: 'path',
        required: true,
        schema: new OA\Schema(type: 'integer', minimum: 1, example: 1)
    )]
    #[OA\Response(
        response: 200,
        description: 'Estado de la orden actualizado exitosamente',
        content: new OA\JsonContent(
            properties: [
                'success' => new OA\Property(property: 'success', type: 'boolean', example: true),
                'message' => new OA\Property(property: 'message', type: 'string', example: 'Estado de la orden actualizado exitosamente'),
                'data' => new OA\Property(property: 'data', ref: '#/components/schemas/Order')
            ]
        )
    )]
    #[OA\Response(
        response: 404,
        description: 'Orden no encontrada',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    #[OA\Response(
        response: 422,
        description: 'Transición de estado inválida',
        content: new OA\JsonContent(
            properties: [
                'success' => new OA\Property(property: 'success', type: 'boolean', example: false),
                'message' => new OA\Property(property: 'message', type: 'string', example: 'No se puede avanzar el estado de esta orden')
            ]
        )
    )]
    #[OA\Response(
        response: 500,
        description: 'Error interno del servidor',
        content: new OA\JsonContent(ref: '#/components/schemas/ErrorResponse')
    )]
    public function advanceStatus(int $id): JsonResponse
    {
        try {
            $command = new AdvanceOrderStatusCommand($id);
            $orderDto = $this->advanceOrderStatusUseCase->execute($command);
            
            return response()->json([
                'success' => true,
                'message' => 'Estado de la orden actualizado exitosamente',
                'data' => $orderDto->toArray()
            ], 200);
        } catch (\DomainException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado de la orden',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

// Esquemas de datos OpenAPI
#[OA\Schema(
    schema: 'Order',
    title: 'Order',
    description: 'Orden del restaurante',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'client_name', type: 'string', example: 'Jessika Carvajal'),
        new OA\Property(property: 'status', type: 'string', enum: ['initiated', 'sent', 'delivered'], example: 'initiated'),
        new OA\Property(property: 'total_amount', type: 'string', example: '85.00'),
        new OA\Property(property: 'created_at', type: 'string', format: 'date-time', example: '2025-08-17T00:14:12.000000Z'),
        new OA\Property(property: 'updated_at', type: 'string', format: 'date-time', example: '2025-08-17T00:14:12.000000Z'),
        new OA\Property(
            property: 'items',
            type: 'array',
            items: new OA\Items(ref: '#/components/schemas/OrderItem')
        )
    ]
)]
class OrderSchema {}

#[OA\Schema(
    schema: 'OrderItem',
    title: 'OrderItem',
    description: 'Item de una orden',
    properties: [
        new OA\Property(property: 'id', type: 'integer', example: 1),
        new OA\Property(property: 'description', type: 'string', example: 'Lomo saltado'),
        new OA\Property(property: 'quantity', type: 'integer', example: 1),
        new OA\Property(property: 'unit_price', type: 'string', example: '60.00')
    ]
)]
class OrderItemSchema {}

#[OA\Schema(
    schema: 'CreateOrderRequest',
    title: 'CreateOrderRequest',
    description: 'Datos para crear una nueva orden',
    required: ['client_name', 'items'],
    properties: [
        new OA\Property(property: 'client_name', type: 'string', maxLength: 255, example: 'Jessika Carvajal'),
        new OA\Property(
            property: 'items',
            type: 'array',
            minItems: 1,
            items: new OA\Items(ref: '#/components/schemas/CreateOrderItemRequest')
        )
    ]
)]
class CreateOrderRequestSchema {}

#[OA\Schema(
    schema: 'CreateOrderItemRequest',
    title: 'CreateOrderItemRequest',
    description: 'Datos para crear un item de orden',
    required: ['description', 'quantity', 'unit_price'],
    properties: [
        new OA\Property(property: 'description', type: 'string', maxLength: 255, example: 'Lomo saltado'),
        new OA\Property(property: 'quantity', type: 'integer', minimum: 1, example: 1),
        new OA\Property(property: 'unit_price', type: 'number', format: 'float', minimum: 0.01, example: 60.00)
    ]
)]
class CreateOrderItemRequestSchema {}

#[OA\Schema(
    schema: 'ErrorResponse',
    title: 'ErrorResponse',
    description: 'Respuesta de error estándar',
    properties: [
        new OA\Property(property: 'success', type: 'boolean', example: false),
        new OA\Property(property: 'message', type: 'string', example: 'Error al procesar la solicitud'),
        new OA\Property(property: 'error', type: 'string', example: 'Descripción detallada del error')
    ]
)]
class ErrorResponseSchema {}

#[OA\Schema(
    schema: 'ValidationErrorResponse',
    title: 'ValidationErrorResponse',
    description: 'Respuesta de error de validación',
    properties: [
        new OA\Property(property: 'message', type: 'string', example: 'The given data was invalid.'),
        new OA\Property(
            property: 'errors',
            type: 'object',
            additionalProperties: new OA\AdditionalProperties(
                type: 'array',
                items: new OA\Items(type: 'string')
            ),
            example: [
                'client_name' => ['El nombre del cliente es obligatorio.'],
                'items.0.quantity' => ['La cantidad debe ser al menos 1.']
            ]
        )
    ]
)]
class ValidationErrorResponseSchema {} 