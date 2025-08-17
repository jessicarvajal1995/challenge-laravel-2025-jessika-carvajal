<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

// Comando para verificar el estado del caché de órdenes activas a manera de prueba.
class CheckCacheCommand extends Command
{
    
    protected $signature = 'cache:check-orders';

    protected $description = 'Verificar el estado del caché de órdenes activas';

    public function handle()
    {
        $cacheKey = 'active_orders';
        
        $this->info('🔍 Verificando estado del caché de órdenes...');
        $this->newLine();
        
        // Verificar si existe el cache
        if (Cache::has($cacheKey)) {
            $this->info("✅ Cache EXISTE para la clave: '{$cacheKey}'");
            
            // Obtener el contenido del cache
            $cachedData = Cache::get($cacheKey);
            $orderCount = is_array($cachedData) ? count($cachedData) : 0;
            
            $this->info("📊 Número de órdenes en caché: {$orderCount}");
            
            // Mostrar las órdenes si hay pocas
            if ($orderCount > 0 && $orderCount <= 5) {
                $this->newLine();
                $this->info('📋 Órdenes en caché:');
                foreach ($cachedData as $order) {
                    $this->line("   - ID: {$order['id']} | Cliente: {$order['client_name']} | Estado: {$order['status']}");
                }
            }
            
        } else {
            $this->warn("❌ Cache NO EXISTE para la clave: '{$cacheKey}'");
            $this->info('💡 Esto significa que:');
            $this->line('   - El cache expiró (TTL: 30 segundos)');
            $this->line('   - Fue borrado al crear/actualizar una orden');
            $this->line('   - Es la primera consulta al sistema');
        }
        
        $this->newLine();
        
        // Información adicional sobre Redis
        $this->info('🔧 Información del sistema de caché:');
        $this->line('   - Driver: ' . config('cache.default'));
        $this->line('   - TTL configurado: 30 segundos');
        
        return Command::SUCCESS;
    }
}
