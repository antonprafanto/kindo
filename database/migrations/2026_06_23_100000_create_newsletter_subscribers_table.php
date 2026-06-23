<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('newsletter_subscribers', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('status')->default('pending'); // pending | active | unsubscribed
            $table->string('confirmation_token', 64)->nullable()->unique();
            $table->string('unsubscribe_token', 64)->nullable()->unique();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamp('unsubscribed_at')->nullable();
            $table->timestamps();

            $table->index('status');
        });

        Schema::create('article_newsletter_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('recipients_count')->default(0);
            $table->timestamp('sent_at');
            $table->unique('article_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('article_newsletter_logs');
        Schema::dropIfExists('newsletter_subscribers');
    }
};
