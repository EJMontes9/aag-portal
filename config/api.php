<?php

/*
|------------------------------------------------------------------------------
| API publica de solo lectura
|------------------------------------------------------------------------------
|
| El portal expone una API REST de SOLO LECTURA sobre el contenido que ya es
| publico en la web (noticias y convocatorias), protegida con tokens de
| Laravel Sanctum.
|
| Viene DESACTIVADA por defecto y a proposito. Un endpoint que nadie consume
| es solo superficie expuesta: cada ruta registrada es una ruta que se puede
| sondear, medir y atacar. Mientras no haya un consumidor real, lo correcto es
| que no exista. Cuando alguien la necesite se enciende con una variable y un
| comando, sin tocar codigo ni volver a desplegar.
|
| Se activa con:
|
|   API_ENABLED=true    en el .env
|   php artisan config:cache
|
| El segundo paso NO es opcional en produccion: si la configuracion esta
| cacheada, el .env ni siquiera se lee, y el cambio no tiene ningun efecto
| hasta que se regenera la cache.
|
| ── Por que el valor se lee AQUI y no con env() ─────────────────────────────
| env() solo funciona mientras el .env esta cargado. Con config:cache aplicado
| Laravel se salta la lectura del .env por completo, y cualquier env() fuera de
| un archivo de config devuelve null. Un null aqui significaria "API apagada"
| justo en el entorno donde deberia estar encendida, o al reves segun el valor
| por defecto: en cualquier caso, un fallo silencioso. Los archivos de config
| son el unico sitio donde env() es seguro, porque se evaluan antes de que la
| cache se escriba y su resultado es lo que queda congelado dentro.
|
*/

return [

    /*
     * Interruptor general. Con esto en false las rutas de la API NO se
     * registran: responden 404, no 401.
     *
     * La diferencia importa. Un 401 confirma a quien sondea que el endpoint
     * existe y que hay algo detras que merece autenticarse; un 404 es
     * indistinguible de una URL inventada y no da ninguna pista.
     */
    'enabled' => env('API_ENABLED', false),

    /*
     * Limite de peticiones por token y minuto.
     *
     * La API sirve contenido que tambien esta en la web, asi que el limite no
     * protege un secreto: protege la base de datos de un cliente mal escrito
     * que entre en bucle. 60 por minuto da margen de sobra a cualquier
     * integracion razonable (una sincronizacion recorre unas pocas paginas) y
     * corta en seco a un bucle sin freno.
     */
    'rate_limit' => (int) env('API_RATE_LIMIT', 60),

    /*
     * Elementos por pagina en los listados.
     *
     * El cliente no puede cambiarlo. Si pudiera, un ?per_page=100000 volcaria
     * la tabla entera en una sola peticion y el limite de arriba dejaria de
     * servir de nada.
     */
    'per_page' => 15,

];
