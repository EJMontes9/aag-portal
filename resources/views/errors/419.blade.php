{{-- Sesión caducada (token CSRF expirado). Típico al dejar un formulario
     abierto mucho rato y enviarlo después. --}}
@include('errors._layout', [
    'code'    => 419,
    'titulo'  => 'La sesión ha caducado',
    'mensaje' => 'Pasó demasiado tiempo desde que abriste el formulario. Vuelve a la página anterior y envíalo de nuevo; los datos no se perdieron si tu navegador los conserva.',
    'sugerencias' => [
        'El formulario estuvo abierto demasiado tiempo sin enviarse.',
        'Se cerró la sesión en otra pestaña.',
    ],
])
