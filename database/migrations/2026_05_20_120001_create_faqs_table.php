<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('faqs', function (Blueprint $t) {
            $t->id();
            $t->string('question');
            $t->longText('answer');
            $t->foreignId('category_id')->nullable()->constrained('faq_categories')->nullOnDelete();
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->boolean('is_active')->default(true);
            $t->boolean('featured')->default(false);
            $t->timestamps();

            $t->index(['is_active', 'sort_order']);
            $t->index('featured');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};
