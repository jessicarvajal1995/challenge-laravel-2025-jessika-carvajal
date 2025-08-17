<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class GenerateSwaggerDocs extends Command
{

    protected $signature = 'swagger:generate
                            {--output=storage/api-docs/api-docs.json : Archivo de salida para la documentación}
                            {--source=app/ : Directorio fuente para escanear anotaciones}';

   
    protected $description = 'Genera la documentación OpenAPI/Swagger a partir de las anotaciones del código';

    public function handle(): int
    {
        $this->info('🚀 Generando documentación OpenAPI/Swagger...');
        
        $output = $this->option('output');
        $source = $this->option('source');
        
        // Crear directorio si no existe
        $outputDir = dirname($output);
        if (!is_dir($outputDir)) {
            $this->info("📁 Creando directorio: {$outputDir}");
            mkdir($outputDir, 0755, true);
        }
        
        // Comando para generar documentación
        $command = [
            base_path('vendor/bin/openapi'),
            '--output',
            $output,
            base_path($source)
        ];
        
        try {
            $process = new Process($command);
            $process->setTimeout(60); // 60 segundos timeout
            $process->run();
            
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }
            
            // Verificar que el archivo se generó correctamente
            if (file_exists($output)) {
                $fileSize = filesize($output);
                $this->info("✅ Documentación generada exitosamente");
                $this->info("📄 Archivo: {$output}");
                $this->info("📊 Tamaño: " . $this->formatBytes($fileSize));
                
                // Mostrar información adicional
                $this->newLine();
                $this->info('🔗 Acceder a la documentación:');
                $this->line('   • Swagger UI: ' . config('app.url') . '/api/documentation');
                $this->line('   • JSON API:   ' . config('app.url') . '/api/docs');
                
                return Command::SUCCESS;
            } else {
                $this->error('❌ Error: El archivo de documentación no se generó');
                return Command::FAILURE;
            }
            
        } catch (ProcessFailedException $exception) {
            $this->error('❌ Error al generar la documentación:');
            $this->error($exception->getMessage());
            return Command::FAILURE;
        } catch (\Exception $exception) {
            $this->error('❌ Error inesperado:');
            $this->error($exception->getMessage());
            return Command::FAILURE;
        }
    }
    
    /**
     * Formatear bytes en formato legible
     */
    private function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, $precision) . ' ' . $units[$i];
    }
} 