<?php

use App\Http\Controllers\Api\V1\ConvocatoriaApiController;
use App\Http\Controllers\Api\V1\NewsApiController;
use Illuminate\Support\Facades\Route;

/*
|------------------------------------------------------------------------------
| API publica de solo lectura
|------------------------------------------------------------------------------
|
| Este archivo NO se carga siempre: bootstrap/app.php solo lo registra cuando
| config('api.enabled') es true. Con la API apagada estas rutas no existen y
| /api/* devuelve 404. Ver config/api.php para el porque.
|
| Todo lo que hay aqui es GET. La API no crea, no modifica y no borra nada: un
| token filtrado permite leer contenido que ya esta publicado en la web, y no
| mucho mas. Si algun dia hace falta escribir, sera con otro mecanismo y otra
| revision de permisos, no anadiendo un POST a este grupo.
|
| Solo se exponen noticias y convocatorias publicadas. Usuarios, suscriptores
| del boletin y envios de formularios NO tienen ni tendran endpoint: son datos
| personales y no son publicos en ningun sitio del portal.
|
*/

Route::prefix('v1')
    ->middleware([
        'auth:sanctum',
        // El limite va POR TOKEN, no por IP: el throttle de Laravel usa el
        // usuario autenticado como clave cuando lo hay. Asi un integrador que
        // se pase de vueltas no deja sin servicio a los demas.
        'throttle:'.config('api.rate_limit').',1',
    ])
    ->group(function () {
        Route::get('/noticias', [NewsApiController::class, 'index'])->name('api.noticias.index');
        Route::get('/noticias/{slug}', [NewsApiController::class, 'show'])->name('api.noticias.show');

        Route::get('/convocatorias', [ConvocatoriaApiController::class, 'index'])->name('api.convocatorias.index');
        Route::get('/convocatorias/{slug}', [ConvocatoriaApiController::class, 'show'])->name('api.convocatorias.show');
    });
