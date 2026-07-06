<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('convocatorias', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('area')->nullable();
            $table->string('modality')->nullable();
            $table->text('short_description')->nullable();
            $table->longText('requirements')->nullable(); // JSON array
            $table->string('bases_pdf')->nullable();
            $table->dateTime('opens_at')->nullable();
            $table->dateTime('closes_at');
            $table->string('status')->default('vigente'); // vigente | cerrada | borrador
            $table->string('alert_mode')->default('none'); // none | modal | toast | banner
            $table->string('alert_frequency')->default('session'); // session | always | once
            $table->boolean('featured_on_home')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convocatorias');
    }
};
