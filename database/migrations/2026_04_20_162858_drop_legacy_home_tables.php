<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('hero_cards');
        Schema::dropIfExists('quick_links');
        Schema::dropIfExists('institutional_values');
        Schema::dropIfExists('home_sections');
    }

    public function down(): void
    {
        // Migración irreversible: los datos viven ahora en page_blocks
    }
};
