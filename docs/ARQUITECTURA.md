# Arquitectura del sistema

Documentación técnica del portal de la Autoridad Aeroportuaria de Guayaquil.
Dirigida a quien vaya a mantener o ampliar el código.

---

## 1. Pila tecnológica

| Componente | Versión | Notas |
|---|---|---|
| PHP | 8.3 | Igual en desarrollo y en producción (cPanel) |
| Laravel | 11.x | |
| MySQL | 8.0 | |
| Filament | 3.2 | Panel de administración |
| Livewire | 3.x | Editor visual, galería de medios, formularios |
| Alpine.js | 3.x | Interactividad del front (servido por Vite) |
| Tailwind CSS | 3.4 | |
| Vite | 5.x | Compilación de assets |
| Node | 20.x | Solo para compilar; no hace falta en producción |

**Paquetes principales**

| Paquete | Para qué |
|---|---|
| `filament/filament` | Panel de administración |
| `bezhansalleh/filament-shield` | Permisos por recurso |
| `spatie/laravel-permission` | Roles y permisos (base de Shield) |
| `spatie/laravel-activitylog` | Registro de auditoría |
| `intervention/image-laravel` | Procesado y compresión de imágenes |
| `symfony/html-sanitizer` | Saneado del HTML del editor |

---

## 2. Estructura de carpetas

El código vive **fuera** del webroot, como en cPanel:

```
/home/USUARIO/
├── public_html/          ← lo único accesible desde internet
│   ├── index.php         ← apunta a ../laravel_app
│   ├── build/            ← assets compilados por Vite
│   ├── fonts/ css/ js/ images/
│   └── storage → laravel_app/storage/app/public   (symlink)
└── laravel_app/          ← la aplicación completa
```

> `public_html` es una **copia** de `laravel_app/public`. Cada asset estático
> nuevo (una fuente, una imagen) hay que copiarlo también allí. Es la fuente
> de error más habitual al desplegar.

---

## 3. Modelo de contenido

### 3.1 Páginas por bloques

Las páginas no tienen un cuerpo fijo: se componen de **bloques** que se
añaden, quitan y reordenan.

```
Page  (key, title, slug, status, meta_*)
 └── PageBlock  (type, settings JSON, sort_order, is_active)
```

- Una fila por bloque, no un JSON monolítico.
- `type` es la clave del bloque (`hero`, `stats`…) y decide qué vista lo pinta.
- `settings` guarda todo su contenido en JSON.

**Renderizado** — `resources/views/page.blade.php` recorre `activeBlocks` y
para cada uno resuelve su vista con `BlockRegistry::viewFor()`.

### 3.2 Los 15 bloques

`hero`, `banner_slider`, `quick_links`, `news_highlights`, `flights`,
`convocatoria`, `values`, `video`, `text_image`, `cta`, `stats`,
`faq_accordion`, `transparency_browser`, `form`, `map`.

### 3.3 Cómo añadir un bloque nuevo

1. Crear `app/Blocks/Types/MiBloque.php` extendiendo `BlockType`, con
   `key()`, `label()`, `icon()`, `view()`, `filamentBlock()` y `defaults()`.
2. Registrarlo en el array de `app/Blocks/BlockRegistry::types()`.
3. Crear la vista `resources/views/blocks/mi-bloque.blade.php`. Recibe una
   única variable, `$block`, y lee sus datos con `$block->get('clave')`.
4. Crear el campo del editor visual en
   `resources/views/editor/fields/mi-bloque.blade.php`.
5. **Registrarlo también** en el mapa `$editorViewMap` de
   `resources/views/livewire/visual-editor.blade.php` — está duplicado a mano
   y es fácil olvidarlo.

⚠️ **Las claves de `settings` son un contrato entre cuatro sitios**:
`defaults()`, `filamentBlock()`, el campo del editor (`wire:model`) y la vista
pública. Cambiar una obliga a cambiar las cuatro **y** migrar el JSON ya
guardado en la base de datos. Lo mismo con los valores enum
(`'navy'`, `'editorial'`, `'poster'`…).

### 3.4 Otros modelos

| Modelo | Qué guarda |
|---|---|
| `News` + `NewsCategory` | Noticias. Cuerpo en HTML y/o bloques de contenido |
| `Project` | Proyectos y obras |
| `Convocatoria` | Procesos y avisos, con cronograma y documentos |
| `Faq` + `FaqCategory` | Preguntas frecuentes |
| `LotaipYear` → `LotaipMonth` → `LotaipDocument` | Transparencia (ver su manual) |
| `Form` + `FormField` + `FormSubmission` | Constructor de formularios |
| `Menu` + `MenuItem` | Menús por ubicación |
| `SiteSetting` | Configuración global (clave/valor, cacheada) |
| `Media` | Galería de archivos |
| `Subscriber` | Boletín |
| `User` | Cuentas del panel |

