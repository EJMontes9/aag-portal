<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->string('name');                         // nombre de display
            $table->string('file_name');                    // nombre real en disco
            $table->string('disk')->default('public');
            $table->string('path');                         // ruta en el disco (ej: media/2026/05/img.webp)
            $table->string('mime_type')->nullable();
            $table->string('extension', 20)->nullable();
            $table->unsignedBigInteger('size')->default(0); // bytes
            $table->unsignedInteger('width')->nullable();   // solo imágenes
            $table->unsignedInteger('height')->nullable();  // solo imágenes
            $table->string('alt_text')->nullable();
            $table->string('type', 20)->default('image');   // image | video | document | other
            $table->string('folder', 200)->nullable();      // carpeta/categoría
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
    }
};
