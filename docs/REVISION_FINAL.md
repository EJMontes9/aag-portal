# Preparación para la revisión final

Documento para llevar a la revisión: qué se entrega, qué cumple cada punto del
pliego, qué no está y por qué, y cómo demostrar en vivo lo que pregunten.

No es un documento comercial. Está escrito para que quien lo lea pueda
comprobar cada afirmación, y para que las cosas que faltan aparezcan aquí antes
de que las encuentre otro.

---

## 1. Resumen

Portal institucional en Laravel 11 + Filament 3, desplegable sobre el cPanel de
Ecuahosting ya contratado. Las páginas se componen por **bloques** que se
añaden y quitan desde el panel. El diseño sale de tokens configurables, no de
valores fijos en el código.

Los **1137 documentos de transparencia** siguen en su subdominio
(`document.aag.org.ec`) y no se han movido: los enlaces publicados hasta hoy
siguen funcionando exactamente igual. El portal los lee, no los reubica.

---

## 2. Cumplimiento del pliego

### 2.1 Estructura del portal (sección 7 del TDR)

| Requisito | Estado | Dónde verlo |
|---|---|---|
| Inicio con banners rotativos, noticias y accesos rápidos | ✅ | `/` |
| Noticias y boletines con archivo histórico | ✅ | `/noticias` (paginado completo) |
| Transparencia y LOTAIP | ✅ | `/transparencia` |
| Contacto y ubicación, FAQ | ✅ | `/contacto`, `/faq` |
| Convocatorias con pop-up, vigentes y archivo | ✅ | `/convocatorias` (paginado + filtro por año) |
| Motor de búsqueda interno | ✅ | `/buscar` — busca en noticias, proyectos, convocatorias, FAQ y páginas |
| Formularios configurables | ✅ | Panel → Formularios |
| Galería multimedia | ✅ | Bloques de galería y de vídeo |
| Integración con redes sociales | ✅ | Enlaces configurables + botones de compartir |
| Visor y descarga de PDF | ✅ | Ficha de convocatoria: "Ver" y "Descargar" |
| Boletín con suscripción | ✅ | Pie de página |
| Guía de viaje, proyectos y obras | ✅ | `/guia-de-viaje`, `/proyectos` |
| Estadísticas de visitas (Google Analytics) | ⚠️ | Integrado; **falta pegar el identificador** en Ajustes |
| Nosotros: pilares y plan estratégico | ✅ | `/nosotros` |
| Nosotros: premios, galería institucional, estatutos | ❌ | **Falta el material.** Ver §5 |

### 2.2 Seguridad y rendimiento (sección 9)

| Requisito | Estado | Cómo se cumple |
|---|---|---|
| Protección XSS, CSRF, SQL, OWASP | ✅ | Protecciones nativas + saneado por lista blanca al guardar |
| Autenticación segura y sesiones robustas | ✅ | Sesión cifrada, cookie `secure`+`httpOnly`+`SameSite=strict`, caducidad 60 min, bcrypt 12 rondas |
| Laravel Sanctum | ✅ | API implementada y protegida, **apagada de fábrica**. Ver §3 |
| Cifrado de datos sensibles | ✅ | Sesión cifrada; contraseñas con bcrypt |
| Cloudflare WAF / DDoS / HTTPS | ⏳ | Se configura al desplegar. Guía en `DESPLIEGUE_PASO_A_PASO.md` |
| Lazy loading, compresión, minificación | ✅ | 19 imágenes con carga diferida, conversión automática a WebP, Vite |
| Caché de consultas | ✅ | Con invalidación por eventos de modelo |
| Actualizaciones con soporte LTS | ⚠️ | **Leer §4 antes de la revisión** |

### 2.3 Accesibilidad WCAG 2.1 AA (sección 8)

| Punto | Estado |
|---|---|
| Idioma declarado (`lang="es"`) | ✅ |
| Landmarks (`header`, `nav`, `main`, `footer`) | ✅ |
| Enlace "saltar al contenido" | ✅ |
| Etiquetas en campos de formulario | ✅ |
| Texto alternativo en imágenes | ✅ (con campo en el panel) |
| Foco visible por teclado | ✅ |
| Modales con `role="dialog"`, Escape y foco atrapado | ✅ |