---

## 4. Dos editores para las páginas

Conviven a propósito:

**Editor visual** (`/admin/visual-editor/{page}`) — el principal. Un overlay
sobre la página real: se ve el resultado mientras se edita. Livewire, en
`app/Livewire/VisualEditor.php`.

**Builder de Filament** (dentro de `PageResource`) — formulario clásico. Útil
para cambios en lote.

> ⚠️ El Builder de Filament **borra y recrea** todas las filas de bloques al
> guardar (`EditPage::syncBlocks()`), así que los IDs cambian. No es un
> problema en sí, pero conviene saberlo al depurar.

---

## 5. Sistema de diseño

Todo el aspecto visual sale de **tokens**, no de valores sueltos:

- Los colores y las tipografías se guardan en `site_settings` y se inyectan
  como variables CSS en `layouts/app.blade.php`.
- `tailwind.config.js` mapea esas variables a utilidades (`bg-brand-navy`,
  `rounded-card`…).
- Las clases de componente viven en `@layer components` de
  `resources/css/app.css`: `.section-wrap`, `.btn-primary`, `.card-surface`…

**Redefinir esas clases reestiliza el sitio entero sin tocar Blade.** Es el
punto de entrada para cualquier cambio de aspecto.

La especificación completa está en **`LINEA_GRAFICA.md`**. Léela antes de
crear un componente nuevo.

### Trampa conocida

Las clases de Tailwind construidas en tiempo de ejecución **no se compilan**:

```blade
{{-- MAL: nunca llega al CSS --}}
<div class="grid-cols-{{ $n }}">

{{-- BIEN --}}
@php $cols = match($n) { 2 => 'grid-cols-2', default => 'grid-cols-4' }; @endphp
<div class="{{ $cols }}">
```

Tailwind escanea el código fuente en el build; una clase que solo existe en
runtime no aparece por ningún lado. Este fallo ya apareció varias veces en
este proyecto.

---

## 6. Comandos propios

| Comando | Cuándo corre | Qué hace |
|---|---|---|
| `lotaip:sincronizar` | Diario 03:30 | Trae del subdominio los documentos de transparencia. Ver su manual |
| `convocatorias:close-expired` | Cada hora | Cierra convocatorias vencidas |

Ambos dependen del cron de Laravel:

```
* * * * * cd /home/USUARIO/laravel_app && php artisan schedule:run >> /dev/null 2>&1
```

---

## 7. Servicios

| Clase | Responsabilidad |
|---|---|
| `MediaService` | Subida de archivos: valida el tipo real, comprime a WebP, decide la extensión |
| `HtmlSanitizer` | Limpia el HTML del editor enriquecido antes de guardarlo |
| `EmbedUrl` | Valida las URL de mapas y vídeos incrustados |

---

## 8. Caché

Se cachean las consultas repetidas, y se invalidan en los `saved`/`deleted`
de sus modelos:

| Clave | Contenido |
|---|---|
| `site_settings` | Toda la configuración (`rememberForever`) |
| `page_{key}` | Una página con sus bloques |
| `transparency_tree_{seccion}` | Árbol año → mes → documentos (5 min) |
| `news_home_highlights_*` | Noticias destacadas (5 min) |
| `sitemap_xml` | Sitemap (1 h) |

> `Page::clearCache()` sin argumento hace `Cache::flush()`, que borra también
> los contadores del limitador de peticiones. Pásale siempre la clave.

---

## 9. Entorno de desarrollo

Se ejecuta en **Ubuntu 24.04 sobre WSL** con Apache + PHP 8.3 + MySQL 8,
reproduciendo el esquema de cPanel (webroot separado). No usa Docker ni
`php artisan serve`.

Detalles y comandos: `LEVANTAR_PROYECTO.md`, en la carpeta del proyecto.

> Recuerda: WSL apaga su máquina virtual cuando no queda ningún proceso
> dentro. Hay que dejar una terminal Ubuntu abierta mientras se trabaja, o la
> web deja de responder.

---

## 10. Despliegue

Ver **`DESPLIEGUE_CPANEL.md`**, con la lista de comprobación completa.

Resumen del proceso:

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
php artisan storage:link
# copiar public/build y los estáticos a public_html/
```
