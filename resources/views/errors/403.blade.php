{{-- Acceso denegado: la direccion existe, pero no se tiene permiso. --}}
@include('errors._layout', [
    'code'    => 403,
    'titulo'  => 'Acceso no autorizado',
    'mensaje' => 'No tienes permiso para ver esta sección del portal. Si crees que deberías tener acceso, comunícate con el administrador.',
    'sugerencias' => [
        'La sección es de uso interno de la institución.',
        'Tu sesión no tiene los permisos necesarios.',
        'El contenido aún no se ha publicado.',
    ],
])
