<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('news', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('slug')->unique();
            $t->foreignId('category_id')->nullable()->constrained('news_categories')->nullOnDelete();
            $t->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $t->text('excerpt')->nullable();
            $t->longText('content');
            $t->string('cover_image')->nullable();
            $t->string('cover_image_alt')->nullable();
            $t->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $t->timestamp('published_at')->nullable();
            $t->boolean('featured_on_home')->default(false);
            $t->string('meta_title')->nullable();
            $t->text('meta_description')->nullable();
            $t->unsignedInteger('views_count')->default(0);
            $t->timestamps();

            $t->index(['status', 'published_at']);
            $t->index('featured_on_home');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
