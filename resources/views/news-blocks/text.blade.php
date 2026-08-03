@props(['data'])
{{-- Cuerpo de texto del artículo: es EL bloque que se lee entero, así que aquí
     manda la legibilidad sobre la densidad de la Propuesta B.

     Se sale de "prose-lg" (que a raíz 17px daba párrafos de ~19px, más grandes
     que el propio título de tarjeta) y se fija el cuerpo en 16px con interlineado
     1.75 y separación de 1.15em entre párrafos: es la horquilla de lectura larga
     y además iguala al contenido legacy de pages/news/show, que iba a 14px.

     El párrafo pasa de fg/85 a fg pleno: sobre blanco, el 85% de #222 baja el
     contraste sin ganar nada, y el gris ya está reservado a los metadatos.

     Los titulares intermedios se alinean con el resto del portal: Neulis en
     mayúsculas y navy, igual que los rótulos de sección.

     max-w-[72ch] + mx-auto: acota la medida de línea al entorno de 65-75
     caracteres. Va aquí, en el TEXTO, y no en la columna del artículo, para no
     encoger también las fotos, galerías y mapas que comparten esa columna. --}}
<div class="prose max-w-[72ch] mx-auto
            prose-headings:font-serif prose-headings:uppercase prose-headings:text-brand-navy
            prose-h2:text-[20px] prose-h2:mt-9 prose-h2:mb-3
            prose-h3:text-[17px] prose-h3:mt-7 prose-h3:mb-2
            prose-p:text-[16px] prose-p:text-fg prose-p:leading-[1.75] prose-p:my-[1.15em]
            prose-li:text-[16px] prose-li:leading-[1.7] prose-li:my-1
            prose-a:text-brand-primary prose-a:no-underline hover:prose-a:underline
            prose-strong:text-fg prose-strong:font-semibold
            prose-blockquote:border-brand-accent prose-blockquote:text-fg
            prose-img:rounded-card">
    {!! $data['content'] ?? '' !!}
</div>
