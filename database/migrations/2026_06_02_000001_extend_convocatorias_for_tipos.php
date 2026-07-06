<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            // ── Tipo de convocatoria ──────────────────────────────────────────
            // 'proceso' = contratación con countdown + documentos (comportamiento actual)
            // 'aviso'   = anuncio simple, sin countdown
            $table->string('tipo')->default('proceso')->after('featured_on_home');

            // ── Campos para AVISO ─────────────────────────────────────────────
            $table->string('layout_type')->default('poster')->nullable()->after('tipo');
            // poster | banner | minimal
            $table->string('imagen')->nullable()->after('layout_type');
            $table->string('video_url')->nullable()->after('imagen');
            $table->boolean('show_logo')->default(true)->after('video_url');

            // ── Campos para PROCESO ───────────────────────────────────────────
            $table->json('cronograma')->nullable()->after('show_logo');
            // [{etapa, fecha, hora}]
            $table->string('enlace_referencia')->nullable()->after('cronograma');
            $table->json('documentos')->nullable()->after('enlace_referencia');
            // [{nombre, archivo}]

            // ── closes_at ahora es nullable (avisos no necesitan fecha de cierre)
            $table->dateTime('closes_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('convocatorias', function (Blueprint $table) {
            $table->dropColumn([
                'tipo', 'layout_type', 'imagen', 'video_url', 'show_logo',
                'cronograma', 'enlace_referencia', 'documentos',
            ]);
            $table->dateTime('closes_at')->nullable(false)->change();
        });
    }
};
