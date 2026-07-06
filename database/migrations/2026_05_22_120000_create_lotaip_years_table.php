<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lotaip_years', function (Blueprint $t) {
            $t->id();
            $t->enum('section', ['lotaip', 'rendicion'])->default('lotaip')->index();
            $t->unsignedSmallInteger('year');
            // Extensiones permitidas para mostrar archivos en este año (json)
            // Ej: ["pdf"] / ["csv"] / ["pdf","csv"]
            $t->json('allowed_extensions')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();

            $t->unique(['section', 'year']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotaip_years');
    }
};
