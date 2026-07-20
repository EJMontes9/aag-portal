# Seguridad del sistema

Medidas implementadas y por qué existen. **Léelo antes de tocar cualquiera de
ellas**: casi todas se pusieron para cerrar un fallo concreto y encontrado, no
por precaución teórica.

---

## 1. Subida de archivos

**Regla:** la extensión de un archivo subido **nunca** sale del nombre que
envía el cliente. Se deriva de una lista blanca indexada por el tipo MIME real
del contenido (`App\Services\MediaService::TIPOS_PERMITIDOS`).

Por qué importa: si se toma la extensión del cliente, basta subir un `.php`
declarándolo como imagen para dejar código ejecutable bajo `/storage`, que es
público. Eso permite ejecutar comandos en el servidor.

**Al tocar `MediaService`:**

- No añadas SVG a la lista. Admite `<script>` y se sirve desde nuestro propio
  dominio, así que ejecuta JavaScript con la sesión de quien lo abra.
- Tampoco HTML, XML ni nada interpretable por el navegador.
- `EXTENSIONES_PROHIBIDAS` es una segunda red por si alguien amplía la lista
  sin pensar. No la vacíes.
- `processFromStoredPath()` **borra** el archivo si el tipo no está permitido:
  ahí Filament ya lo ha escrito en disco, y rechazarlo sin borrarlo lo dejaría
  accesible por URL.

**Validación en los formularios.** Todo `FileUpload` necesita
`acceptedFileTypes()` explícito, y todo `validate()` de subida necesita la
regla `mimes`. No es decorativa: Laravel solo aplica su bloqueo interno de
archivos PHP cuando hay una regla `mimes`/`mimetypes` presente.

**Defensa en profundidad.** `storage/app/public/.htaccess` impide ejecutar PHP
entre los archivos subidos. Está **versionado a propósito** (el `.gitignore`
de esa carpeta lo excluye de la regla `*`). Si se pierde en un despliegue, la
protección desaparece en silencio: hay que comprobarlo en el servidor (punto 5
de `DESPLIEGUE_CPANEL.md`).

---

## 2. Autorización

**El panel deniega por defecto.** `Resource::checkPolicyExistence(false)` en
`AdminPanelProvider::boot()`.

Filament, sin esa línea, **permite** cuando un recurso no tiene policy. Con 16
recursos y una sola policy, eso dejaba todo abierto a cualquiera que entrara
al panel, incluidos los datos personales de suscriptores y los envíos de
formularios.

Consecuencia práctica: **todo recurso nuevo necesita su policy**. Si falta, no
se verá — y es preferible eso a que se vea de más. Se generan con:

```bash
php artisan shield:generate --all --option=policies_and_permissions --panel=admin
php artisan db:seed --class=RolePermissionSeeder
```

**Excepciones del `Gate::before`** (`AppServiceProvider::configurarAutorizacion`):

- El `super_admin` tiene acceso total.
- **Salvo** borrar su propia cuenta: eso se comprueba *antes*, porque el
  `return true` del super_admin cortocircuita las policies y la de usuarios no
  llegaba a evaluarse.

**Escalada de privilegios.** Solo un `super_admin` puede otorgar el rol
`super_admin` (`UserResource`), y la gestión de usuarios y roles está limitada
a ese rol: quien crea cuentas y asigna roles puede concederse cualquier
permiso.

Ver `MANUAL_USUARIOS_ROLES.md`.

---

## 3. Contenido inyectado por usuarios

### HTML del editor enriquecido

El cuerpo de noticias, la respuesta de las FAQ y la descripción de proyectos
se pintan **sin escapar** (`{!! !!}`), porque si no se verían las etiquetas.

Se sanean **al guardar**, en el `saving()` de cada modelo, con
`App\Services\HtmlSanitizer` (lista blanca de etiquetas y atributos). Así el
HTML peligroso no llega ni a la base de datos.

> La barra de herramientas del editor limita lo que se hace con el ratón, **no
> lo que llega al servidor**. Cualquiera con acceso al panel puede interceptar
> la petición.

Si añades otro campo de HTML enriquecido, **añade también su saneado**.

### Datos estructurados (JSON-LD)

Usa siempre el helper `json_ld()`, nunca `json_encode()` directo.

El bloque va dentro de un `<script>`, y ahí el navegador busca la secuencia de
cierre en crudo: no hay escapado de HTML que valga. `json_ld()` aplica
`JSON_HEX_TAG`, que la hace imposible. **No añadas `JSON_UNESCAPED_SLASHES`**:
era justo lo que dejaba pasar la barra del cierre.

### Contenido incrustado (mapas y vídeos)

No se acepta HTML. `App\Services\EmbedUrl` extrae solo la dirección, la valida
contra una lista de proveedores y la plantilla construye el `<iframe>` con
`sandbox`.

La comparación de dominio es exacta o por subdominio real. Un `str_contains`
dejaría pasar `google.com.atacante.net`.

### Enlaces de documentos

`LotaipDocument::getUrlAttribute()` solo admite esquemas `http` y `https`. La
comprobación **no exige `//`**: `javascript:alert(1)` no lo lleva y, si solo se
buscara `://`, pasaría por ruta relativa.

---

## 4. Cabeceras HTTP

