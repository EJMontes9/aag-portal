# Portal AAG

Portal institucional de la **Autoridad Aeroportuaria de Guayaquil**.

Laravel 11 · Filament 3 · PHP 8.3 · MySQL 8

---

## 📚 Documentación

Toda la documentación está en **[`docs/`](docs/README.md)**.

| Necesito… | Documento |
|---|---|
| Publicar contenido | [Manual de contenido](docs/MANUAL_CONTENIDO.md) |
| Subir documentos LOTAIP | [Manual de transparencia](docs/MANUAL_TRANSPARENCIA.md) |
| Dar acceso a alguien | [Usuarios y roles](docs/MANUAL_USUARIOS_ROLES.md) |
| Desplegar en el servidor | [Despliegue en cPanel](docs/DESPLIEGUE_CPANEL.md) |
| Entender el código | [Arquitectura](docs/ARQUITECTURA.md) |
| Tocar algo de seguridad | [Seguridad](docs/SEGURIDAD.md) ← **léelo antes** |
| Cambiar el diseño | [Línea gráfica](docs/LINEA_GRAFICA.md) |

---

## Puesta en marcha (desarrollo)

El entorno es **Ubuntu 24.04 sobre WSL** con Apache + PHP 8.3 + MySQL 8,
reproduciendo el esquema de cPanel. No usa Docker ni `php artisan serve`.

```bash
composer install
npm install && npm run build
cp .env.example .env      # ajustar a desarrollo (ver abajo)
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
```

> `.env.example` trae los valores de **producción** a propósito. Para
> desarrollo local hay que cambiar `APP_ENV=local`, `APP_DEBUG=true` y
> `SESSION_SECURE_COOKIE=false` (sin HTTPS, esa opción deja el sitio
> inaccesible).

Instrucciones detalladas del entorno: `LEVANTAR_PROYECTO.md`, en la carpeta
del proyecto.

---

## Comandos propios

```bash
php artisan lotaip:sincronizar [--dry-run]   # documentos de transparencia
php artisan convocatorias:close-expired      # cierra convocatorias vencidas
```

Ambos se ejecutan solos si el servidor tiene el cron de Laravel:

```
* * * * * cd /home/USUARIO/laravel_app && php artisan schedule:run >> /dev/null 2>&1
```

---

## Antes de publicar

1. `APP_DEBUG=false` y `APP_ENV=production`
2. Comprobar **en el servidor** que `/storage` no ejecuta PHP
3. Cron de Laravel configurado
4. Cambiar la contraseña inicial del administrador

Lista completa: [`docs/DESPLIEGUE_CPANEL.md`](docs/DESPLIEGUE_CPANEL.md)
