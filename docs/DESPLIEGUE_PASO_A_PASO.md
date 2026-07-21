# Publicación paso a paso: primero en pruebas, después en el dominio real

> **Datos reales de la cuenta** (comprobados en el cPanel el 20/07/2026):
>
> | | |
> |---|---|
> | Usuario de cPanel | `aagorgec` |
> | Carpeta personal | `/home/aagorgec` |
> | Dominio principal | `aag.org.ec` → `/public_html` (WordPress) |
> | IP compartida | `142.132.139.147` |
> | **PHP** | **8.2 — ya correcta, no hay que tocarla** |
> | Extensiones | Las 24 que necesita el portal están activas |
> | Terminal | **Disponible** (Avanzada › Terminal) |
> | Base de datos existente | `aagorgec_site` (170 MB, del WordPress) — **no tocar** |
> | Subdominios ya creados | `document`, `dataowncloud`, `monitor`, `principal`, `ticketera` |
>
> El selector de PHP permite además configuración **por dominio**
> (*Seleccionar Versión PHP › Per Domain Settings*), por si en el futuro hiciera
> falta una versión distinta para el WordPress y para el portal.

Guía operativa para el caso concreto de la AAG: hay un **WordPress publicado y
en uso**, y el portal nuevo debe verse primero en un subdominio para revisarlo,
y pasar al dominio principal sólo cuando esté conforme.

`DESPLIEGUE_CPANEL.md` es la lista de comprobación técnica. Este documento es el
orden en que se hacen las cosas y por qué.

> **Regla que no se rompe en ningún paso:** no se toca `public_html` (el
> WordPress actual) hasta la Fase 3, y no se toca `document.aag.org.ec` nunca.
> Los 1137 documentos de transparencia se quedan donde están, con sus enlaces
> intactos. El portal sólo los lee.

---

## La idea que hace fácil el cambio final

El truco está en **dónde se instala el código**, y se decide ahora, no al final.

El subdominio de pruebas no apunta a una copia del sitio. Apunta a la carpeta
`public/` del proyecto:

```
/home/USUARIO/
├── public_html/              ← WordPress actual. NO SE TOCA hasta la fase 3.
│
├── aag_portal/               ← el portal nuevo, FUERA del webroot
│   ├── app/  config/  vendor/  storage/  .env   ← nada de esto es accesible
│   └── public/               ← lo ÚNICO que se publica
│       ├── index.php
│       ├── build/
│       └── storage → ../storage/app/public
│
└── (el subdominio de pruebas apunta a /home/USUARIO/aag_portal/public)
```

Con esto, **pasar al dominio real es cambiar a qué carpeta apunta el dominio**.
Nada más. No hay que mover archivos, ni copiar `public/` a `public_html`, ni
recompilar, ni volver a sincronizar los assets cada vez que se añade una fuente
o una imagen.

Ya está comprobado que el código no tiene ningún dominio escrito a fuego: las
URLs canónicas se calculan solas con la dirección por la que entra el visitante.
Por eso el cambio se reduce a `APP_URL` y limpiar la caché.

> **Si el cPanel no deja elegir la carpeta del subdominio** (algunos lo fijan a
> `public_html/subdominio`), hay alternativa: se deja en su carpeta por defecto
> y dentro se pone sólo el `index.php` y el `.htaccess`, apuntando a
> `../aag_portal`. Funciona igual de bien y el resto de la guía no cambia.
> Dímelo si es el caso y te paso esos dos archivos ya ajustados.

---

## Fase 0 — Antes de tocar el cPanel

### 0.1 Copia de seguridad del sitio actual

Aunque no vayamos a tocar el WordPress, se hace igual. Es la red de seguridad
de todo lo demás.

En cPanel → **Asistente de copia de seguridad** → *Copia de seguridad completa*
→ destino "Directorio principal". Cuando termine, **descárgala a tu equipo**.
Una copia que sólo vive en el mismo servidor no protege de casi nada.

