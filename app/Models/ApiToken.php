<?php

namespace App\Models;

use Laravel\Sanctum\PersonalAccessToken;

/**
 * Token de acceso a la API publica.
 *
 * Es el PersonalAccessToken de Sanctum sin cambios de comportamiento: la misma
 * tabla, la misma logica. Existe solo para tener un modelo dentro de App\Models
 * al que colgar un Resource de Filament y su Policy.
 *
 * Hace falta porque el panel deniega por defecto cualquier recurso sin policy
 * (ver AdminPanelProvider::boot), y una policy sobre una clase de vendor no se
 * descubre sola ni la genera Shield. Con el modelo aqui, ApiTokenPolicy y los
 * permisos se generan igual que en el resto del panel.
 *
 * El token en claro NO vive en esta tabla: la columna `token` guarda un hash.
 * Por eso solo se puede mostrar una vez, en el momento de crearlo.
 */
class ApiToken extends PersonalAccessToken
{
    protected $table = 'personal_access_tokens';
}
