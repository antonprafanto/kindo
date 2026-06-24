<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Support\Facades\Artisan;

class DeployController extends Controller
{
    /**
     * Clear cached config/routes/views after FTP deploy (shared hosting, no SSH).
     * Protected by DEPLOY_HOOK_TOKEN — returns 404 when token is missing or invalid.
     */
    public function clearCache()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('view:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Cache cleared', 200);
    }

    /**
     * Run pending migrations after deploy (shared hosting tanpa SSH).
     */
    public function migrate()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('migrate', ['--force' => true]);

        return response(trim(Artisan::output()) ?: 'Migrated', 200);
    }

    /**
     * Publish artikel ke-9 via seeder (shared hosting tanpa SSH).
     */
    public function publishArticle9()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article9Seeder', '--force' => true]);

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 9 published', 200);
    }

    /**
     * Publish artikel ke-8 via seeder (shared hosting tanpa SSH).
     */
    public function publishArticle8()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article8Seeder', '--force' => true]);

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 8 published', 200);
    }

    /**
     * Publish artikel ke-7 via seeder (shared hosting tanpa SSH).
     * Idempotent — aman dipanggil ulang.
     */
    public function publishArticle7()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 7 published', 200);
    }

    /**
     * Publish artikel ke-6 via seeder (shared hosting tanpa SSH).
     * Idempotent — aman dipanggil ulang.
     */
    public function publishArticle6()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article6Seeder', '--force' => true]);

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 6 published', 200);
    }
}