### 0.2 Comprobar la versión de PHP

cPanel → **Selector de PHP** (o *MultiPHP Manager*).

- Necesitamos **PHP 8.2 o 8.3**. Con 8.1 o menos el portal no arranca.
- Ojo: en 8.5 tampoco, Laravel 11 aún no lo soporta.
- Extensiones que deben estar marcadas: `bcmath`, `ctype`, `curl`, `dom`,
  `fileinfo`, `gd`, `intl`, `mbstring`, `openssl`, `pdo_mysql`, `tokenizer`,
  `xml`, `zip`.

Si el WordPress está en una versión distinta, se puede fijar una versión
**por dominio** desde MultiPHP Manager: cada uno con la suya, sin conflicto.

### 0.3 Base de datos nueva

cPanel → **Bases de datos MySQL**. Se crea una **nueva**, nunca la del
WordPress.

1. Crear base de datos: `aagportal` → quedará como `usuario_aagportal`.
2. Crear usuario: `aagportal` → quedará como `usuario_aagportal`.
3. **Contraseña**: usa el generador de cPanel y guárdala en tu gestor de
   contraseñas. No la mandes por chat ni por correo.
4. Añadir el usuario a la base de datos con **TODOS los privilegios**.

Apunta el nombre exacto de la base, del usuario y el host (casi siempre
`localhost`): van al `.env` en el paso 1.4.

---

## Fase 1 — Publicar en el subdominio de pruebas

### 1.1 Crear el subdominio

cPanel → **Dominios** → *Crear un dominio*.

- Dominio: `pruebas.aag.org.ec` (o el nombre que prefieras).
- **Quita la marca** de "Compartir raíz del documento".
- Raíz del documento: `/home/USUARIO/aag_portal/public`

Si el campo no te deja escribir esa ruta, es el caso de la nota de arriba:
avísame y seguimos por la vía alternativa.

### 1.2 Subir el código

**Lo que se sube:** todo el proyecto **menos** `node_modules/`, `.git/`,
`storage/logs/*`, `.env` y `vendor/` (esta última sólo si hay Terminal; ver
abajo).

**Con Terminal disponible** (cPanel → *Terminal*):

```bash
cd ~/aag_portal
composer install --no-dev --optimize-autoloader
```

**Sin Terminal:** hay que subir también `vendor/` por FTP. Es una carpeta con
muchos archivos: comprímela en `.zip`, súbela y descomprímela con el
Administrador de archivos. Descomprimir en el servidor tarda segundos;
subir 20 000 archivos sueltos por FTP puede tardar horas y suele fallar a medias.

> ⚠️ **Comprueba que subió `storage/app/public/.htaccess`.** Muchos clientes FTP
> se saltan los archivos que empiezan por punto. Ese archivo impide que se
> ejecute código PHP entre los archivos subidos por usuarios: si falta, la
> protección desaparece sin avisar y la web sigue funcionando igual. En el
> Administrador de archivos hay que activar *Mostrar archivos ocultos*.

### 1.3 Assets compilados

La carpeta `public/build/` ya va compilada en el proyecto. **No hace falta
instalar Node en el servidor.** Sólo verifica que `public/build/manifest.json`
llegó.

### 1.4 El archivo `.env`

Copia `.env.example` a `.env` y rellénalo. Los valores propios de esta fase:

```ini
APP_ENV=production
APP_DEBUG=false                        # nunca true en el servidor
APP_URL=https://pruebas.aag.org.ec     # el subdominio, de momento

DB_DATABASE=usuario_aagportal
DB_USERNAME=usuario_aagportal
DB_PASSWORD=          # la del paso 0.3, escrita aquí directamente

TRUSTED_PROXIES=      # vacío por ahora; se rellena al activar Cloudflare

MAIL_MAILER=smtp
MAIL_HOST=mail.aag.org.ec
MAIL_PORT=465
MAIL_SCHEME=smtps
MAIL_USERNAME=no-responder@aag.org.ec
MAIL_PASSWORD=        # la de la cuenta de correo del cPanel
MAIL_FROM_ADDRESS="no-responder@aag.org.ec"
```

