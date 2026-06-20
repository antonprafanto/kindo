<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->unsignedInteger('read_time_minutes')->default(1)->after('views_count');
            $table->string('seo_title')->nullable()->after('read_time_minutes');
            $table->text('seo_description')->nullable()->after('seo_title');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('admin')->after('email_verified_at');
            $table->string('avatar')->nullable()->after('role');
            $table->text('bio')->nullable()->after('avatar');
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('articles', function (Blueprint $table) {
            $table->dropColumn(['read_time_minutes', 'seo_title', 'seo_description']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar', 'bio']);
            $table->dropSoftDeletes();
        });
    }
};
