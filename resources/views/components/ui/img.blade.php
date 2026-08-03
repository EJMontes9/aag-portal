{{--
    Imagen con buenas prácticas de rendimiento por defecto:
    - loading="lazy" + decoding="async" salvo que se pida "eager" (imágenes
      above-the-fold / candidatas a LCP como el hero o el logo).
    - width/height obligatorios quedan como atributos para reservar espacio
      y evitar Cumulative Layout Shift (CLS) mientras carga.
    - fetchpriority="high" opcional para el elemento LCP de la página.

    Uso:
      <x-ui.img :src="$url" alt="..." width="800" height="450" />
      <x-ui.img :src="$heroUrl" alt="..." width="1600" height="900" loading="eager" fetchpriority="high" />
--}}
@props([
    'src',
    'alt' => '',
    'width' => null,
    'height' => null,
    'loading' => 'lazy',
    'fetchpriority' => 'auto',
    'sizes' => null,
])

@if($src)
<img
    src="{{ $src }}"
    alt="{{ $alt }}"
    @if($width) width="{{ $width }}" @endif
    @if($height) height="{{ $height }}" @endif
    loading="{{ $loading }}"
    decoding="async"
    @if($fetchpriority !== 'auto') fetchpriority="{{ $fetchpriority }}" @endif
    @if($sizes) sizes="{{ $sizes }}" @endif
    {{ $attributes }}
>
@endif
