# 🧪 OlaClick Backend Challenge - Laravel Edition

## 🎯 Objetivo

API RESTful para la gestión de órdenes de un restaurante, implementada en **Laravel 10** siguiendo **Arquitectura Hexagonal (Ports & Adapters)**. Utiliza **PostgreSQL** como base de datos, **Redis** para caché, y está completamente **desacoplada** de Laravel/Eloquent mediante **Domain-Driven Design (DDD)**.

---

## 🏗️ Arquitectura Hexagonal Implementada

### **Separación por Capas**

Esta implementación sigue el patrón **Ports & Adapters** para lograr:
- ✅ **Lógica de dominio independiente** del framework
- ✅ **Testabilidad** sin dependencias externas  
- ✅ **Mantenibilidad** con separación clara de responsabilidades
- ✅ **Escalabilidad** y facilidad para cambios

### **Estructura de la Arquitectura**

```
app/
├── Domain/                    # 🎯 CAPA DE DOMINIO (Sin Laravel)
│   ├── Entities/             # Entidades con lógica de negocio
│   │   ├── Order.php
│   │   └── OrderItem.php
│   ├── ValueObjects/         # Objetos de valor inmutables
│   │   ├── OrderId.php
│   │   ├── OrderStatus.php
│   │   └── Money.php
│   ├── Contracts/            # Interfaces del dominio
│   │   ├── OrderRepositoryInterface.php
│   │   ├── CacheInterface.php
│   │   └── LoggerInterface.php
│   └── Exceptions/           # Excepciones del dominio
│       └── InvalidOrderStatusTransition.php
│
├── Application/              # 🚀 CAPA DE APLICACIÓN
│   ├── UseCases/            # Casos de uso del negocio
│   │   ├── CreateOrderUseCase.php
│   │   ├── GetActiveOrdersUseCase.php
│   │   └── AdvanceOrderStatusUseCase.php
│   ├── Commands/            # Comandos de entrada
│   │   ├── CreateOrderCommand.php
│   │   └── AdvanceOrderStatusCommand.php
│   └── DTOs/               # Objetos de transferencia
│       ├── OrderDto.php
│       └── OrderItemDto.php
│
├── Infrastructure/          # 🔧 CAPA DE INFRAESTRUCTURA (Laravel)
│   ├── Persistence/         # Adaptadores de persistencia
│   │   └── EloquentOrderRepository.php
│   ├── Cache/              # Adaptadores de caché
│   │   └── LaravelCacheAdapter.php
│   └── Logging/            # Adaptadores de logging
│       └── LaravelLoggerAdapter.php
│
├── Http/                    # 🌐 CAPA DE PRESENTACIÓN
│   ├── Controllers/Api/     
│   │   └── OrderController.php
│   └── Requests/
│       └── CreateOrderRequest.php
│
└── Models/                  # 📊 MODELOS ELOQUENT (Solo para BD)
    ├── Order.php           # Modelo Eloquent tradicional
    └── OrderItem.php
```

---

## 🚀 Instalación y Configuración

### Prerrequisitos
- Docker y Docker Compose instalados
- Git

### 1. Clonar el repositorio
```bash
git clone <repository-url>
cd challenge-laravel-2025
```

### 2. Levantar los contenedores
```bash
docker compose up -d --build
```

### 3. Ejecutar migraciones y seeders
```bash
docker compose exec app php artisan migrate:fresh --seed
```

### 4. Verificar que todo funciona
```bash
curl -X GET http://localhost:8000/api/orders
```

---

## 📚 Documentación de la API

### **🔥 Swagger/OpenAPI 3.0 - Documentación Interactiva**

La API cuenta con **documentación completa e interactiva** usando **Swagger/OpenAPI 3.0** que complementa este README:

#### **🌐 Acceder a la documentación:**
```
🔗 Swagger UI Interactiva: http://localhost:8000/api/documentation
📄 JSON OpenAPI:          http://localhost:8000/api/docs
```

#### **✨ Características de la documentación Swagger:**
- ✅ **Interfaz interactiva** - Prueba endpoints directamente desde el navegador
- ✅ **Esquemas de datos** completos con validaciones
- ✅ **Ejemplos en tiempo real** de requests y responses
- ✅ **Códigos de respuesta** documentados (200, 201, 422, 500, etc.)
- ✅ **Descripción detallada** de cada endpoint y parámetro
- ✅ **Try it out** - Ejecuta requests reales contra la API
- ✅ **Responsive design** - Funciona perfecto en móviles

#### **🛠️ Regenerar documentación:**
```bash
# Comando artisan personalizado
docker compose exec app php artisan swagger:generate

# O usar directamente swagger-php
docker compose exec app vendor/bin/openapi --output storage/api-docs/api-docs.json app/
```


## 📋 Endpoints de la API

### Base URL
```
http://localhost:8000/api
```

### 1. **Listar órdenes activas** 
```http
GET /orders
```

