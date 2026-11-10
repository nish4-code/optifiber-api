<?php

namespace App\Console\Commands;

use App\Services\SmartOlt\SmartOltService;
use Illuminate\Console\Command;

class SmartOltSyncCommand extends Command
{
    protected $signature = 'smartolt:sync {--force : Forzar refresco ignorando cache}';

    protected $description = 'Sincroniza el inventario de OLTs y ONUs desde SmartOLT hacia la base de datos local';

    public function handle(SmartOltService $service): int
    {
        $this->info('Iniciando sincronización con SmartOLT...');

        $result = $service->syncToDatabase();

        $this->table(
            ['Parámetro', 'Valor'],
            [
                ['Modo', $result['mode']],
                ['OLTs sincronizadas', $result['olts_synced']],
                ['ONUs sincronizadas', $result['onus_synced']],
                ['Fecha/Hora', $result['synced_at']],
            ]
        );

        $this->info('Sincronización completada correctamente.');

        return Command::SUCCESS;
    }
}
