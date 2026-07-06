<?php

namespace App\Console\Commands;

use App\Models\Convocatoria;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

class CloseExpiredConvocatorias extends Command
{
    protected $signature = 'convocatorias:close-expired {--dry-run : Mostrar sin aplicar cambios}';

    protected $description = 'Marca como cerradas las convocatorias cuya fecha de cierre ya paso.';

    public function handle(): int
    {
        $expired = Convocatoria::query()
            ->where('status', 'vigente')
            ->whereNotNull('closes_at')
            ->where('closes_at', '<=', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No hay convocatorias vencidas para cerrar.');
            return self::SUCCESS;
        }

        $this->info("Convocatorias vencidas detectadas: {$expired->count()}");
        foreach ($expired as $c) {
            $this->line("  - #{$c->id} \"{$c->title}\" (cerro {$c->closes_at->diffForHumans()})");
        }

        if ($this->option('dry-run')) {
            $this->warn('Dry-run: no se aplican cambios. Quita --dry-run para ejecutar.');
            return self::SUCCESS;
        }

        $updated = Convocatoria::query()
            ->where('status', 'vigente')
            ->whereNotNull('closes_at')
            ->where('closes_at', '<=', now())
            ->update(['status' => 'cerrada']);

        Cache::forget('home_convocatoria');

        $this->info("Cerradas $updated convocatorias.");
        return self::SUCCESS;
    }
}
