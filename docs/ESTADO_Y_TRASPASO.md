# Estado del despliegue y traspaso de sesión

> **Última actualización:** 21 de julio de 2026, 01:30
> **Para qué sirve este documento:** retomar el trabajo en otra máquina sin
> perder contexto. Contiene el estado real del servidor, lo que falta, y las
> decisiones tomadas con su porqué.

---

## 1. Resumen en diez líneas

Portal institucional de la **Autoridad Aeroportuaria de Guayaquil** en Laravel 11
+ Filament 3, que sustituirá al WordPress actual. El código está **terminado y
verificado**; lo que está en curso es el **despliegue en el cPanel de
Ecuahosting**.

Se está publicando primero en un **subdominio de pruebas**
(`pruebas.aag.org.ec`) para revisión, y después pasará al dominio principal.

El despliegue está **parado por dos bloqueos externos**: falta un registro DNS y
falta PHP 8.3 en el servidor. Todo lo demás está hecho.

**El WordPress actual no se ha tocado en ningún momento** y se ha verificado
tras cada paso.

---

## 2. Entornos y rutas

### Repositorio (máquina de trabajo)

```
C:\Users\erick\OneDrive\Obsidian\Personal\25.Proyectos\WEB\aag-portal
```

Es un repositorio git local, sin remoto. **Está en OneDrive**, así que se
sincroniza entre máquinas.

> ⚠️ **Ojo con esto:** existe además una copia de trabajo en WSL
> (`/home/erick/laravel_app`) que **no es la misma carpeta** y **no tiene git**.
> Durante el desarrollo se editó en WSL y se copiaron los archivos al
> repositorio. Antes de sincronizar en un sentido u otro, **comparar primero**:
> el `README.md` del repositorio es el bueno y el de WSL es el genérico de
> Laravel; `docs/` sólo existe en el repositorio.

### Entorno local de desarrollo

- WSL, distribución **Ubuntu-24.04** (no la 26.04: sólo trae PHP 8.5, incompatible)
- Proyecto en `/home/erick/laravel_app`
- **Apache** sirviendo en `http://localhost:8000` — el webroot es
  `~/public_html`, que es una copia de `public/`
- **`php artisan serve` NO funciona** en este entorno; usar siempre Apache
- MySQL: base `aag_portal`, usuario `aag`, contraseña `secret`

### Servidor de producción (cPanel Ecuahosting)

| Dato | Valor |
|---|---|
| Panel | `https://www.aag.org.ec:2083` |
| Usuario | `aagorgec` |
| Carpeta personal | `/home/aagorgec` |
| IP del servidor | `142.132.139.147` |
| Nombre del servidor | `red.hostingcolor.com` |
| cPanel | 136.0.29 |
| **PHP actual** | **8.2.31** (el portal necesita 8.3) |
| Shell/SSH | **NO habilitado** |
| Site Isolation | **Denegado por el administrador** |
| Certificado | comodín `*.aag.org.ec`, vence 11-oct-2026, vía AutoSSL |

---

## 3. Estado del despliegue

### Hecho y verificado

| Paso | Estado | Detalle |
|---|---|---|
| Subdominio creado | ✅ | `pruebas.aag.org.ec` → `/home/aagorgec/aag_portal/public` |
| Base de datos | ✅ | `aagorgec_portal` |
| Usuario de base de datos | ✅ | `aagorgec_portal`, con ALL PRIVILEGES. **La contraseña la tiene el cliente en su gestor** |
| Código del portal | ✅ | Extraído en `/home/aagorgec/aag_portal/` |
| Librerías (`vendor`) | ✅ | Subidas — **pero requieren PHP 8.3** |
| WordPress intacto | ✅ | Verificado tras cada paso |

### Bloqueado

| Bloqueo | Quién lo resuelve | Por qué |
|---|---|---|
| **Registro DNS** | El cliente (es de sistemas) | Sin él el nombre no resuelve para nadie |
| **PHP 8.3** | Ecuahosting | El `vendor` no arranca en 8.2 |

