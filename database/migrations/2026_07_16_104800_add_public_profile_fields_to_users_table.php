<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('slug')->nullable()->unique()->after('name');
            $table->string('expertise')->nullable()->after('bio');
            $table->string('github_url')->nullable()->after('expertise');
            $table->string('linkedin_url')->nullable()->after('github_url');
            $table->string('website_url')->nullable()->after('linkedin_url');
            $table->json('external_works')->nullable()->after('website_url');
        });

        $authors = DB::table('users')
            ->where('role', 'author')
            ->whereNull('slug')
            ->orderBy('id')
            ->get(['id', 'name']);

        $used = [];

        foreach ($authors as $author) {
            $base = Str::slug($author->name) ?: 'penulis-'.$author->id;
            $slug = $base;
            $i = 2;

            while (isset($used[$slug]) || DB::table('users')->where('slug', $slug)->exists()) {
                $slug = $base.'-'.$i;
                $i++;
            }

            $used[$slug] = true;

            DB::table('users')->where('id', $author->id)->update(['slug' => $slug]);
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['slug']);
            $table->dropColumn([
                'slug',
                'expertise',
                'github_url',
                'linkedin_url',
                'website_url',
                'external_works',
            ]);
        });
    }
};
