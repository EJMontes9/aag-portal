<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lotaip_months', function (Blueprint $t) {
            $t->id();
            $t->foreignId('year_id')->constrained('lotaip_years')->cascadeOnDelete();
            $t->unsignedTinyInteger('month'); // 1-12
            // 'files' = listado de documentos subidos al sistema
            // 'redirect' = redirige a URL externa (ej. transparencia activa)
            $t->enum('mode', ['files', 'redirect'])->default('files');
            $t->string('redirect_url')->nullable();
            $t->string('redirect_label')->nullable();
            // Override de extensiones permitidas a nivel mes (json)
            // Si esta NULL hereda de lotaip_years.allowed_extensions
            $t->json('allowed_extensions')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();

            $t->unique(['year_id', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotaip_months');
    }
};