**Descripción:** Retorna todas las órdenes activas (status != 'delivered'). Usa Redis para cachear el resultado (TTL: 30s).

**Respuesta:**
```json
{
  "success": true,
  "message": "Órdenes activas obtenidas exitosamente",
  "data": [
    {
      "id": 1,
      "client_name": "Jessika Carvajal",
      "status": "initiated",
      "total_amount": "85.00",
      "created_at": "2025-08-17T00:14:12.000000Z",
      "updated_at": "2025-08-17T00:14:12.000000Z",
      "items": [
        {
          "id": 1,
          "description": "Lomo saltado",
          "quantity": 1,
          "unit_price": "60.00"
        }
      ]
    }
  ]
}
```

### 2. **Crear una nueva orden**
```http
POST /orders
Content-Type: application/json
```

**Body:**
```json
{
  "client_name": "Jessika Carvajal",
  "items": [
    {
      "description": "Lomo saltado",
      "quantity": 1,
      "unit_price": 60
    },
    {
      "description": "Inka Kola",
      "quantity": 2,
      "unit_price": 10
    }
  ]
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Orden creada exitosamente",
  "data": {
    "id": 4,
    "client_name": "Jessika Carvajal",
    "status": "initiated",
    "total_amount": "80.00",
    "items": [...]
  }
}
```

### 3. **Ver detalle de una orden**
```http
GET /orders/{id}
```

**Ejemplo:**
```bash
curl -X GET http://localhost:8000/api/orders/1
```

### 4. **Avanzar estado de una orden**
```http
POST /orders/{id}/advance
```

**Flujo de estados:**
- `initiated` → `sent` → `delivered`
- Cuando llega a `delivered`, la orden se elimina de la base de datos y del caché

**Ejemplo:**
```bash
curl -X POST http://localhost:8000/api/orders/1/advance
```

---

## 🧪 Ejemplos de Uso Completos

### Crear una orden nueva
```bash
curl -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{
    "client_name": "Juan Pérez",
    "items": [
      {
        "description": "Pizza Margherita",
        "quantity": 1,
        "unit_price": 25.50
      },
      {
        "description": "Coca Cola",
        "quantity": 2,
        "unit_price": 8.00
      }
    ]
  }'
```

### Avanzar una orden paso a paso
```bash
# 1. Crear orden (status: initiated)
ORDER_ID=$(curl -s -X POST http://localhost:8000/api/orders \
  -H "Content-Type: application/json" \
  -d '{"client_name": "Test", "items": [{"description": "Test", "quantity": 1, "unit_price": 10}]}' \
  | jq -r '.data.id')

# 2. Avanzar a "sent"
curl -X POST http://localhost:8000/api/orders/$ORDER_ID/advance

# 3. Avanzar a "delivered" (se elimina)
curl -X POST http://localhost:8000/api/orders/$ORDER_ID/advance
```

---


### **Flujo de Datos**
```
HTTP Request → Controller → Use Case → Domain Entity → Repository Interface → Eloquent Adapter → Database
```

---

## 🧪 Tests

### Ejecutar tests de dominio (sin Laravel)
```bash
docker compose exec app php vendor/bin/phpunit tests/Unit/Domain/OrderTest.php
```

### Resultado esperado:
```
PHPUnit 10.5.52 by Sebastian Bergmann and contributors.
Runtime: PHP 8.3.24

.......                                                             7 / 7 (100%)
Time: 00:00.005, Memory: 10.00 MB
OK (7 tests, 18 assertions)
```

### Tests incluidos
- ✅ **Entidades de dominio** puras (sin BD)
- ✅ **Value Objects** inmutables
- ✅ **Transiciones de estado** válidas
- ✅ **Validaciones de negocio**
- ✅ **Cálculos de dominio**

---

## 🗄️ Base de Datos

### Modelo de datos

**Tabla `orders`:**
- `id` (Primary Key)
- `client_name` (String)
- `status` (Enum: initiated, sent, delivered)
- `total_amount` (Decimal)
- `timestamps`
- `soft_deletes`

**Tabla `order_items`:**
- `id` (Primary Key)
- `order_id` (Foreign Key)
- `description` (String)
- `quantity` (Integer)
- `unit_price` (Decimal)
- `timestamps`

---

## ⚡ Cache con Redis

- **Endpoint cacheado:** `GET /api/orders`
- **TTL:** 30 segundos
- **Invalidación:** Automática al crear, actualizar o eliminar órdenes
- **Configuración:** Redis container incluido en Docker Compose

---

## 🐳 Docker

### Servicios incluidos

1. **App (PHP 8.3-FPM)**
   - Laravel 10
   - Extensiones: PostgreSQL, Redis, GD, ZIP, etc.

2. **Nginx**
   - Proxy reverso para PHP-FPM
   - Puerto: 8000

3. **PostgreSQL 15**
   - Base de datos principal
   - Puerto: 5433 (mapeado)

