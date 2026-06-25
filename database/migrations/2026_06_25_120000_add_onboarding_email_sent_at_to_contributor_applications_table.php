<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contributor_applications', function (Blueprint $table) {
            $table->timestamp('onboarding_email_sent_at')->nullable()->after('reviewed_at');
        });
    }

    public function down(): void
    {
        Schema::table('contributor_applications', function (Blueprint $table) {
            $table->dropColumn('onboarding_email_sent_at');
        });
    }
};
