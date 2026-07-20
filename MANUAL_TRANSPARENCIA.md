# Manual — Transparencia (LOTAIP y Rendición de Cuentas)

Cómo se publican los documentos de transparencia en el portal de la AAG.

> **Resumen en una frase:** los documentos se suben por FTP al subdominio
> `document.aag.org.ec` y el portal los muestra solos; no hay que cargarlos
> dos veces ni tocar el panel.

---

## 1. Cómo funciona

Los archivos **viven en el subdominio**, no en el portal:

```
document.aag.org.ec/          ← aquí se sube por FTP
├── 2023/
│   ├── 01-Enero/
│   │   ├── Literal a4 - Metas y objetivos.pdf
│   │   └── Literal b2 - Distributivo del personal.pdf
│   └── 02-Febrero/ …
├── 2024/
│   └── 01-Enero/
│       └── Artículo 19/
│           └── 9. Listado de empresas.../
│               ├── Conjunto de datos.csv
│               ├── Diccionario de datos.csv
│               └── Metadatos.csv
└── 2026/
    └── Enero/
        └── config_link.txt      ← enlace en vez de archivos (ver §4)
```

El portal **no copia nada**. Cada noche revisa el subdominio, anota qué hay y
lo publica en `aag.gob.ec/transparencia` con el diseño del sitio. Las
descargas apuntan al archivo original en el subdominio.

**Por qué así:** las direcciones de los documentos ya están publicadas y
enlazadas desde documentación anterior. Si se movieran, se romperían enlaces
que la ciudadanía y otras instituciones tienen guardados.

---

## 2. Publicar documentos nuevos

1. Sube los archivos por FTP a la carpeta del año y mes que corresponda.
2. Espera a la sincronización nocturna (3:30 de la madrugada).
3. Listo: aparecen en el portal.

**No hay que hacer nada más.** Ni entrar al panel, ni volver a subir nada.

### Si no puedes esperar

Desde el servidor:

```bash
cd /home/aagorgec/laravel_app
php artisan lotaip:sincronizar
```

Para ver qué haría **sin aplicar nada**:

```bash
php artisan lotaip:sincronizar --dry-run
```

Y para la sección de Rendición de Cuentas:

```bash
php artisan lotaip:sincronizar --seccion=rendicion
```

---

## 3. Cómo organizar las carpetas

El portal deduce el año y el mes del nombre de la carpeta. Reconoce estas
formas, que son las que ya se han usado:

| Nombre de carpeta | Se interpreta como |
|---|---|
| `01-Enero` | Enero |
| `1-Enero` | Enero |
| `Enero` | Enero |
| `01_Enero` | Enero |

**Lo que hay entre el mes y el archivo se usa para agrupar.** Por ejemplo:

```
2024/01-Enero/Artículo 19/9. Listado de empresas.../Metadatos.csv
                          └────── agrupador ──────┘
```

En el portal aparece un encabezado *"9. Listado de empresas…"* con sus
archivos debajo. Sin esta agrupación, un mes de 2024 serían 75 archivos
seguidos llamados todos "Conjunto de datos", "Diccionario de datos" o
"Metadatos", y no se sabría a cuál corresponde cada uno.

La carpeta `Artículo 19` se ignora como agrupador: es un nivel organizativo,
no un literal de la LOTAIP.

Si no hay carpeta intermedia (como en 2023), los archivos aparecen sueltos
bajo su mes.

---

## 4. Redirigir un mes a otro sitio (`config_link.txt`)

Cuando la información de un periodo se publica en otro portal —por ejemplo el
de Transparencia Activa de la Defensoría del Pueblo— el mes puede mostrar
**un enlace en vez del listado de archivos**.

Para ello, crea en la carpeta del mes un archivo llamado **`config_link.txt`**
con este contenido:

```
https://transparencia.dpe.gob.ec/entidades/1833|Transparencia activa
```

El formato es:

```
URL|Texto del enlace
```

- Antes de la barra `|`: la dirección de destino. Debe empezar por `https://`
  (o `http://`); cualquier otra cosa se ignora por seguridad.
- Después de la barra: el texto que verá el ciudadano. Es **opcional**; si no
  se pone, se muestra "Ver documentos".

