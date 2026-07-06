<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('lotaip_documents', function (Blueprint $t) {
            $t->id();
            $t->foreignId('month_id')->constrained('lotaip_months')->cascadeOnDelete();
            $t->string('title');                         // Ej. "Literal b2 - Distributivo del personal"
            $t->string('literal', 10)->nullable();       // a, b1, b2, c, ... (opcional)
            $t->string('file_path');                     // ruta en disk public
            $t->string('extension', 10);                 // pdf, csv, etc
            $t->unsignedBigInteger('file_size')->nullable();
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();

            $t->index(['month_id', 'is_active', 'sort_order']);
            $t->index('literal');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotaip_documents');
    }
};