### Pendiente (cuando se desbloquee)

1. Crear el `.env` en el servidor (plantilla lista, ver §6)
2. Ejecutar migraciones, semillas y `storage:link` **por cron** (no hay shell)
3. Permisos: `.env` a 600
4. AutoSSL para el subdominio
5. Cron de `schedule:run` cada minuto
6. Comprobaciones finales

---

## 4. Los dos bloqueos, explicados

### 4.1 El DNS no lo controla el cPanel

**Esto costó descubrirlo y es contraintuitivo.** Los servidores de nombres de
`aag.org.ec` son:

```
aagadsrv03.aag.org.ec
aagadsrv04.aag.org.ec
```

Son **servidores internos de la propia AAG** (controladores de dominio). El
cPanel tiene una zona DNS para `aag.org.ec`, pero **no la consulta nadie**: es
decorativa.

**La prueba:** la zona del cPanel dice que `aag.org.ec` apunta a
`142.132.139.147`, pero internet resuelve `172.16.1.2` y `172.16.1.7`. Y `www`
figura como CNAME de `aag.org.ec`; si esa zona mandara, `www` resolvería a la IP
interna, y sin embargo resuelve al hosting.

Por eso `document.aag.org.ec` funciona (alguien creó el registro en el DNS de la
AAG) y `monitor.aag.org.ec` no responde desde fuera pese a estar creado en el
cPanel (su registro apunta a `172.16.1.16`).

**Lo que hay que pedir:**

| Tipo | Nombre | Valor | TTL |
|---|---|---|---|
| A | `pruebas` | `142.132.139.147` | 14400 |

**Comprobación rápida** desde cualquier equipo:
```
nslookup pruebas.aag.org.ec      → hoy: no existe
nslookup document.aag.org.ec     → devuelve 142.132.139.147
```

### 4.2 El servidor tiene PHP 8.2 y el portal necesita 8.3

Error exacto al ejecutar `artisan`:

```
Your Composer dependencies require a PHP version ">= 8.3.0". You are running 8.2.31.
```

Los paquetes que lo exigen:

- `intervention/image` 4.0.1 y `intervention/gif` → `^8.3`
- `intervention/image-laravel` 4.0.0 → `^8.3`
- `openspout/openspout` v4.32.0 → `~8.3.0`

**Se intentó bajar a versiones compatibles con 8.2 y se descartó a propósito.**
Ver §5.

**Lo que hay que pedir a Ecuahosting:**

> Habilitar **Site Isolation (CageFS + PHP Selector)** en la cuenta `aagorgec`,
> para poder asignar PHP 8.3 a un subdominio concreto sin afectar al resto.

En el PHP Selector aparece hoy: *"Site Isolation has been denied by your server
administrator. Isolation toggles are disabled."*

---

## 5. Decisiones tomadas (no revertir sin motivo)

### 5.1 NO degradar las dependencias a PHP 8.2

Se probó y se descartó. Motivos:

- `intervention/image` v3 ya no recibe mejoras, y es la librería que procesa
  **todas las imágenes que suban** al portal.
- Bajar una arrastra otras hacia atrás por efecto dominó.
- No resuelve el fondo: PHP 8.2 **termina soporte de seguridad en diciembre de
  2026**; la 8.3 lo tiene hasta diciembre de 2027.

Sería deuda técnica para ahorrar una noche.

### 5.2 NO subir el PHP global de la cuenta

El WordPress actual es **6.5.8 con 32 plugins**, entre ellos `js_composer`
(WPBakery), `revslider` y el tema comercial `listingpro` — los tres se
actualizan sólo con el tema, así que probablemente estén congelados.

WordPress 6.5 **no tiene soporte oficial para PHP 8.3** (llegó en 6.6). El
riesgo no es que la portada deje de cargar, sino fallos parciales que se
detectan días después.

