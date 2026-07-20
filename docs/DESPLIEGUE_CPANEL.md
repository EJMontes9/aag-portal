# Despliegue en cPanel — lista de comprobación

Este documento existe porque varias protecciones del portal **dependen de la
configuración del servidor**, no sólo del código. Si se salta un paso, la
protección desaparece en silencio: la web funcionará igual, pero quedará
expuesta.

Marca cada punto al desplegar.

---

## 1. Estructura de carpetas

cPanel sirve `public_html`. El código de la aplicación debe quedar **fuera** de
esa carpeta:

```
/home/USUARIO/
├── public_html/          ← webroot (lo que se ve desde internet)
│   ├── index.php         ← apunta a ../laravel_app
│   ├── .htaccess
│   ├── build/            ← assets compilados
│   ├── fonts/  css/  js/  favicon.ico  robots.txt
│   └── storage → /home/USUARIO/laravel_app/storage/app/public   (symlink)
└── laravel_app/          ← el proyecto completo
```

Nunca subas el proyecto entero dentro de `public_html`: dejaría `.env`,
`vendor/`, `storage/` y `.git/` accesibles desde internet.

## 2. El archivo `.env`

- [ ] Copiar `.env.example` a `.env` y rellenarlo. **La plantilla ya trae los
      valores de producción**; comprueba que siguen así:
      - `APP_ENV=production`
      - `APP_DEBUG=false` ← si queda en `true`, cada error muestra la clave de
        la aplicación y las credenciales de la base de datos
      - `SESSION_SECURE_COOKIE=true` (requiere HTTPS activo)
      - `LOG_LEVEL=error`
- [ ] `APP_URL` con el dominio real y `https://`.
- [ ] Generar clave: `php artisan key:generate`
- [ ] **Permisos del `.env`: `chmod 600 .env`**. En hosting compartido otros
      usuarios del mismo servidor pueden leer archivos con permisos abiertos.
- [ ] Dejar `SEED_ADMIN_PASSWORD` vacío.

## 3. Base de datos y primer arranque

```bash
php artisan migrate --force
php artisan db:seed --force            # anota la contrasena que imprime
php artisan shield:generate --all --option=policies_and_permissions --panel=admin
php artisan db:seed --class=RolePermissionSeeder --force
php artisan shield:super-admin --user=1
```

- [ ] **Apunta la contraseña que imprime el seeder**: se muestra una sola vez.
- [ ] Entra al panel y cámbiala desde el perfil.
- [ ] Comprueba que `admin@aag.gob.ec` con la contraseña `password` **no**
      funciona.

## 4. Symlink de storage

```bash
php artisan storage:link
```

- [ ] Verifica que `public_html/storage` existe y apunta a
      `laravel_app/storage/app/public`.
- [ ] Si el hosting no permite symlinks, configúralo como carpeta compartida;
      **no** copies los archivos, o las subidas nuevas no se verán.

## 5. ⚠️ Bloqueo de ejecución en la carpeta de subidas — CRÍTICO

Es la protección que más fácil se pierde en un despliegue, y la más grave.

El repositorio incluye `storage/app/public/.htaccess`, que impide ejecutar PHP
entre los archivos subidos por usuarios. **Comprueba que llegó al servidor**
(algunos clientes FTP omiten los archivos que empiezan por punto):

```bash
ls -la /home/USUARIO/laravel_app/storage/app/public/.htaccess
```

- [ ] El archivo existe.
- [ ] `AllowOverride All` está activo para esa ruta (en cPanel suele estarlo).
- [ ] **Compruébalo de verdad**, no lo des por hecho:

```bash
echo '<?php echo "PRUEBA"; ?>' > /home/USUARIO/laravel_app/storage/app/public/prueba.php
curl -s https://TU-DOMINIO/storage/prueba.php
rm /home/USUARIO/laravel_app/storage/app/public/prueba.php
```

Debe devolver **403** o el código sin interpretar. Si responde `PRUEBA`, el
`.htaccess` no se está aplicando: **no publiques el sitio** hasta resolverlo
(en ese caso, añade la restricción en la configuración del VirtualHost o pide
soporte al hosting).

La validación del código (`App\Services\MediaService`) ya rechaza los archivos
peligrosos, así que esto es la segunda barrera; pero es la que cubre cualquier
vía de subida que se añada en el futuro.

## 6. HTTPS

- [ ] Certificado activo (AutoSSL de cPanel o Cloudflare).
- [ ] Redirección de HTTP a HTTPS.
- [ ] Con HTTPS ya activo, `SESSION_SECURE_COOKIE=true` en el `.env`.
      Sin HTTPS esta opción deja el sitio inaccesible: actívala **después**.

## 7. Optimización

```bash
composer install --no-dev --optimize-autoloader
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm ci && npm run build     # o sube public/build ya compilado
```

- [ ] Copiar `laravel_app/public/build` a `public_html/build`.
- [ ] Copiar el resto de estáticos (`fonts`, `css`, `js`, `favicon.ico`,
      `robots.txt`) a `public_html/`.

> Recuerda: `public_html` es una **copia** de `public/`. Cada vez que añadas un
> asset estático (una fuente, una imagen) hay que copiarlo también allí.

## 8. Permisos

