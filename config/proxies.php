<?php

/*
|------------------------------------------------------------------------------
| Proxies de confianza
|------------------------------------------------------------------------------
|
| Cuando el sitio esta detras de Cloudflare (o de cualquier proxy inverso), la
| peticion que llega a Apache viene de la IP del proxy, no del visitante. La IP
| real viaja en la cabecera X-Forwarded-For.
|
| Si no se declara en quien confiar, Laravel ignora esa cabecera y:
|
|   - Todos los visitantes comparten la IP del proxy. El limite del boletin
|     (5 intentos por IP) pasa a ser global: cinco intentos de cualquiera y el
|     formulario queda bloqueado para todo el mundo.
|   - La ip_address que guardamos de cada suscriptor es la del proxy. Como
|     registro de consentimiento no vale nada.
|   - Laravel no ve que la peticion original era HTTPS y puede generar enlaces
|     http://, que Cloudflare rebota.
|
| Pero confiar a ciegas es peor: si se confia en cualquier IP, cualquiera que
| alcance el servidor puede mandar un X-Forwarded-For inventado y saltarse los
| limites o falsear el registro. Por eso el valor por defecto es no confiar en
| nadie, y se activa a proposito.
|
| Se configura con TRUSTED_PROXIES en el .env:
|
|   (vacio)      No se confia en ningun proxy. Correcto si el dominio apunta
|                directo al hosting, sin Cloudflare de por medio.
|
|   cloudflare   Se confia solo en los rangos de Cloudflare de este archivo.
|                Es la opcion recomendada cuando Cloudflare esta en modo proxy
|                (el icono de la nube naranja en el panel de DNS).
|
|   *            Se confia en cualquier proxy. Usar SOLO si el servidor es
|                inalcanzable salvo a traves del proxy. En hosting compartido
|                normalmente NO se cumple: la IP del servidor suele ser
|                accesible directamente, asi que esto abre el agujero descrito
|                arriba.
|
|   1.2.3.4,...  Lista de IPs o rangos CIDR separados por comas.
|
| Los rangos de Cloudflare cambian muy de vez en cuando. La lista oficial esta
| en https://www.cloudflare.com/ips/ — conviene revisarla si algun dia los
| limites de peticiones empiezan a comportarse de forma rara.
|
*/

return [

    'trusted' => env('TRUSTED_PROXIES', ''),

    /*
     * Rangos publicos de Cloudflare. Actualizados en julio de 2026.
     */
    'cloudflare' => [
        // IPv4
        '173.245.48.0/20',
        '103.21.244.0/22',
        '103.22.200.0/22',
        '103.31.4.0/22',
        '141.101.64.0/18',
        '108.162.192.0/18',
        '190.93.240.0/20',
        '188.114.96.0/20',
        '197.234.240.0/22',
        '198.41.128.0/17',
        '162.158.0.0/15',
        '104.16.0.0/13',
        '104.24.0.0/14',
        '172.64.0.0/13',
        '131.0.72.0/22',
        // IPv6
        '2400:cb00::/32',
        '2606:4700::/32',
        '2803:f800::/32',
        '2405:b500::/32',
        '2405:8100::/32',
        '2a06:98c0::/29',
        '2c0f:f248::/32',
    ],

];
