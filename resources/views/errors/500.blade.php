{{-- Error interno. Autonoma: ver el comentario de _standalone. --}}
@include('errors._standalone', [
    'code'    => 500,
    'titulo'  => 'Error en el servidor',
    'mensaje' => 'Ocurrió un problema al procesar tu solicitud. No es culpa tuya: el fallo está de nuestro lado.',
    'nota'    => 'El equipo técnico ya tiene registro del incidente. Puedes intentarlo de nuevo en unos minutos.',
])