```bash
chmod 600 .env
chmod -R 755 storage bootstrap/cache
```

- [ ] `storage` y `bootstrap/cache` escribibles por el usuario de PHP.
- [ ] Ningún archivo o carpeta con permisos `777`.

## 9. Comprobaciones finales

- [ ] `https://TU-DOMINIO/.env` → 404 o 403 (nunca el contenido)
- [ ] `https://TU-DOMINIO/storage/logs/laravel.log` → 403
- [ ] Provocar un error y confirmar que sale la página 500 del portal, **no**
      un stack trace de Laravel (si sale el trace, `APP_DEBUG` sigue en `true`)
- [ ] `https://TU-DOMINIO/pagina-inexistente` → la página 404 del portal
- [ ] Cabeceras de seguridad presentes:
      `curl -I https://TU-DOMINIO/ | grep -i "x-frame\|content-security\|x-content-type"`
- [ ] Entrar al panel con un usuario `editor` y comprobar que **no** ve
      Suscriptores ni Envíos de formulario

## 10. Documentos de transparencia (LOTAIP)

Los documentos pueden vivir en **dos sitios**, y cada documento elige el suyo:

| Origen | Dónde está el archivo | Cómo se sube |
|---|---|---|
| **Subdominio** (por defecto) | `https://document.aag.org.ec/` | por FTP, fuera del portal |
| **Este portal** | `storage/app/public/lotaip/` | desde el panel |

- [ ] Configura la dirección del subdominio en **Ajustes del sitio › Documentos**.
- [ ] Comprueba que un documento de cada tipo abre correctamente.

**Sobre los enlaces ya publicados:** un documento cuyo `file_path` guarda una
dirección completa (`https://…`) **conserva ese enlace tal cual**, aunque se
cambie el subdominio configurado. Es deliberado: la documentación difundida
anteriormente sigue funcionando pase lo que pase con esta configuración.

Si dejas la dirección del subdominio vacía, los documentos externos con ruta
relativa **dejan de listarse** (en vez de mostrar un enlace roto). Los que
tienen URL completa se siguen viendo.

### Sincronizar la estructura con el subdominio

```bash
php artisan lotaip:sincronizar --dry-run   # ver qué haría
php artisan lotaip:sincronizar             # aplicarlo
```

Recorre el subdominio, detecta qué años y meses tienen archivos, y enlaza cada
mes con su carpeta del explorador. Es idempotente: se puede repetir sin
duplicar nada.

**No copia archivos ni registra documento a documento**, y es deliberado: hay
más de mil archivos y la estructura no es uniforme (2023 es plana; 2024 y 2025
anidan cuatro niveles: `AÑO/Mes/Artículo 19/N. Literal/archivo.csv`).
Mantener mil registros sincronizados a mano sería frágil, y aplanarlos
perdería una jerarquía que sí tiene sentido para el ciudadano.

La consecuencia práctica es la buena: **cuando subas archivos nuevos por FTP
aparecen solos**, sin volver a ejecutar nada. Sólo hace falta repetir el
comando cuando se cree un **mes o año nuevo**.

Si en algún momento prefieres que ciertos documentos se listen dentro del
portal (con nombre, formato y peso en la línea gráfica del sitio), puedes
registrarlos uno a uno en **Transparencia › Documentos**: los dos modos
conviven, y se elige por mes.

## 11. Mantenimiento

- [ ] Copias de seguridad de la base de datos y de `storage/app/public`.
- [ ] `composer audit` de vez en cuando para revisar dependencias.
- [ ] Rotación de `storage/logs`, que crece sin límite.

---

## Pendientes de la revisión de seguridad: todos cerrados

Los seis puntos que abrió la primera revisión **ya están resueltos**. Se listan
con dónde quedó cada uno, porque el historial ayuda a no reabrirlos por error:

| # | Punto | Estado |
|---|---|---|
| 1 | HTML del editor sin sanear | Cerrado — `App\Services\HtmlSanitizer` (lista blanca), aplicado al guardar en `News`, `Faq` y `Project` |
| 2 | JSON-LD sin `JSON_HEX_TAG` | Cerrado — helper único en `app/Helpers/helpers.php`; las 6 plantillas con JSON-LD lo usan |
| 3 | `embed_code` sin validar | Cerrado — `App\Services\EmbedUrl` extrae y valida la URL; el `<iframe>` lo construye la plantilla |
| 4 | Documentos LOTAIP accesibles al subirlos | **No es un fallo: es lo pedido.** En transparencia los documentos deben poder verse desde que se publican. Se deja así a propósito |
| 5 | Sin límite de peticiones | Cerrado — `throttle:60,1` en el front, `throttle:10,1` en las subidas de Livewire y 9 puntos con `RateLimiter` propio |
| 6 | Enumeración de suscriptores | Cerrado — el boletín responde siempre lo mismo, exista o no la dirección |

Lo que **sí** sigue abierto está en `SEGURIDAD.md`, secciones 10 y 12. Lo
resumido: Laravel 11 ya no recibe parches de seguridad y quedan tres avisos
mitigados en la aplicación pero sin parche oficial (el parche existe sólo en
Laravel 12.60+). No bloquea publicar; sí conviene planificar la subida de
versión como trabajo aparte.