> **Salvedad honesta:** esto es lo verificable sobre el marcado. Una auditoría
> WCAG completa incluye contraste real, pruebas con lector de pantalla y
> navegación por teclado de principio a fin en dispositivos reales. Eso **no se
> ha hecho** y no debe presentarse como hecho.

### 2.4 Migración de contenidos (sección 10)

| Requisito | Estado |
|---|---|
| Redirecciones 301 para no perder posicionamiento | ✅ Sistema listo en **Configuración › Redirecciones** |
| Migración de textos, imágenes y documentos | ⏳ Según avance la carga de contenido |
| Monitoreo de enlaces rotos | ✅ La tabla cuenta las visitas de cada redirección |

---

## 3. Demostrar la API en vivo

Si en la revisión preguntan por Sanctum, esto se enseña en dos minutos.

**Estado de partida:** apagada. Es deliberado y conviene explicarlo antes de que
parezca un olvido:

> Una API que nadie consume no es una funcionalidad, es superficie expuesta. Con
> el interruptor apagado las rutas ni se registran: `/api/v1/noticias` responde
> **404**, indistinguible de una dirección inventada. Si se dejara encendida
> respondería **401**, que le confirma a cualquiera que sondee el servidor que
> ahí hay algo por lo que insistir.

**Para encenderla delante de quien lo pida:**

```bash
# 1. En el .env
API_ENABLED=true

# 2. Este paso NO es opcional
php artisan config:cache

# 3. Comprobar que las rutas existen
php artisan route:list --path=api
```

Con la configuración cacheada Laravel ni lee el `.env`: sin el segundo paso no
pasa nada y parece que no funciona. Es el fallo más habitual.

**Probarla:**

```bash
# Sin token -> 401
curl -i https://TU-DOMINIO/api/v1/noticias

# Se crea un token en el panel: Configuración › Tokens de API
# (se muestra UNA sola vez)

curl -H "Authorization: Bearer EL-TOKEN" \
     -H "Accept: application/json" \
     https://TU-DOMINIO/api/v1/noticias
```

**Para volver a apagarla:** `API_ENABLED=false` + `php artisan config:cache`.

Detalle completo en [`API.md`](API.md).

---

## 4. Laravel 11: qué decir si lo preguntan

**Esto va a salir en cualquier auditoría técnica.** Mejor llevarlo preparado que
improvisar.

### El hecho

`composer audit` reporta **7 avisos de seguridad en 5 paquetes**. No es que
falte actualizar: **ninguna versión de Laravel 11 está libre de avisos**. Los
parches existen solo a partir de Laravel 12.60.

### Lo que sí se hizo

Se partió de **29 avisos en 14 paquetes** y se bajó a 7 actualizando todo lo que
admitía actualización sin cambiar de versión mayor:

| Paquete | Antes | Ahora |
|---|---|---|
| `symfony/html-sanitizer` | v7.4.8 | v7.4.14 |
| `symfony/http-kernel` | v7.4.8 | v7.4.14 |
| `symfony/mime` | v7.4.8 | v7.4.13 |
| `symfony/routing` | v7.4.8 | v7.4.13 |
| `guzzlehttp/guzzle` | 7.10.0 | 7.15.1 |

### De los 7 restantes, 4 no son explotables aquí

Comprobado en el código, no supuesto:

| Aviso | Por qué no aplica |
|---|---|
| Filament: XSS en `RichEditor` deshabilitado | No se usa ningún `RichEditor` deshabilitado |
| Laravel: confusión de ruta en URL firmada | No se usan URLs firmadas |
| `symfony/mailer`: inyección en `SendmailTransport` | El correo sale por SMTP, no por sendmail |
| Filament: `AttachAction`/`AssociateAction` | Esas acciones no se usan |

### Los 3 que sí tocaban código están mitigados

**Inyección CRLF en la regla `email`** (severidad alta). Las direcciones acaban
siendo destinatario de un envío, así que un salto de línea permitiría añadir
cabeceras y usar el formulario del portal para enviar spam a nombre de la AAG.
Mitigado con `app/Rules/CorreoSeguro.php`, aplicado **además** de `email:rfc`.
Verificado con 10 casos: pasan los 3 correos válidos y fallan los 7 ataques.