Después, **permisos del `.env`**: en el Administrador de archivos, clic derecho
→ *Cambiar permisos* → **600**. En hosting compartido otros usuarios del mismo
servidor pueden leer los archivos con permisos abiertos, y ahí están las
credenciales de la base de datos.

### 1.5 Poner en marcha la aplicación

Con Terminal:

```bash
cd ~/aag_portal
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force            # ANOTA la contraseña que imprime
php artisan shield:generate --all --option=policies_and_permissions --panel=admin
php artisan db:seed --class=RolePermissionSeeder --force
php artisan shield:super-admin --user=1
php artisan storage:link
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

> **La contraseña del administrador se imprime una sola vez.** Apúntala en ese
> momento. Entra al panel y cámbiala desde tu perfil.

Sin Terminal, estos comandos necesitan otra vía; avísame y preparo un script
temporal protegido por clave que se borra al terminar.

### 1.6 Impedir que Google indexe las pruebas

**Esto importa más de lo que parece.** Si Google indexa el subdominio de
pruebas, acabas con el sitio duplicado en los resultados de búsqueda,
compitiendo contra el dominio real. Cuesta meses de limpiar.

En cPanel, crea `/home/USUARIO/aag_portal/public/robots.txt`:

```
User-agent: *
Disallow: /
```

**Y acuérdate de quitarlo en la Fase 3.** Está anotado en la lista de esa fase.

### 1.7 Certificado HTTPS del subdominio

cPanel → **SSL/TLS Status** → marca el subdominio → *Run AutoSSL*. Tarda unos
minutos. Cuando `https://pruebas.aag.org.ec` cargue con el candado, sigue.

Con HTTPS ya funcionando, en el `.env`:

```ini
SESSION_SECURE_COOKIE=true
```

Antes no: sin HTTPS esa opción deja el sitio inaccesible.

### 1.8 Tareas programadas (cron)

cPanel → **Trabajos cron**. Añade uno solo:

- Frecuencia: **cada minuto** (`* * * * *`)
- Comando:
  ```
  /usr/local/bin/php /home/USUARIO/aag_portal/artisan schedule:run >/dev/null 2>&1
  ```

Con ese basta: Laravel se encarga de lanzar a su hora el cierre de
convocatorias vencidas (cada hora) y la sincronización de LOTAIP (de
madrugada). La ruta de PHP puede variar; en el Selector de PHP aparece la
correcta.

### 1.9 Comprobaciones antes de enseñarlo

Marca una por una. Si alguna falla, no sigas:

- [ ] `https://pruebas.aag.org.ec/` carga con el diseño correcto
- [ ] `https://pruebas.aag.org.ec/.env` → **404 o 403**, nunca el contenido
- [ ] `https://pruebas.aag.org.ec/pagina-que-no-existe` → la página 404 del portal
- [ ] Provocar un error y ver la página 500 del portal, **no** un listado de
      código con rutas y credenciales (si sale eso, `APP_DEBUG` sigue en `true`)
- [ ] Entrar al panel en `/admin` y cambiar la contraseña
- [ ] Subir una imagen desde el panel y verla en el sitio
- [ ] Transparencia: abrir un documento y comprobar que va a
      `document.aag.org.ec` y **abre bien**
- [ ] Enviar el formulario de contacto y comprobar que **llega el correo**
- [ ] Verificar que la protección de subidas está activa:

```bash
echo '<?php echo "PRUEBA"; ?>' > ~/aag_portal/storage/app/public/prueba.php
curl -s https://pruebas.aag.org.ec/storage/prueba.php
rm ~/aag_portal/storage/app/public/prueba.php
```

