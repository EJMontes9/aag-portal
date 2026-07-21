# Documentación — Portal AAG

Portal institucional de la **Autoridad Aeroportuaria de Guayaquil**.

---

> 🔴 **Despliegue en curso.** El estado real del servidor, lo que falta y las
> decisiones tomadas están en
> [`ESTADO_Y_TRASPASO.md`](ESTADO_Y_TRASPASO.md). **Léelo primero** si vas a
> retomar el despliegue.

## Por dónde empezar

**Si vas a gestionar contenido**
→ [`MANUAL_CONTENIDO.md`](MANUAL_CONTENIDO.md) — páginas, noticias, menús
→ [`MANUAL_TRANSPARENCIA.md`](MANUAL_TRANSPARENCIA.md) — LOTAIP y Rendición de Cuentas
→ [`MANUAL_USUARIOS_ROLES.md`](MANUAL_USUARIOS_ROLES.md) — dar acceso al panel

**Si vas a desplegar**
→ [`DESPLIEGUE_PASO_A_PASO.md`](DESPLIEGUE_PASO_A_PASO.md) — **empieza por aquí**: publicar en pruebas, revisar y pasar al dominio real, con Cloudflare
→ [`DESPLIEGUE_CPANEL.md`](DESPLIEGUE_CPANEL.md) — lista de comprobación técnica
→ [`SEGURIDAD.md`](SEGURIDAD.md) — puntos que verificar en el servidor

**Si vas a tocar el código**
→ [`ARQUITECTURA.md`](ARQUITECTURA.md) — cómo está construido
→ [`SEGURIDAD.md`](SEGURIDAD.md) — **léelo antes de tocar nada de seguridad**
→ [`LINEA_GRAFICA.md`](LINEA_GRAFICA.md) — el sistema de diseño

---

## Índice

| Documento | Contenido | Para quién |
|---|---|---|
| [ARQUITECTURA.md](ARQUITECTURA.md) | Pila tecnológica, modelo de contenido, sistema de bloques, caché, comandos | Desarrollo |
| [SEGURIDAD.md](SEGURIDAD.md) | Medidas implementadas, por qué existen y qué no romper | Desarrollo / Sistemas |
| [DESPLIEGUE_PASO_A_PASO.md](DESPLIEGUE_PASO_A_PASO.md) | Publicación en subdominio de pruebas, cambio al dominio real, Cloudflare, Analytics | Sistemas |
| [DESPLIEGUE_CPANEL.md](DESPLIEGUE_CPANEL.md) | Lista de comprobación de puesta en producción | Sistemas |
| [LINEA_GRAFICA.md](LINEA_GRAFICA.md) | Paleta, tipografía, formas y reglas de diseño | Desarrollo / Diseño |
| [MANUAL_CONTENIDO.md](MANUAL_CONTENIDO.md) | Uso diario del panel | Redacción / Comunicación |
| [MANUAL_TRANSPARENCIA.md](MANUAL_TRANSPARENCIA.md) | Publicación de documentos LOTAIP | Transparencia |
| [MANUAL_USUARIOS_ROLES.md](MANUAL_USUARIOS_ROLES.md) | Cuentas, roles y permisos | Administración |
| [API.md](API.md) | API pública de solo lectura: cómo activarla, tokens, endpoints | Desarrollo / Sistemas |

---

## El sistema en cuatro líneas

Portal en **Laravel 11 + Filament 3**, con las páginas compuestas por
**bloques** que se editan visualmente. El diseño sale de **tokens**
configurables, no de valores fijos. Los documentos de transparencia **viven en
un subdominio aparte** y el portal los recoge cada noche. El acceso al panel
se reparte con **cinco roles** de permisos distintos.

Las direcciones del sitio anterior se mantienen vivas desde
**Configuración › Redirecciones**, sin desplegar nada.

---

## Antes de publicar el sitio

Cuatro puntos que **no se pueden dar por hechos**:

1. `APP_DEBUG=false` y `APP_ENV=production` en el `.env` del servidor.
2. **Comprobar en el servidor** que `/storage` no ejecuta PHP
   (`DESPLIEGUE_CPANEL.md`, punto 5). Si el `.htaccess` no llegó por FTP, la
   protección desaparece sin avisar.
3. El **cron** de Laravel configurado.
4. Cambiar la contraseña inicial del administrador.

Lista completa en [`DESPLIEGUE_CPANEL.md`](DESPLIEGUE_CPANEL.md).

---

## Estado conocido

Cosas que conviene tener presentes, documentadas con detalle en
[`SEGURIDAD.md`](SEGURIDAD.md) §10:

- Los documentos de transparencia son **públicos desde que se suben**; no hay
  estado de borrador. Es el comportamiento buscado.
- **Laravel 11 ya no recibe parches de seguridad.** Quedan tres avisos
  mitigados en la aplicación pero sin parche oficial: sólo existe en Laravel
  12.60+. No bloquea publicar; conviene planificar el salto de versión como
  trabajo aparte. Detalle en [`SEGURIDAD.md`](SEGURIDAD.md) §12.
- El **responsive no se ha verificado en dispositivos reales**, solo sobre el
  marcado.
- El explorador del subdominio de documentos (código ajeno a este repositorio)
  tiene una comprobación de ruta que conviene endurecer.
- La **API pública viene desactivada** (`API_ENABLED=false`). Con ella apagada
  las rutas `/api/*` ni se registran: responden 404, no 401. Se activa cuando
  alguien vaya a consumirla, no antes. Ver [`API.md`](API.md).

---

## Entorno de desarrollo

Ubuntu 24.04 sobre WSL con Apache + PHP 8.3 + MySQL 8, reproduciendo el
esquema de cPanel. Instrucciones en `LEVANTAR_PROYECTO.md`, en la carpeta del
proyecto.
