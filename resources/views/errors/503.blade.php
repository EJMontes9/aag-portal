{{-- Servicio no disponible / modo mantenimiento (php artisan down).
     Autonoma: ver el comentario de _standalone. --}}
@include('errors._standalone', [
    'code'    => 503,
    'titulo'  => 'Portal en mantenimiento',
    'mensaje' => 'Estamos realizando tareas de mantenimiento en el portal. El servicio se restablecerá en breve.',
    'nota'    => 'Gracias por tu paciencia. Vuelve a intentarlo en unos minutos.',
])