Debe devolver **403** o el texto sin ejecutar. Si responde `PRUEBA`, el
`.htaccess` no se está aplicando: **no publiques** hasta resolverlo.

---

## Fase 2 — Revisión

Aquí es donde aparecen las cosas que en local no se ven. Recorre el sitio en
móvil y en ordenador, con la gente que vaya a usar el panel.

Cosas que conviene mirar con calma:

- Los bloques de cada página, añadiendo y quitando alguno
- Convocatorias: el pop-up, los procesos vigentes y el archivo histórico
- Transparencia: varios años y meses, incluidos los que no tienen documentos
- Los formularios, y que los avisos lleguen a quien corresponde
- El buscador
- Cada rol de usuario: que el de contenido no vea lo que no debe

Anota lo que salga y lo corregimos antes de la Fase 3. **No hay prisa en pasar
al dominio real**: mientras el WordPress siga publicado, no hay nada roto de
cara al público.

---

## Fase 3 — Pasar al dominio principal

Sólo cuando la Fase 2 esté conforme.

### 3.1 Preparar las redirecciones del WordPress

**Este paso se prepara antes, no después.** Las direcciones del WordPress
actual están indexadas en Google y enlazadas desde otros sitios. Si al cambiar
dejan de existir, esas visitas acaban en un 404 y se pierde el posicionamiento
ganado. En una entidad pública además se rompen enlaces que la ciudadanía tiene
guardados.

El portal ya trae el sistema montado: **Configuración › Redirecciones** en el
panel. Se añade la dirección antigua y a dónde debe llevar, y queda resuelto al
momento, sin desplegar nada.

**Cómo sacar la lista de direcciones antiguas:**

1. Si tenéis Google Search Console del sitio actual: *Rendimiento › Páginas*,
   ordenar por clics y exportar. Es la mejor fuente, porque da exactamente las
   que reciben visitas.
2. Si no, en Google: `site:aag.org.ec` muestra lo que tiene indexado.
3. Del propio WordPress: la lista de páginas y entradas publicadas.

Con las 20 o 30 más visitadas se cubre casi todo el tráfico real. El resto se
van añadiendo cuando aparezcan: la tabla del panel muestra **cuántas veces se
ha usado cada redirección**, así se ve cuáles importan y cuáles sobran.

**Comprobación después del cambio:** entra a una dirección antigua y confirma
que lleva a la nueva. En la columna "Visitas" del panel debe subir el contador.

> Por seguridad, solo se admiten destinos internos (`/nosotros`) o direcciones
> `https://`. No se puede redirigir a `//otro-sitio.com`: si una cuenta del
> panel se viera comprometida, esa capacidad convertiría el dominio
> institucional en un trampolín de phishing.

### 3.2 El cambio

1. Copia de seguridad completa otra vez (paso 0.1).
2. **Quita el `robots.txt`** que bloqueaba la indexación (paso 1.6). Si esto
   se olvida, el sitio nuevo no aparece en Google. Es el olvido más común.
3. En el `.env`, cambia `APP_URL` al dominio definitivo.
4. cPanel → **Dominios** → cambia la raíz del documento del dominio principal
   a `/home/USUARIO/aag_portal/public`.
5. Limpia las cachés:
   ```bash
   cd ~/aag_portal
   php artisan config:cache && php artisan route:cache && php artisan view:cache
   ```
6. Comprueba el sitio en el dominio real.

El WordPress sigue intacto en `public_html`. Si algo va mal, se vuelve atrás
cambiando la raíz del documento otra vez: **el paso es reversible en un minuto**,
y por eso conviene hacerlo así y no borrando nada.

Deja el WordPress unas semanas antes de retirarlo, por si aparece algún
contenido que no se había migrado.

---

## Cloudflare

El TDR lo exige como capa de CDN, WAF y protección DDoS. Se hace **después** de
que el sitio funcione en el dominio real: si se activa antes, cuesta distinguir
si un fallo es del portal o del proxy.