Si Ecuahosting dijera que Site Isolation no es posible, **valorarlo con calma,
con copia de seguridad completa y probando el WordPress sección por sección**.

### 5.3 Document root del subdominio a `/public`, no a la raíz

`pruebas.aag.org.ec` apunta a `/home/aagorgec/aag_portal/public`.

Si apuntara a `aag_portal/`, quedarían accesibles desde internet el `.env` (con
las credenciales de base de datos y correo), `vendor/`, `storage/` y el código.

Además, así el paso a producción es cambiar **una casilla**: el Document Root
del dominio principal a esa misma carpeta.

### 5.4 La API pública viene apagada

`API_ENABLED=false`. Con ella apagada las rutas `/api/*` **ni se registran**:
responden 404, no 401. Se enciende sólo cuando alguien vaya a consumirla.
Ver `docs/API.md`.

---

## 6. Archivos preparados y listos para usar

En `WEB/deploy/`:

| Archivo | Qué es |
|---|---|
| `aag_portal.zip` (16 MB) | Proyecto completo **con `vendor`**. Ya subido y extraído en el servidor |
| `aag_portal_sin_vendor.zip` (1,6 MB) | Sin librerías. Ya extraído también |
| `env-produccion.txt` | **Plantilla del `.env` de producción**, con la clave de cifrado ya generada |

### Cómo crear el `.env` en el servidor

1. Administrador de archivos → `/home/aagorgec/aag_portal`
2. **+ File** → nombre: `.env`
3. Seleccionarlo → **Edit**
4. Pegar el contenido de `env-produccion.txt`
5. Sustituir `DB_PASSWORD=PEGA_AQUI_LA_CONTRASENA` por la contraseña real
6. **Permissions → 600**

> Si no se ve el archivo: *Settings* → *Show Hidden Files*.

### Cómo ejecutar los comandos sin shell (por cron)

cPanel → **Cron Jobs** → *Once Per Minute* → comando en una sola línea:

```
cd /home/aagorgec/aag_portal && /usr/local/bin/php artisan migrate --force > /home/aagorgec/instalacion.txt 2>&1 && /usr/local/bin/php artisan db:seed --force >> /home/aagorgec/instalacion.txt 2>&1 && /usr/local/bin/php artisan storage:link >> /home/aagorgec/instalacion.txt 2>&1
```

**Esperar 2 minutos, BORRAR la tarea** (si no, se repite cada minuto) y leer
`/home/aagorgec/instalacion.txt`.

> ⚠️ La contraseña del administrador **se imprime una sola vez** en la salida
> del seeder. Apuntarla de ahí.

### Cron definitivo (ese sí se queda)

```
* * * * *  /usr/local/bin/php /home/aagorgec/aag_portal/artisan schedule:run >/dev/null 2>&1
```

---

## 7. Lo que falta del proyecto (no del despliegue)

| Pendiente | Quién | Notas |
|---|---|---|
| Contenido de "Nosotros": premios, galería, estatutos | **La AAG** | La estructura está; falta el material real. No se puede inventar |
| Identificador de Google Analytics | Cliente | Integración hecha; falta pegar el `G-XXXX` en Ajustes. Revisar antes el aviso de cookies |
| Manual de despliegue con capturas en Word | Pendiente | Ver §9 |
| Verificación en móviles reales | Pendiente | Sólo se ha verificado el marcado |
| Modal de vista previa de PDF en navegador real | Pendiente | Tiene prueba unitaria; falta confirmación visual |
| Subida a Laravel 12 LTS | A planificar | Laravel 11 ya no recibe parches. Ver `SEGURIDAD.md` §12 |

---

## 8. Trabajo terminado en el portal (referencia)

Commits de esta tanda, del más reciente al más antiguo:

