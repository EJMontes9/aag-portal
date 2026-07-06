<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('subscribers', function (Blueprint $t) {
            $t->id();
            $t->string('email')->unique();
            $t->string('name')->nullable();
            $t->enum('status', ['pending', 'confirmed', 'unsubscribed'])->default('pending');
            $t->string('confirmation_token', 64)->nullable()->unique();
            $t->string('source')->nullable()->comment('news_detail, sidebar, homepage, etc.');
            $t->timestamp('confirmed_at')->nullable();
            $t->timestamp('unsubscribed_at')->nullable();
            $t->string('ip_address', 45)->nullable();
            $t->string('user_agent', 500)->nullable();
            $t->timestamps();

            $t->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscribers');
    }
};
