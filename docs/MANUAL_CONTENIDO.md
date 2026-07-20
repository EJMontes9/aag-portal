# Manual — Gestión de contenido

Cómo publicar y mantener el contenido del portal. Para el uso diario.

Panel: **`https://TU-DOMINIO/admin`**

---

## 1. Páginas por bloques

Las páginas del portal se montan con **bloques**: piezas que se añaden,
reordenan y quitan. La portada, por ejemplo, es una sucesión de bloques.

### El editor visual

**Páginas → (elegir una) → Editor visual**

Es la forma recomendada: se ve la página real mientras se edita.

| Acción | Cómo |
|---|---|
| Editar un bloque | Pulsa sobre él; se abre el panel lateral |
| Añadir | Botón "Agregar bloque" y elige el tipo |
| Reordenar | Flechas ▲▼ de cada bloque |
| Ocultar sin borrar | Icono del ojo |
| Eliminar | Icono de papelera |

Los cambios se guardan al pulsar **Guardar** en el panel lateral.

### Bloques disponibles

| Bloque | Para qué |
|---|---|
| **Hero editorial** | Cabecera principal, con 4 variantes de composición |
| **Banner rotativo** | Carrusel de imágenes |
| **Accesos directos** | Rejilla de iconos con enlaces |
| **Noticias destacadas** | Últimas noticias, automáticas |
| **Vuelos** | Bloque de estado de vuelos con enlace al portal de TAGSA |
| **Convocatoria destacada** | Muestra una convocatoria con su cronograma |
| **Valores institucionales** | Lista numerada |
| **Números / Estadísticas** | Cifras destacadas |
| **Texto + Imagen** | Dos columnas |
| **Vídeo** | YouTube o Vimeo |
| **Llamado a la acción** | Banda con botón |
| **Preguntas frecuentes** | Acordeón, automático desde las FAQ |
| **Navegador de Transparencia** | Árbol LOTAIP (ver su manual) |
| **Formulario** | Inserta un formulario creado en el constructor |
| **Mapa** | Mapa incrustado |

> **Mapas y vídeos:** pega la dirección o el "código para insertar". Solo se
> aceptan Google Maps, OpenStreetMap, YouTube y Vimeo; cualquier otra cosa se
> descarta por seguridad.

---

## 2. Noticias

**Noticias → Crear**

- **Título** — genera solo la dirección web (*slug*); se puede cambiar.
- **Categoría** — se gestionan en *Categorías de noticias*.
- **Portada** — se comprime automáticamente a WebP.
- **Extracto** — el resumen que aparece en los listados.
- **Contenido** — editor enriquecido, o bloques para artículos más elaborados
  (texto, imagen, galería, cita, descarga, vídeo…).
- **Estado** — `borrador` no se ve en el portal; `publicado` sí.
- **Destacada en portada** — la incluye en el bloque de noticias del home.

> Una noticia publicada con fecha futura no aparece hasta que llega esa fecha.

---

## 3. Proyectos, convocatorias y FAQ

**Proyectos** — obras e iniciativas, con ubicación, presupuesto, hitos y
galería. El estado (`en curso`, `completado`, `planificado`) se muestra como
una etiqueta de color.

**Convocatorias** — procesos de contratación y avisos. Admiten cronograma,
requisitos y documentos adjuntos. Las vencidas **se cierran solas** cada hora.
Marca *Destacada* para que aparezca en la portada.

**Preguntas frecuentes** — agrupadas por categoría; se muestran en `/faq` y en
el bloque de acordeón.

---

## 4. Formularios

**Formularios → Crear**

Añade los campos que necesites (texto, correo, teléfono, selección, archivo…),
marca cuáles son obligatorios e indica a qué correos avisar.

Para publicarlo, añade un bloque **Formulario** a una página y elígelo.

Los envíos se consultan en **Envíos de formulario**. Contienen datos
personales, así que solo son visibles para administradores.

---

## 5. Menús

**Menús** — cada uno tiene una **ubicación**, que decide dónde sale:

| Ubicación | Dónde aparece |
|---|---|
| `header` | Navegación principal |
| `topbar` | Franja amarilla superior |
| `footer` | Columna "Enlaces" del pie |
| `footer_secondary` | Columna "Institución" |
| `footer_services` | Columna "Servicios" (opcional) |
| `footer_transparency` | Columna "Transparencia" (opcional) |

Las columnas opcionales **no aparecen** si no existe su menú. Los elementos
admiten submenús (un nivel).

---

## 6. Galería de medios

**Medios** — todas las imágenes y documentos subidos. Las imágenes se
comprimen a WebP automáticamente.

Formatos admitidos: JPG, PNG, GIF, WebP, PDF, Word, Excel, PowerPoint y MP4.
Otros se rechazan por seguridad.

---

## 7. Ajustes del sitio

**Ajustes del sitio** — configuración global:

| Pestaña | Contiene |
|---|---|
| Identidad | Nombre, logo, favicon |
| Tipografías y Colores | Paleta institucional |
| Tema | Modo claro/oscuro |
| Contacto | Dirección, teléfono, correo |
| Documentos | Dirección del subdominio de transparencia |
| Redes Sociales | Enlaces |
| Header / CTA | Botón destacado y franja superior |
| Animaciones | Movimiento del front |
| Correo SMTP | Envío de correo |
| Footer y SEO | Pie, metadatos y analítica |

> ⚠️ Los colores y tipografías afectan a **todo el portal**. Ver
> `LINEA_GRAFICA.md` antes de cambiarlos: la paleta es la institucional.

---

## 8. Transparencia

Tiene su propio manual, porque funciona distinto: los documentos se suben por
FTP a un subdominio y el portal los recoge solo.

👉 **`MANUAL_TRANSPARENCIA.md`**

---

## 9. Registro de actividad

**Registro de actividad** — quién cambió qué y cuándo. Útil para saber por qué
algo dejó de verse como estaba.

---

## 10. Problemas frecuentes

**Publiqué algo y no se ve**
Comprueba el estado (`publicado`) y que la fecha no sea futura. El portal
cachea algunas listas 5 minutos.

**Cambié un color y no cambia nada**
La configuración se cachea. Si tras guardar sigue igual, pide a soporte
técnico que ejecute `php artisan cache:clear`.

**No puedo subir un archivo**
Revisa el formato (§6) y que no supere el tamaño máximo. Los formatos no
admitidos se rechazan a propósito.

**No veo una sección del panel**
Tu rol no tiene permiso. Ver `MANUAL_USUARIOS_ROLES.md`.

**El mapa o el vídeo no aparece**
Solo se aceptan Google Maps, OpenStreetMap, YouTube y Vimeo. Comprueba que la
dirección sea de uno de ellos y empiece por `https://`.
