<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Origen del archivo de cada documento LOTAIP.
 *
 * Los documentos de transparencia de la AAG viven en un subdominio propio
 * (https://document.aag.org.ec/), al que se suben por FTP. Esas direcciones ya
 * estan publicadas y enlazadas desde documentacion anterior, asi que no se
 * pueden mover: al hacerlo se romperian enlaces que la ciudadania y otras
 * instituciones ya tienen guardados.
 *
 * Con este campo cada documento declara de donde sale su archivo:
 *
 *   local     -> subido al propio hosting, en el disco publico
 *   external  -> vive en el subdominio de documentos
 *
 * Los que ya existian se marcan como "local", que es lo que eran.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('lotaip_documents', function (Blueprint $t) {
            $t->string('source', 20)->default('local')->after('month_id');

            // file_path pasa a admitir tres formas segun el origen:
            //   local    -> ruta en el disco publico     ("lotaip/informe.pdf")
            //   external -> ruta bajo el subdominio      ("2026/01/informe.pdf")
            //   external -> o una URL absoluta completa  ("https://.../informe.pdf")
            // Se amplia porque una URL absoluta puede pasarse de 255.
            $t->text('file_path')->change();

            $t->index('source');
        });
    }

    public function down(): void
    {
        Schema::table('lotaip_documents', function (Blueprint $t) {
            $t->dropIndex(['source']);
            $t->dropColumn('source');
            $t->string('file_path')->change();
        });
    }
};
