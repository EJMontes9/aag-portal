<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Cierra automaticamente convocatorias vencidas cada hora.
// Requiere que el cron del servidor ejecute: * * * * * php artisan schedule:run
Schedule::command('convocatorias:close-expired')
    ->hourly()
    ->withoutOverlapping()
    ->runInBackground();

// ── Transparencia ───────────────────────────────────────────────────────────
// Los documentos se suben por FTP al subdominio y el portal enlaza cada MES con
// su carpeta, asi que los archivos nuevos de un mes ya publicado aparecen solos
// sin hacer nada.
//
// Lo unico que no se detecta solo es la aparicion de un MES o un AÑO nuevo, que
// es justo lo que hace esta tarea: revisa el subdominio de madrugada y enlaza
// lo que haya aparecido. Asi, subir por FTP es lo unico que hay que hacer.
//
// Es idempotente: si no hay nada nuevo, no toca la base de datos.
Schedule::command('lotaip:sincronizar')
    ->dailyAt('03:30')
    ->withoutOverlapping()
    ->runInBackground();
