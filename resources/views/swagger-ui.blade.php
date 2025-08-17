<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OlaClick Restaurant Orders API - Documentación</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@5.27.1/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 0;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .header h1 {
            margin: 0;
            font-size: 2.5em;
            font-weight: 300;
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 1.1em;
            opacity: 0.9;
        }
        .api-info {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .info-card {
            background: #f8f9ff;
            padding: 20px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .info-card h3 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 1.2em;
        }
        .info-card p {
            margin: 0;
            color: #666;
            line-height: 1.5;
        }
        .tech-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 20px;
        }
        .badge {
            background: #667eea;
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.9em;
            font-weight: 500;
        }
        #swagger-ui {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .swagger-ui .topbar {
            display: none;
        }
        @media (max-width: 768px) {
            .header h1 {
                font-size: 2em;
            }
            .info-grid {
                grid-template-columns: 1fr;
            }
            .api-info {
                margin: 10px;
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🧪Challenge Jessika Carvajal - OlaClick Restaurant Orders API</h1>
        <p>Documentación Interactiva - Arquitectura Hexagonal con Laravel 10</p>
    </div>

    <div class="api-info">
        <div class="info-grid">
            <div class="info-card">
                <h3>🏗️ Arquitectura</h3>
                <p>Implementada siguiendo <strong>Arquitectura Hexagonal</strong> (Ports & Adapters) con <strong>Domain-Driven Design</strong> para máxima escalabilidad y mantenibilidad.</p>
            </div>
            <div class="info-card">
                <h3>⚡ Performance</h3>
                <p>Redis para caché distribuido con TTL de 30s en órdenes activas. Base de datos PostgreSQL 15 optimizada.</p>
            </div>
            <div class="info-card">
                <h3>🧪 Testabilidad</h3>
                <p>Lógica de dominio completamente independiente del framework. Tests unitarios sin dependencias externas.</p>
            </div>
            <div class="info-card">
                <h3>📊 Estados de Orden</h3>
                <p><code>initiated</code> → <code>sent</code> → <code>delivered</code><br>Al llegar a <em>delivered</em>, la orden se elimina automáticamente.</p>
            </div>
        </div>

        <div class="tech-badges">
            <span class="badge">Laravel 10</span>
            <span class="badge">PHP 8.3</span>
            <span class="badge">PostgreSQL 15</span>
            <span class="badge">Redis</span>
            <span class="badge">Docker</span>
            <span class="badge">OpenAPI 3.0</span>
            <span class="badge">Hexagonal Architecture</span>
            <span class="badge">DDD</span>
        </div>
    </div>

    <div id="swagger-ui"></div>

    <script src="https://unpkg.com/swagger-ui-dist@5.27.1/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@5.27.1/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            // Configuración de Swagger UI
            const ui = SwaggerUIBundle({
                url: '/api/docs',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                defaultModelsExpandDepth: 2,
                defaultModelExpandDepth: 2,
                docExpansion: "list",
                filter: true,
                showExtensions: true,
                showCommonExtensions: true,
                tryItOutEnabled: true,
                requestInterceptor: function(request) {
                    request.headers['Accept'] = 'application/json';
                    request.headers['Content-Type'] = 'application/json';
                    return request;
                },
                onComplete: function() {
                    console.log('📚 Documentación API cargada exitosamente');
                },
                onFailure: function(data) {
                    console.error('❌ Error cargando documentación:', data);
                }
            });

            // Configuración adicional para mejorar UX
            setTimeout(() => {
                // Scroll suave al hacer clic en enlaces internos
                document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                    anchor.addEventListener('click', function (e) {
                        e.preventDefault();
                        const target = document.querySelector(this.getAttribute('href'));
                        if (target) {
                            target.scrollIntoView({
                                behavior: 'smooth',
                                block: 'start'
                            });
                        }
                    });
                });
            }, 1000);
        };

        // Configurar tema oscuro basado en preferencias del sistema
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.body.style.background = '#1a1a1a';
        }
    </script>
</body>
</html> 