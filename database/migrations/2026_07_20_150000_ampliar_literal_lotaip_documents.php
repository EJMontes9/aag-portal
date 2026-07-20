<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Amplia el campo "literal" para poder usarlo como AGRUPADOR.
 *
 * En el subdominio los documentos no cuelgan sueltos del mes: se organizan en
 * carpetas por literal de la LOTAIP, con nombres largos como
 * "9. Listado de empresas y personas que han incumplido contratos".
 *
 * El campo estaba definido como string(10), pensado para guardar solo "a4" o
 * "b2". Se amplia a 255 para poder conservar el nombre completo de la carpeta
 * y agrupar los documentos igual que estan en el servidor, en vez de mostrar
 * setenta y cinco archivos sueltos por mes.
 */
return new class extends Migration {
    public function up(): void
    {
        Schema::table('lotaip_documents', function (Blueprint $t) {
            $t->string('literal', 255)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('lotaip_documents', function (Blueprint $t) {
            $t->string('literal', 10)->nullable()->change();
        });
    }
};