### Puesta en marcha

1. Crea la cuenta en [cloudflare.com](https://cloudflare.com) y añade el dominio
   `aag.org.ec` (plan **Free**, que es el que pide el TDR).
2. Cloudflare lee los DNS actuales. **Revisa que estén todos**, y en especial:
   - el registro de `document` (el subdominio de documentos)
   - los registros de correo (`MX`) — si falta alguno, **el correo deja de llegar**
3. Cambia los servidores de nombres en el panel donde esté registrado el dominio
   (probablemente Ecuahosting). Tarda entre unos minutos y 24 horas.

### Configuración una vez activo

**SSL/TLS** → modo de cifrado: **Full (strict)**.

> No uses "Flexible". Con Flexible, Cloudflare habla con el servidor por HTTP
> mientras le dice al navegador que la conexión es segura: además de no serlo,
> provoca un bucle de redirecciones que deja el sitio inaccesible. Es el error
> más habitual al empezar con Cloudflare.

- **SSL/TLS → Edge Certificates**: activa *Always Use HTTPS* y *Automatic HTTPS
  Rewrites*.
- **Speed → Optimization**: activa Brotli. **Deja Rocket Loader desactivado**:
  reordena la carga de JavaScript y rompe Alpine.js, que es lo que mueve los
  menús y los bloques interactivos del portal.
- **Caching → Configuration**: nivel estándar. Crea una regla para **no
  cachear el panel**: si `URI Path` empieza por `/admin` o `/livewire` →
  *Bypass cache*. Sin esto, el panel se comporta de forma errática.
- **Security → WAF**: activa los *Managed Rules*. Si algún formulario empieza a
  dar error 403, revisa ahí qué regla lo bloqueó antes de desactivar nada en
  bloque.

### ⚠️ El paso que se olvida siempre

Con Cloudflare activo, en el `.env`:

```ini
TRUSTED_PROXIES=cloudflare
```

Y después `php artisan config:cache`.

Sin esto, el portal ve la IP de Cloudflare en lugar de la del visitante, y
**todos los visitantes cuentan como uno solo**: el límite del boletín (5 intentos
por IP) se vuelve global, y cinco intentos de cualquiera dejan el formulario
bloqueado para todo el mundo. Además las IPs que se guardan de los suscriptores
dejan de tener valor como registro de consentimiento.

Comprobación: suscríbete al boletín y mira en el panel, en Suscriptores, que la
IP registrada **no** empiece por `172.` o `104.` (rangos de Cloudflare).

---

## Google Analytics

El TDR pide "estadísticas básicas de visitas (Google Analytics)".

1. Crea la propiedad en [analytics.google.com](https://analytics.google.com) →
   Administrar → Crear propiedad → plataforma **Web**.
2. Copia el identificador de medición (tiene la forma `G-XXXXXXXXXX`).
3. Pégalo en el panel del portal, en **Ajustes del sitio**.

> **Antes de activarlo**, ten en cuenta la protección de datos: Analytics deja
> cookies y trata datos de los visitantes. En un portal público institucional
> eso normalmente obliga a tener un aviso de cookies y una política de
> privacidad accesible. Conviene consultarlo con quien lleve el tema legal en la
> AAG antes de encenderlo, no después.

---

## Resumen de lo que hay que hacer fuera del cPanel

| Dónde | Qué | Cuándo |
|---|---|---|
| Cloudflare | Cuenta, dominio, DNS, SSL Full (strict), WAF, reglas de caché | Después de la Fase 3 |
| Registrador del dominio | Cambiar los servidores de nombres a Cloudflare | Al activar Cloudflare |
| Google Analytics | Crear propiedad y copiar el identificador | Cuando se decida activarlo |
| Google Search Console | Verificar el dominio y enviar el sitemap | Después de la Fase 3 |
| Gestor de contraseñas | Guardar las credenciales de base de datos, correo y panel | Según se vayan creando |
