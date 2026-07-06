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