Con ese archivo presente, el portal muestra el mes como un enlace externo.
**Los archivos que haya en esa carpeta se siguen subiendo pero no se listan**,
que es exactamente el comportamiento del explorador del subdominio.

Para volver a mostrar los archivos, basta con **borrar el `config_link.txt`**
y sincronizar.

---

## 5. Los tres estados de un mes

En el portal, cada mes se ve de una de estas tres formas:

| Se ve así | Significa |
|---|---|
| 📁 **Enero** — 75 archivos | Hay archivos y se listan en el portal |
| 🔗 **Febrero** · Transparencia activa | Hay un `config_link.txt`: enlaza fuera |
| 📁 **Junio** — Sin documentos | La carpeta está vacía o no existe |

---

## 6. Configuración

### Dirección del subdominio

**Panel → Ajustes del sitio → Documentos**

Ahí se indica `https://document.aag.org.ec`. Si algún día cambia el
subdominio, se cambia aquí y **todos** los enlaces se recalculan solos.

> Los documentos que tengan guardada una dirección completa conservan su
> enlace original aunque se cambie este ajuste. Es deliberado: la
> documentación ya difundida sigue funcionando pase lo que pase.

### Sincronización automática

Está programada cada día a las **3:30**. Requiere que el servidor tenga
configurado el cron de Laravel:

```
* * * * * cd /home/aagorgec/laravel_app && php artisan schedule:run >> /dev/null 2>&1
```

En cPanel: *Cron Jobs* → cada minuto. Ese mismo cron se encarga también de
cerrar automáticamente las convocatorias vencidas.

---

## 7. Cargar un documento suelto desde el panel

Para casos puntuales, también se puede dar de alta un documento a mano:

**Panel → Transparencia → Documentos → Crear**

Ahí se elige dónde está el archivo:

- **En el subdominio** — se escribe su ruta (`2026/01-Enero/informe.pdf`) o la
  dirección completa. No sube nada; sólo lo enlaza.
- **Subirlo a este portal** — se sube el archivo al hosting del portal.

Los documentos añadidos a mano conviven con los sincronizados.

> ⚠️ La sincronización nocturna **no borra** lo que hayas creado a mano, pero
> tampoco lo actualiza. Si un mes lo gestionas por FTP, es mejor no mezclarlo
> con altas manuales.

---

## 8. Problemas frecuentes

**Subí archivos y no aparecen**
1. ¿Ha pasado la sincronización de las 3:30? Fuerza con
   `php artisan lotaip:sincronizar`.
2. ¿La carpeta del mes tiene un nombre reconocible? (ver §3)
3. ¿Hay un `config_link.txt` en esa carpeta? Entonces el mes muestra el
   enlace y **oculta los archivos** a propósito (ver §4).

**Un mes muestra un enlace y quiero que muestre los archivos**
Borra el `config_link.txt` de esa carpeta y sincroniza.

**Cambié la dirección del subdominio y se rompieron enlaces**
Los documentos con ruta relativa se recalculan solos. Los que tuvieran
guardada una dirección completa conservan la antigua: hay que editarlos.

**Todos los meses dicen "sin documentos"**
Comprueba que la dirección del subdominio esté bien puesta en
*Ajustes del sitio → Documentos*, y que responda desde el navegador.

---

## 9. Nota sobre el explorador del subdominio

El subdominio tiene su propio explorador de archivos (`index.php`). El portal
**ya no depende de él** para mostrar los documentos: lee la estructura una vez
al día y enlaza directamente a cada archivo.

Se detectó un punto a corregir en ese explorador, en la función que impide
salir de la carpeta raíz:

```php
// Actual: compara sólo el prefijo del texto
return strpos(realpath($path), realpath($root)) === 0;

// Recomendado: añadir el separador
return strpos(realpath($path), realpath($root) . DIRECTORY_SEPARATOR) === 0;
```

Tal como está, si algún día existiera una carpeta hermana llamada
`document.aag.org.ec.bak` (o similar), sus archivos pasarían la validación
porque la ruta empieza igual. No es explotable hoy, pero conviene arreglarlo.
