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

## 11. Mantenimiento

- [ ] Copias de seguridad de la base de datos y de `storage/app/public`.
- [ ] `composer audit` de vez en cuando para revisar dependencias.
- [ ] Rotación de `storage/logs`, que crece sin límite.

---

## Pendientes conocidos

Estos puntos se identificaron en la revisión de seguridad y **siguen abiertos**.
No bloquean la publicación, pero conviene resolverlos:

1. **HTML del editor enriquecido sin sanear** (noticias, FAQ, proyectos): un
   usuario del panel puede inyectar HTML arbitrario, que se ejecuta en el
   navegador de los visitantes. Solución: sanear con HTMLPurifier al guardar.
2. **JSON-LD sin `JSON_HEX_TAG`** (11 puntos): un título con `</script>`
   permite salir del bloque de datos estructurados.
3. **`embed_code` de mapas** se inserta sin validar: aceptar sólo la URL y
   construir el `<iframe>` en la plantilla.
4. **Documentos LOTAIP accesibles antes de publicarse**: los flags ocultan el
   enlace, no el archivo. Solución: disco privado + controlador que valide.
5. **Sin límite de peticiones** en el front público ni en `/livewire/update`.
6. **Enumeración de suscriptores**: la respuesta del boletín revela si una
   dirección ya está registrada.