4. **Redis**
   - Cache y sesiones
   - Puerto: 6380 (mapeado)

### Comandos útiles

```bash
# Levantar servicios
docker compose up -d

# Ver logs
docker compose logs -f app

# Ejecutar comandos Artisan
docker compose exec app php artisan migrate

# Acceder al contenedor
docker compose exec app bash

# Detener servicios
docker compose down
```

---

## 🔧 Desarrollo

### Comandos útiles

```bash
# Limpiar cache
docker compose exec app php artisan cache:clear
docker compose exec app php artisan config:clear

# Verificar estado del caché de órdenes activas
docker compose exec app php artisan cache:check-orders

# Ver rutas
docker compose exec app php artisan route:list

# Generar nueva migración
docker compose exec app php artisan make:migration create_example_table

# Rollback migraciones
docker compose exec app php artisan migrate:rollback

# 📚 Documentación Swagger
# Regenerar documentación OpenAPI/Swagger
docker compose exec app php artisan swagger:generate

# Verificar documentación JSON
curl -X GET http://localhost:8000/api/docs | jq

# Acceder a Swagger UI
open http://localhost:8000/api/documentation
```

---

## 📝 Validaciones

### Crear Orden
- `client_name`: Requerido, string, máximo 255 caracteres
- `items`: Requerido, array, mínimo 1 item
- `items.*.description`: Requerido, string, máximo 255 caracteres  
- `items.*.quantity`: Requerido, entero, mínimo 1
- `items.*.unit_price`: Requerido, numérico, mínimo 0.01

---

## 🚦 Estados de Órdenes

```
initiated → sent → delivered (eliminada)
     ↓        ↓         ↓
   Creada  Enviada  Entregada
```

---

## 🎯 Características Técnicas

### **Framework & Lenguaje**
- ✅ **Laravel 10** con **PHP 8.3**
- ✅ **Arquitectura Hexagonal** (Ports & Adapters)
- ✅ **Domain-Driven Design (DDD)**

### **Base de Datos & Cache**
- ✅ **PostgreSQL 15** como base de datos
- ✅ **Redis** para caché distribuido (TTL: 30s)
- ✅ **Eloquent ORM** solo en capa de infraestructura

### **Patrones & Principios**
- ✅ **Principios SOLID** aplicados estrictamente
- ✅ **Command Query Responsibility Segregation (CQRS)**
- ✅ **Repository Pattern** con interfaces abstractas
- ✅ **Use Cases** para lógica de aplicación
- ✅ **Value Objects** inmutables
- ✅ **Dependency Injection** completa

### **Testing & Calidad**
- ✅ **Tests unitarios** sin dependencias externas
- ✅ **Tests de dominio** completamente puros
- ✅ **Arquitectura testeable** y mantenible

### **Infraestructura**
- ✅ **Docker** completamente contenedorizado
- ✅ **PHP 8.3** con características modernas
- ✅ **Readonly classes** y **promoted properties**
- ✅ **Soft Deletes** para auditoria

### **Escalabilidad**
- ✅ **Desacoplamiento** total de framework
- ✅ **Portable** a cualquier infraestructura
- ✅ **Intercambiable** (BD, Cache, Logging)
- ✅ **Microservicios ready**

---

## ❓ Preguntas Opcionales

### **¿Qué estrategia seguirías para desacoplar la lógica del dominio de Laravel/Eloquent?**

Implementé **Arquitectura Hexagonal**: dominio puro (VO inmutables, entidades ricas, excepciones), aplicación (use cases + DTOs) y infraestructura (adaptadores de Repository/Cache/Logger sobre Eloquent/Laravel). Con **PHP 8+** aprovecho `readonly`/typing. Los tests son unitarios y rápidos (sin framework). **Resultado**: dominio independiente, código expresivo y fácil de migrar/mantener.

### **¿Cómo manejarías versiones de la API en producción?**

Usaría **versionado por path** (`/api/v1`, `/api/v2`) y mantendría **2–3 versiones activas**. La arquitectura hexagonal me permitiría definir DTOs/mappers por versión sin tocar el dominio. Definiría una **deprecación a 6 meses**, agregaría headers informativos e instrumentaría métricas por versión. Complementaría con **tags Git + SemVer** que dispararían pipelines: build de imágenes Docker por versión mayor, deploys paralelos y rollback automático.

### **¿Cómo asegurarías que esta API escale ante alta concurrencia?**

Primero **mediría y atacaría cuellos de botella** de forma incremental. La hexagonal me permitiría intercambiar adaptadores: repositorios optimizados (o CQRS), cache + read replicas, colas/async, rate limiting y circuit breakers; además habilitaría autoscaling y observabilidad. **Objetivo**: 10k+ req/min, p95 < 200 ms y 99.9% de disponibilidad.

---

## 👥 Autor

**Jessika Carvajal** - Desarrollado para OlaClick Backend Challenge

---

**¡La API con Arquitectura Hexagonal está lista para producción! 🚀**
