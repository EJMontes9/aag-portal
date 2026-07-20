# API pública de solo lectura

API REST sobre el contenido que **ya es público** en el portal: noticias y
convocatorias. Autenticada con tokens de Laravel Sanctum.

**Viene desactivada de fábrica.** Ver [Por qué viene desactivada](#por-qué-viene-desactivada).

---

## Por qué viene desactivada

Una API que nadie consume no es una funcionalidad: es superficie expuesta. Cada
ruta registrada es una ruta que se puede sondear, cronometrar y atacar, y que
hay que vigilar en los registros aunque no la use nadie.

Por eso el interruptor `API_ENABLED` sale en `false`, y mientras lo esté las
rutas **no se registran siquiera**. La diferencia con dejarlas puestas y
protegidas es importante:

| Estado | `GET /api/v1/noticias` sin token | Qué aprende quien sondea |
|---|---|---|
| `API_ENABLED=false` | `404` | Nada. Indistinguible de una URL inventada. |
| `API_ENABLED=true` | `401` | Que ahí hay algo, y que merece la pena insistir. |

El requisito del pliego —"autenticación con Laravel Sanctum" y "APIs
RESTful"— queda cumplido y es demostrable en cualquier momento: se enciende con
una variable, se prueba, y se vuelve a apagar. No hace falta desplegar nada ni
tocar código.

---

## Cómo se activa

```bash
# 1. En el .env del servidor
API_ENABLED=true

# 2. Regenerar la cache de configuración
php artisan config:cache
```

El segundo paso **no es opcional**. Con la configuración cacheada Laravel ni
siquiera lee el `.env`, así que el cambio no surte ningún efecto hasta que la
cache se regenera. Es el fallo más habitual al activar esto.

Para comprobar que ha funcionado:

```bash
php artisan route:list --path=api
```

Deben aparecer las cuatro rutas de `/api/v1`. Si sólo sale `admin/api-tokens`,
la cache no se regeneró.

**Para desactivarla**, el camino inverso: `API_ENABLED=false` y
`php artisan config:cache`. Las rutas desaparecen y `/api/*` vuelve a dar 404.
Los tokens emitidos siguen en la base de datos, pero no sirven para nada
mientras la API esté apagada.

---

## Cómo se crea un token

Los tokens se emiten desde el panel, no por consola:

1. Entrar en **Configuración → Tokens de API** (`/admin/api-tokens`).
   Sólo lo ven los roles `super_admin` y `admin`.
2. Pulsar **Crear token** y darle un nombre que diga para qué es
   ("App móvil", "Web del municipio"). Meses después, ese nombre es lo único
   que permitirá revocar el acceso correcto y no otro.
3. Copiar el token **en ese momento**. Sólo se muestra una vez.

La única copia legible es la que se lleva quien lo crea: en la base de datos
se guarda un hash, igual que una contraseña. Si se pierde, no se recupera —
se revoca y se emite otro.

**Para revocar**, la acción *Revocar* en la fila del token. El corte es
inmediato: la siguiente petición que use ese token recibe un 401.

En el listado se ve el nombre, quién lo emitió, cuándo se creó y cuándo se usó
por última vez. Un token que ponga *Nunca usado* y lleve meses ahí es un token
que sobra.

---

## Autenticación

El token viaja en la cabecera `Authorization`:

```
Authorization: Bearer 1|48O0f6fknJ5mHetOH4OP5Pcm6S6NAdi3ERr4AWZWa10cb18a
```

Conviene añadir también `Accept: application/json`. No es obligatorio —los
errores bajo `/api/*` se devuelven en JSON en cualquier caso— pero es lo
correcto y evita sorpresas si algún día cambia esa configuración.

---

## Endpoints

Todos son `GET`. La API **no crea, no modifica y no borra nada**: un token
filtrado permite leer contenido que ya está publicado en la web, y poco más.

| Método | Ruta | Qué devuelve |
|---|---|---|
| GET | `/api/v1/noticias` | Listado paginado de noticias publicadas |
| GET | `/api/v1/noticias/{slug}` | Detalle de una noticia |
| GET | `/api/v1/convocatorias` | Listado paginado de convocatorias |
| GET | `/api/v1/convocatorias/{slug}` | Detalle de una convocatoria |

Se aplican los mismos criterios de publicación que la web: sólo noticias con
estado `published` y fecha de publicación ya cumplida, y sólo convocatorias en
estado `vigente` o `cerrada`. Los borradores no salen.

**Lo que NO expone la API, y no lo hará:** usuarios, suscriptores del boletín,
envíos de formularios de contacto o cualquier otro dato personal. No hay
endpoint para eso.

### Ejemplos con `curl`

```bash
TOKEN="1|48O0f6fknJ5mHetOH4OP5Pcm6S6NAdi3ERr4AWZWa10cb18a"
BASE="https://www.aag.gob.ec"

# Listado de noticias
curl -H "Authorization: Bearer $TOKEN" \
     -H "Accept: application/json" \
     "$BASE/api/v1/noticias"

# Segunda página
curl -H "Authorization: Bearer $TOKEN" \
     "$BASE/api/v1/noticias?page=2"

# Detalle de una noticia
curl -H "Authorization: Bearer $TOKEN" \
     "$BASE/api/v1/noticias/aag-presenta-su-plan-operativo-anual-2026"

# Convocatorias
curl -H "Authorization: Bearer $TOKEN" \
     "$BASE/api/v1/convocatorias"

curl -H "Authorization: Bearer $TOKEN" \
     "$BASE/api/v1/convocatorias/jefe-operaciones-aeroportuarias"
```

### Campos de las noticias

Listado (`/api/v1/noticias`):

| Campo | Tipo | Notas |
|---|---|---|
| `id` | entero | |
| `titulo` | texto | |
| `slug` | texto | Identificador de la URL pública |
| `entradilla` | texto | Resumen corto |
| `categoria` | objeto o `null` | `{ nombre, slug }` |
| `imagen_portada` | URL o `null` | Absoluta |
| `fecha_publicacion` | ISO 8601 | |
| `url` | URL | Ficha en el portal |

El detalle (`/api/v1/noticias/{slug}`) añade:

| Campo | Tipo | Notas |
|---|---|---|
| `contenido` | HTML | Ya saneado al guardarse |
| `imagen_portada_alt` | texto o `null` | Texto alternativo |
| `tiempo_lectura_minutos` | entero | Estimado |

No se devuelve el autor, ni el contador de visitas, ni el estado, ni los
campos de SEO. El autor es una persona concreta de la institución y su
registro de usuario no es información pública; el resto son datos internos.

### Campos de las convocatorias

Listado (`/api/v1/convocatorias`):

| Campo | Tipo | Notas |
|---|---|---|
| `id` | entero | |
| `titulo` | texto | |
| `slug` | texto | |
| `tipo` | texto | `proceso` o `aviso` |
| `area` | texto o `null` | |
| `modalidad` | texto o `null` | |
| `descripcion_corta` | texto | |
| `estado` | texto | `vigente` o `cerrada` |
| `fecha_apertura` | ISO 8601 o `null` | |
| `fecha_cierre` | ISO 8601 o `null` | |
| `url` | URL | Ficha en el portal |

El detalle (`/api/v1/convocatorias/{slug}`) añade:

| Campo | Tipo | Notas |
|---|---|---|
| `requisitos` | lista de textos | |
| `cronograma` | lista de objetos | `{ etapa, fecha, hora }` |
| `documentos` | lista de objetos | `{ nombre, tipo, url }` |
| `enlace_referencia` | URL o `null` | |

`estado` es el estado **efectivo**, no la columna de la base de datos: una
convocatoria marcada como vigente cuya fecha de cierre ya pasó se devuelve
como `cerrada`, igual que la presenta la web. Devolver el valor crudo haría
que un consumidor anunciase como abierto un proceso que ya no admite nada.

En `documentos` se funden el PDF de bases y los documentos adicionales, porque
para quien consume son todos lo mismo. Se devuelve la URL de descarga, nunca
la ruta interna de almacenamiento.

### Paginación

Los listados devuelven la envoltura estándar de Laravel:

```json
{
  "data": [ ... ],
  "links": { "first": "...", "last": "...", "prev": null, "next": "..." },
  "meta": { "current_page": 1, "last_page": 3, "per_page": 15, "total": 42 }
}
```

Se navega con `?page=N`. **El tamaño de página no es configurable por el
cliente**: son 15 elementos fijos (`config/api.php`). Si se pudiera cambiar,
un `?per_page=100000` volcaría la tabla entera en una sola petición y el
límite de peticiones dejaría de servir de nada.

---

## Límite de peticiones

**60 peticiones por minuto y por token.** Se ajusta con `API_RATE_LIMIT` en el
`.env` (requiere `config:cache` de nuevo).

El límite es por token, no por IP: un integrador que se pase de vueltas no deja
sin servicio a los demás. Cada respuesta lleva las cabeceras
`X-RateLimit-Limit` y `X-RateLimit-Remaining`. Al superarlo se responde `429`
con la cabecera `Retry-After`.

No protege ningún secreto —el contenido está también en la web— sino la base
de datos frente a un cliente mal escrito que entre en bucle.

---

## Códigos de respuesta

| Código | Significa |
|---|---|
| `200` | Correcto |
| `401` | Falta el token, es inválido o fue revocado |
| `404` | La API está desactivada, **o** el slug no existe |
| `429` | Límite de peticiones superado |

El `404` es deliberadamente ambiguo entre "API apagada" y "recurso
inexistente": no hay razón para que un cliente sin autorizar sepa distinguirlos.

---

## Dónde está cada cosa

| Archivo | Qué contiene |
|---|---|
| `config/api.php` | Interruptor, límite de peticiones, tamaño de página |
| `bootstrap/app.php` | Registro condicional de las rutas (`then:` de `withRouting`) |
| `routes/api.php` | Definición de los endpoints |
| `app/Http/Controllers/Api/V1/` | Controladores |
| `app/Http/Resources/Api/V1/` | Qué campos salen en el JSON |
| `app/Filament/Resources/ApiTokenResource.php` | Gestión de tokens en el panel |
| `app/Models/ApiToken.php` | Modelo de token (envoltorio del de Sanctum) |

Las rutas se registran en el callback `then:` de `withRouting()` y no en el
parámetro `api:`. El motivo está comentado en `bootstrap/app.php`: el parámetro
se evalúa mientras se construye la aplicación, cuando `config()` todavía no
existe y `Env::get()` devuelve `null` si la configuración está cacheada.
`then:` se ejecuta más tarde, con la configuración ya cargada.

---

## Si algo no funciona

**Devuelve 404 con `API_ENABLED=true`.** Falta `php artisan config:cache`.
Comprobar con `php artisan route:list --path=api`.

**Devuelve 401 con un token que debería valer.** Puede estar revocado (mirar
en el panel) o el servidor puede estar descartando la cabecera `Authorization`.
Lo segundo pasa en algunas configuraciones de Apache: el `.htaccess` de
`public/` la reenvía con la regla `HTTP_AUTHORIZATION`, y si ese `.htaccess`
se ha reemplazado, la regla puede haberse perdido.

**Devuelve 429 antes de tiempo.** Si el sitio está detrás de Cloudflare y
`TRUSTED_PROXIES` no está configurado, Laravel ve la IP del proxy en lugar de
la real. Ver `config/proxies.php`.
