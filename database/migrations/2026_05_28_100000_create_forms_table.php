<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forms', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('success_message')->default('¡Gracias! Tu mensaje ha sido enviado correctamente. Nos pondremos en contacto pronto.');
            $table->string('submit_label')->default('Enviar mensaje');
            $table->json('notify_emails')->nullable(); // array de emails que reciben la notificación
            $table->boolean('store_submissions')->default(true); // guardar en BD
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forms');
    }
};
