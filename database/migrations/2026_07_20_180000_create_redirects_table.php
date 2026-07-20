<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Redirecciones de direcciones antiguas.
 *
 * El portal sustituye a un WordPress cuyas direcciones llevan anios indexadas
 * en Google y enlazadas desde otras instituciones y desde redes sociales. Si
 * al cambiar de sitio esas direcciones dejan de existir, cada visita que
 * llegue por ellas termina en un 404: se pierde el posicionamiento ganado y,
 * lo que es peor para una entidad publica, se rompen enlaces que la ciudadania
 * ya tiene guardados.
 *
 * Una redireccion 301 le dice al navegador y a Google "esto se mudo aqui de
 * forma permanente", y el posicionamiento se traslada a la direccion nueva.
 *
 * Se guarda en base de datos y no en un archivo de configuracion para que las
 * pueda mantener el personal de la AAG desde el panel, sin tocar codigo ni
 * volver a desplegar. Las direcciones antiguas van apareciendo con el tiempo
 * (alguien avisa de un enlace roto, o se ven en el informe de rastreo), asi
 * que esto es una tarea continua, no algo que se hace una vez.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::create('redirects', function (Blueprint $t) {
            $t->id();

            // Ruta de origen, SIN dominio y empezando por barra: "/quienes-somos".
            // Unica porque una misma direccion no puede llevar a dos sitios; el
            // indice ademas es lo que hace barata la consulta en cada 404.
            $t->string('from_path', 500)->unique();

            // Destino: ruta interna ("/nosotros") o direccion completa
            // ("https://..."), por si algun contenido se movio fuera del portal.
            $t->string('to_path', 500);

            // 301 permanente (por defecto, y el que traslada el posicionamiento)
            // o 302 temporal, para cuando algo se mueve solo por un tiempo.
            $t->unsignedSmallInteger('status_code')->default(301);

            // Permite desactivar una redireccion sin perder el registro, que es
            // util cuando se sospecha que una esta mal hecha.
            $t->boolean('is_active')->default(true);

            // Cuantas veces se uso y cuando fue la ultima. Sirve para dos cosas:
            // ver que direcciones antiguas siguen recibiendo visitas (merecen
            // atencion) y detectar las que ya no usa nadie (se pueden retirar).
            $t->unsignedInteger('hits')->default(0);
            $t->timestamp('last_used_at')->nullable();

            $t->string('notes', 500)->nullable();

            $t->timestamps();

            $t->index(['is_active', 'from_path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirects');
    }
};