**Subida temporal sin autenticar de Livewire** (media). `/livewire/upload-file`
responde sin sesión en las pantallas de login y, por defecto, acepta cualquier
archivo de 12 MB. Mitigado en `config/livewire.php`: tipos permitidos, 10 MB y
10 peticiones por minuto en lugar de 60.

**Guion inicial en la dirección de correo.** Cubierto también por
`CorreoSeguro`.

### La frase honesta

> Los vectores concretos están cerrados y verificados. Pero mitigar no es
> parchear: la rama 11 seguirá acumulando avisos y ninguno traerá arreglo
> oficial. La recomendación es planificar la subida a Laravel 12 LTS como
> trabajo aparte, con su propio periodo de pruebas — no metida con prisa antes
> de publicar, que es cuando se rompen las cosas.

Detalle completo en [`SEGURIDAD.md`](SEGURIDAD.md) §12.

---

## 5. Lo que no está, y por qué

Mejor decirlo aquí que dejar que aparezca en la revisión.

**Contenido de "Nosotros": premios, galería institucional y estatutos.**
La estructura está y se carga desde el panel sin tocar código, pero **falta el
material de la AAG**: los premios reales, las fotografías institucionales y el
texto de los estatutos. No es algo que pueda redactarse desde fuera.

**Identificador de Google Analytics.**
La integración está hecha y funciona; falta crear la propiedad y pegar el
identificador en Ajustes del sitio. Cinco minutos.
Antes de activarlo conviene revisar el aviso de cookies con quien lleve la
protección de datos: Analytics deja cookies y trata datos de visitantes.

**Cloudflare.**
Se configura al pasar al dominio definitivo, no antes: si se activa mientras se
está montando, cuesta distinguir si un fallo es del portal o del proxy.

**Verificación en dispositivos reales.**
El diseño adaptable está resuelto en el marcado, pero no se ha probado en
móviles y tabletas físicos. Es de lo primero que debería hacerse en el
subdominio de pruebas.

**El modal de vista previa de PDF no se ha abierto en un navegador real.**
La lógica tiene prueba unitaria, pero el entorno de desarrollo no permitía
acceder desde el navegador. Confirmarlo visualmente al desplegar.

---

## 6. Qué mirar primero en el subdominio de pruebas

Por orden de probabilidad de encontrar algo:

1. El sitio en un **móvil real**, no en el simulador del navegador.
2. Abrir la **vista previa de un PDF** en una convocatoria.
3. Enviar el **formulario de contacto** y confirmar que llega el correo.
4. Entrar a **Transparencia** y abrir documentos de varios años y meses.
5. Probar el **buscador** con tildes, con términos que no existen y con textos largos.
6. Entrar al panel con **cada rol** y comprobar que ninguno ve lo que no debe.
7. Añadir y quitar **bloques** de una página desde el editor visual.

---

## 7. Documentación entregada

| Documento | Contenido |
|---|---|
| [`ARQUITECTURA.md`](ARQUITECTURA.md) | Cómo está construido el sistema |
| [`SEGURIDAD.md`](SEGURIDAD.md) | Medidas, por qué existen y qué no romper |
| [`DESPLIEGUE_PASO_A_PASO.md`](DESPLIEGUE_PASO_A_PASO.md) | Publicación en pruebas y paso al dominio real |
| [`DESPLIEGUE_CPANEL.md`](DESPLIEGUE_CPANEL.md) | Lista de comprobación técnica |
| [`API.md`](API.md) | API pública: activación, tokens, endpoints |
| [`LINEA_GRAFICA.md`](LINEA_GRAFICA.md) | Paleta, tipografía y reglas de diseño |
| [`MANUAL_CONTENIDO.md`](MANUAL_CONTENIDO.md) | Uso diario del panel |
| [`MANUAL_TRANSPARENCIA.md`](MANUAL_TRANSPARENCIA.md) | Publicación de documentos LOTAIP |
| [`MANUAL_USUARIOS_ROLES.md`](MANUAL_USUARIOS_ROLES.md) | Cuentas, roles y permisos |
