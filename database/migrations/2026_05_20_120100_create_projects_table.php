<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->text('summary')->nullable();
            $t->longText('description')->nullable();
            $t->json('gallery')->nullable();         // array de paths
            $t->string('cover_image')->nullable();
            $t->enum('status', ['planificado', 'en_curso', 'completado'])->default('planificado');
            $t->string('budget')->nullable();        // texto libre, ej "USD 2.5M"
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->string('location')->nullable();
            $t->json('milestones')->nullable();      // hitos: [{date, label, completed}]
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->boolean('is_published')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();

            $t->index(['is_published', 'status', 'sort_order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('projects');
    }
};
