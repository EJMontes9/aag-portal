{{-- Pagina no encontrada.

     NO redirige automaticamente al inicio a proposito. Una redireccion
     silenciosa deja a la persona en otra pagina sin entender que paso, hace
     imposible corregir un enlace mal escrito, y ante los buscadores convierte
     un 404 legitimo en una redireccion, lo que ensucia el indexado del sitio.
     En su lugar se explica el error y se ofrecen salidas. --}}
@include('errors._layout', [
    'code'    => 404,
    'titulo'  => 'Página no encontrada',
    'mensaje' => 'La dirección a la que intentas acceder no existe o el contenido ya no está disponible en el portal.',
    'sugerencias' => [
        'La dirección se escribió de forma incorrecta o está incompleta.',
        'El enlace que seguiste está desactualizado o roto.',
        'La publicación se archivó o cambió de dirección.',
        'El proceso o convocatoria ya finalizó y se retiró del portal.',
    ],
])
