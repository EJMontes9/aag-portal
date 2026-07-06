<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_fields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('form_id')->constrained()->cascadeOnDelete();
            $table->string('label');                         // "Nombre completo"
            $table->string('field_key');                     // "nombre_completo" (snake_case, único por form)
            $table->string('type')->default('text');         // text|email|tel|textarea|select|radio|checkbox|number|date
            $table->string('placeholder')->nullable();
            $table->text('help_text')->nullable();           // texto de ayuda bajo el campo
            $table->boolean('required')->default(false);
            $table->integer('min_length')->nullable();       // para text / textarea
            $table->integer('max_length')->nullable();
            $table->json('options')->nullable();             // [{label: "Opción A", value: "a"}, ...] para select/radio
            $table->integer('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_fields');
    }
};
