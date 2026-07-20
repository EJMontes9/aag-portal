# Línea gráfica — Propuesta B

Referencia única de diseño del portal AAG. Si vas a crear o modificar un
componente, léela antes: la identidad de B se pierde por acumulación de
pequeñas desviaciones (un radio de 12px aquí, una sombra allá).

Maquetas de origen: `out-propuestas/html/propuesta-b-*.html` y `_styles.js`
(bloque `styleB`, líneas 193-266).

---

## La identidad en una frase

Portal de gobierno de **alta densidad**: fondo gris, cajas blancas de esquinas
casi rectas delimitadas por un borde gris marcado, **sin ninguna sombra**,
texto condensado pequeño, cabecera de cuatro franjas de color apiladas, y el
amarillo institucional reservado para separadores y acciones.

---

## Reglas duras (no las rompas)

1. **Cero `box-shadow`.** La maqueta B no tiene ni una. La separación visual se
   expresa con `border: 1px solid #CCC` sobre fondo `#F5F5F5`. Añadir sombras
   es la forma más rápida de que esto vuelva a parecerse a la Propuesta A.
2. **Radio máximo 4px.** `rounded-card` = 4px (cajas), `rounded-pill` = 2px
   (chips y botones). **No existen píldoras ni círculos**: nada de
   `rounded-full`, `rounded-2xl`, `rounded-lg`.
3. **El amarillo `#EFC600` es el color de acción**, no un adorno. Se usa en el
   botón principal sobre fondo oscuro, en los filetes de 3px (`.rule-accent`)
   y en los encabezados de columna del footer. En la Propuesta A el botón
   principal era celeste — ese criterio queda invertido.
4. **Tipografía condensada.** `Barlow Condensed` para todo el texto y
   `Neulis Black` sólo para titulares. Ambas se auto-hospedan en
   `public/fonts`. Si se sustituye por una no condensada, se descuadra el
   ritmo horizontal de todas las rejillas.
5. **Diseño estático.** El único `:hover` de la maqueta es el de la
   navegación. Se permiten cambios de color en hover; **no** desplazamientos
   (`-translate-y`), escalados ni sombras que aparecen.
6. **Tracking positivo en titulares.** B usa `+0.5px`; A usaba negativo.
7. **Todo lo clicable necesita `focus-visible`.** Anillo de 2px en
   `brand-primary` (o `brand-accent` sobre navy). Dentro de cajas pegadas usa
   `ring-inset`, que si no el anillo se solapa con el elemento contiguo.

---

## Paleta

| Rol | Hex | Token |
|---|---|---|
| Navy (bandas, titulares) | `#2E2F63` | `brand-navy` |
| Navy oscuro (nav activo) | `#1E1F47` | — (literal en `.nav-link-active`) |
| Celeste (enlaces, iconos, acentos) | `#009CDF` | `brand-primary` |
| Amarillo (acción, filetes) | `#EFC600` | `brand-accent` |
| Tinte celeste (chips) | `#E5F4FB` | `brand-soft` |
| Fondo de página | `#F5F5F5` | `bg` |
| Superficie | `#FFFFFF` | `card` |
| Texto | `#222222` | `fg` |
| Texto secundario | `#666666` | `muted` |
| Borde | `#CCCCCC` | `border` |

Los valores viven en `site_settings` y se inyectan como variables CSS en
`layouts/app.blade.php`; los fallbacks están en `resources/css/app.css`.
**Usa siempre los tokens** (`bg-brand-navy`), nunca el hex literal.

## Escala tipográfica

**La raíz está a 106.25% (17px), no a los 16px por defecto.** Barlow Condensed
es estrecha: al mismo `font-size` se lee bastante más pequeña que una fuente de
ancho normal. Subir la raíz reescala todo lo que va en `rem` —texto y
espaciados— de forma proporcional, así que el diseño mantiene sus relaciones y
gana legibilidad. Ojo al leer el código: `text-sm` son ~15px, no 14px.

Los anchos de contenedor van en `px` a propósito y **no** escalan: el contenido
crece dentro de la misma caja.

| Uso | Tamaño | Familia |
|---|---|---|
| H1 hero | 46px | Neulis Black |
| Título de página interior | 28px | Neulis Black |
| Título de sección | 26px | Neulis Black |
| Rótulo de sección (MAYÚS) | 18px | Neulis Black |
| Cuerpo de artículo | 16px | Barlow Condensed |
| Título de tarjeta | 15px / 600 | Barlow Condensed |
| Cuerpo | 14-15px | Barlow Condensed |
| Meta, breadcrumb | 12-13px | Barlow Condensed |
| Chip, rótulo micro | 11-12px / 700 | Barlow Condensed |