`App\Http\Middleware\SecurityHeaders`, aplicado a todas las respuestas web.
Va en código y no en configuración del servidor para que viaje con el
repositorio.

| Cabecera | Para qué |
|---|---|
| `Content-Security-Policy` | Limita de dónde se carga código y a dónde se pueden enviar datos |
| `X-Frame-Options: SAMEORIGIN` | Impide empotrar el panel en un iframe ajeno |
| `X-Content-Type-Options: nosniff` | Evita que el navegador reinterprete el tipo de un archivo |
| `Referrer-Policy` | No filtra la URL completa al salir del sitio |
| `Permissions-Policy` | Desactiva cámara, micrófono y geolocalización |
| `Strict-Transport-Security` | Solo bajo HTTPS |

### ⚠️ La CSP necesita `unsafe-inline` **y** `unsafe-eval`

No las quites "por endurecer". Alpine 3 compila cada expresión (`x-show`,
`x-data`, `:class`) con `new Function()`, también servido por Vite. Sin
`unsafe-eval` el navegador lo bloquea y **Alpine falla a medias**: retira los
`x-cloak` pero no evalúa los `x-show`, así que todo queda visible a la vez.

Pasó de verdad: el navegador de transparencia mostraba los cuatro años
apilados, y estaban rotos el menú móvil, el acordeón de FAQ, el slider y el
contador de convocatorias. **El síntoma es silencioso** — la página carga sin
errores aparentes y solo se ve en la consola del navegador:

```
Alpine Expression Error: ... 'unsafe-eval' is not an allowed source of script
```

Si algo interactivo deja de responder, mira ahí primero.

Para poder quitarlas habría que migrar a la build CSP de Alpine, que obliga a
reescribir todas las expresiones como métodos de un componente.

---

## 5. Límite de peticiones

| Ruta | Límite | Motivo |
|---|---|---|
| Páginas con búsqueda | 60/min | El `LIKE '%texto%'` recorre la tabla entera |
| `/subscribe` | 10 cada 10 min | Escribe en base de datos y envía correo |
| Login del panel | 5/min | Lo aplica Filament |

La búsqueda se acota a 80 caracteres.

---

## 6. Datos personales

Los suscriptores y los envíos de formularios guardan correo, IP y user-agent.
Solo son accesibles para `super_admin` y `admin`: los roles `editor`,
`publisher` y `transparencia` los tienen denegados.

**Sin enumeración:** el formulario de suscripción responde siempre lo mismo,
exista el correo o no. Antes el mensaje cambiaba según el estado, lo que
permitía averiguar qué direcciones están en la lista.

**Las bajas no se revierten solas:** quien se dio de baja no vuelve a la lista
porque alguien escriba su correo en el formulario.

---

## 7. Sesiones y credenciales

- No hay contraseñas por defecto. El seeder genera una aleatoria al crear una
  cuenta y la imprime **una sola vez**; nunca toca la de un usuario existente.
- El panel tiene página de perfil y recuperación por correo, así que la
  contraseña se puede cambiar desde la aplicación.
- En producción: `SESSION_SECURE_COOKIE=true`, `SESSION_ENCRYPT=true`,
  `SESSION_SAME_SITE=strict`, y 60 minutos de vida.
- `AuthenticateSession` está activo: al cambiar la contraseña se invalidan las
  demás sesiones.

---

## 8. Configuración de producción

`.env.example` **ya trae los valores de producción**, no los de desarrollo. Es
deliberado: esa plantilla es lo que se copia al desplegar, y un `APP_DEBUG=true`
olvidado expone en cada error la clave de la aplicación y las credenciales de
base de datos y de correo.

Comprobaciones obligatorias antes de publicar: puntos 2, 5, 6 y 9 de
`DESPLIEGUE_CPANEL.md`.

---

## 9. Registro de auditoría

`spatie/laravel-activitylog` registra quién cambió qué. Visible en el panel,
en **Registro de actividad** (solo administradores).

La contraseña SMTP se guarda cifrada y **se omite del registro**.

---

## 10. Qué NO está resuelto

Honestidad sobre los límites de lo hecho:

1. **La CSP no detiene un XSS en línea**, por `unsafe-inline`. Sí limita a
   dónde se pueden enviar los datos robados. Endurecerla exige nonces.
2. **Los documentos de transparencia son públicos desde que se suben.** Es
   deliberado, a petición del cliente: en transparencia es el comportamiento
   buscado. No hay estado de borrador para ellos.
3. **`/livewire/update` no tiene límite de peticiones propio.**
4. **El explorador del subdominio de documentos** tiene una comprobación de
   ruta que compara solo el prefijo del texto:
   ```php
   // Actual
   strpos(realpath($path), realpath($root)) === 0
   // Recomendado
   strpos(realpath($path), realpath($root) . DIRECTORY_SEPARATOR) === 0
   ```
   Una carpeta hermana con nombre parecido (`...ec.bak`) pasaría la
   validación. No es explotable hoy. Ese código **no es de este repositorio**.
5. **El responsive no se ha verificado en dispositivos reales**, solo sobre el
   marcado.

---

## 11. Mantenimiento

```bash
composer audit      # vulnerabilidades conocidas en dependencias
npm audit --omit=dev
```

Conviene revisarlo de vez en cuando y antes de cada despliegue grande.