```
776bec0  docs: datos reales de la cuenta de cPanel
157e849  docs: preparacion para la revision final
f9b6dcb  feat(api): API publica con Sanctum, apagada de fabrica
a02e7fa  feat(convocatorias): archivo historico y vista previa de PDF
7ad7aef  feat: buscador global del sitio y feed RSS
70cd28e  feat(seo): redirecciones de las direcciones antiguas
cfc320c  perf+a11y: quitar 4600 consultas por pagina y cerrar huecos WCAG
49b5c4f  feat(despliegue): confiar en Cloudflare para ver la IP real
ea65fda  docs: los 6 pendientes de la revision estaban cerrados
5715c45  fix(seguridad): cerrar los avisos de dependencias que si aplican
```

Lo más relevante:

- **Seguridad**: avisos de dependencias de 29 a 7. Los 3 que tocaban código real
  están mitigados (CRLF en correo, subida sin autenticar de Livewire, guion
  inicial). Los otros 4 no son explotables aquí, comprobado en el código.
- **Rendimiento**: `/transparencia` pasó de **4645 consultas y 1,4 s** a **18 y
  9 ms**, memorizando los ajustes por petición.
- **Accesibilidad**: enlace "saltar al contenido" y landmark `<header>` (WCAG 2.1 AA).
- **Nuevo**: buscador global, feed RSS, sistema de redirecciones 301, API con Sanctum.

---

## 9. Sobre el manual con capturas (encargo pendiente)

El cliente pidió **un manual en Word, con capturas y recuadros señalando dónde
pulsar**, para poder repetir el despliegue en el futuro.

**Estado:** no hecho. Las capturas se irán tomando cuando se complete el
despliegue.

**Requisitos que puso el cliente:**

- Que **no salga la interfaz del navegador** (barra lateral de Edge, barra de
  direcciones con la URL de sesión) ni ningún aviso de Claude
- Sólo el contenido de la pantalla del cPanel, recortado y limpio
- En **Word (.docx)** para poder editarlo

**Cómo tomar capturas en este entorno** (esto costó resolverlo):

- `save_to_disk` de la herramienta de navegador **no funciona**: no guarda nada
- La solución es un script PowerShell con `PrintWindow` +
  `PW_RENDERFULLCONTENT`, que captura la ventana del navegador **aunque esté
  detrás**, sin robar el foco (Windows no deja hacerlo desde segundo plano)
- El script está en el scratchpad de la sesión anterior; hay que rehacerlo:
  busca el proceso `msedge`/`chrome` con más área, obtiene su `MainWindowHandle`
  y lo vuelca a PNG
- **Cuidado**: la variable del bucle no puede llamarse `$nombre` si el parámetro
  es `$Nombre` — PowerShell no distingue mayúsculas y pisa el valor
- Después hay que **recortar** el resultado para quitar el cromo del navegador

Las capturas van a:
```
WEB\manual-despliegue\capturas\
```

---

## 10. Contexto de la relación con el cliente

- Es **de sistemas** en la AAG, así que puede tocar el DNS él mismo.
- Prefiere que se le avise antes de crear o borrar algo en el cPanel, aunque
  autorizó a navegar y consultar libremente.
- **No maneja Laravel**: hay que darle los pasos concretos, con clics.
- Sus capturas de pantalla se guardan solas en
  `C:\Users\erick\OneDrive\Imágenes\Screenshots` y se pueden leer desde ahí.
- Tiene prisa por presentar el portal, pero ha aceptado **no tomar atajos** que
  metan deuda técnica (fue él quien preguntó si degradar afectaría a la
  seguridad, lo que llevó a descartar esa vía).

---

## 11. Lo primero que hay que hacer al retomar

1. Preguntar si ya está **el registro DNS** y **PHP 8.3**.
2. Si están: crear el `.env` (§6), lanzar el cron de instalación, ejecutar
   AutoSSL, y comprobar.
3. Si no están: no forzar. Sin PHP 8.3 la aplicación no arranca, y la
   alternativa (degradar dependencias) ya se descartó por buenos motivos.
4. Verificar **siempre** que `www.aag.org.ec` y `document.aag.org.ec` siguen
   respondiendo 200 después de cada cambio en el cPanel.