**Suelo de 11px.** La maqueta baja hasta 9px; en mayúsculas condensadas con
tracking eso es prácticamente ilegible, y peor sobre fondo navy. Nada de texto
por debajo de 11px, y sólo para rótulos de una o dos palabras: cualquier cosa
que se lea de corrido va a 14px o más.

**Medida de línea.** En texto largo (cuerpo de noticia, respuestas de FAQ) la
columna se acota a 70-72 caracteres. Se acota el *texto*, no la columna, para
no encoger también fotos, galerías y mapas.

## Espaciado

- Contenedor: `max-w-[1440px]`, gutter 56px en escritorio (`.section-wrap`).
- Padding vertical de sección: 40px (B es más compacto que A, que usaba 64px).
- Gap de rejilla: 16-18px.

---

## Clases disponibles (`@layer components` de `app.css`)

Son el API de diseño: redefinirlas reestiliza todo el sitio sin tocar Blade.

| Clase | Para qué |
|---|---|
| `.section-wrap` | Contenedor maestro con gutter y padding vertical |
| `.kicker` | Eyebrow celeste sobre el título de sección |
| `.pill` | Chip rectangular sobre tinte celeste |
| `.chip-abierto` / `.chip-proceso` / `.chip-cerrado` | Estados diferenciados |
| `.btn-primary` | Acción principal sobre fondo claro (navy) |
| `.btn-ghost` | Acción secundaria sobre fondo claro |
| `.btn-white` | Acción principal sobre fondo oscuro (amarillo) |
| `.btn-ghost-white` | Acción secundaria sobre fondo oscuro |
| `.card-surface` | Caja blanca con borde marcado, sin sombra |
| `.nav-link` / `.nav-link-active` | Navegación sobre banda navy |
| `.rule-accent` | Filete amarillo de 3px |
| `.breadcrumb-bar` | Banda de migas de pan |
| `.page-header` | Cabecera de página interior |
| `.bg-cloud-gradient` | Gradiente navy→celeste (placeholder de imagen) |

---

## Decisiones tomadas respecto a la maqueta

Cosas donde el portal se aparta deliberadamente de los HTML de origen:

1. **Sin franja del Gobierno Nacional.** La maqueta incluye una banda
   "Gobierno del Encuentro · República del Ecuador". La AAG es una fundación
   de la Municipalidad de Guayaquil, no una entidad del Gobierno Nacional, y
   no puede exhibir su marca. Esa franja se usa para el menú `topbar`.
2. **Responsive.** Las maquetas son renders fijos a 1440px sin una sola media
   query. Todo el comportamiento por debajo de ese ancho es diseño nuevo.
3. **Estados diferenciados por color.** En B, "VIGENTE" y "CERRADO" comparten
   el mismo chip celeste y son indistinguibles de un vistazo. Se conserva la
   forma pero se diferencia el color en tonos apagados, para que el ciudadano
   no tenga que leer para saber si una convocatoria sigue abierta.
4. **Bloques sin maqueta.** `stats` y `values` no existen en ninguna página de
   B; se diseñaron con su vocabulario (caja blanca, borde, cifra en Neulis).
5. **Enlaces reales.** En la maqueta las tarjetas y los ítems del footer son
   texto plano; aquí son enlaces, como corresponde a un portal en producción.
6. **Tamaños mínimos.** Se subió el suelo tipográfico de 9px a 10-11px.

---

## Trampas conocidas

- **Clases de Tailwind construidas en runtime no funcionan.** `grid-cols-{{ $n }}`
  no se compila, porque Tailwind escanea el código fuente en build. Usa un
  `match()` que devuelva clases literales.
- **No renombres las claves de `settings`** de los bloques ni los valores enum
  guardados (`'navy'`, `'soft'`, `'editorial'`…): están sincronizados a mano
  entre `defaults()`, `filamentBlock()`, `editor/fields/*.blade.php` y la
  vista pública, y viven en la BD como JSON.
- **Los atributos `data-aos` / `data-stagger` / `data-word` van con su regla
  CSS.** Si borras el atributo pero no la regla, el elemento queda con
  `opacity: 0` para siempre.
- **El webroot es `~/public_html`, una copia de `public/`.** Cualquier asset
  estático nuevo (fuentes, imágenes) hay que copiarlo allí además de compilar.
