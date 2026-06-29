<?php

namespace App\Http\Controllers;

use App\Services\SitemapService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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
     * Cek kesehatan tabel admin (contributor + contact messages) tanpa SSH.
     */
    public function health()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        $tables = [
            'contributor_applications' => null,
            'contact_messages'         => null,
        ];

        foreach (array_keys($tables) as $table) {
            $tables[$table] = Schema::hasTable($table) ? DB::table($table)->count() : 'missing';
        }

        $recent = Schema::hasTable('contributor_applications')
            ? DB::table('contributor_applications')
                ->orderByDesc('created_at')
                ->limit(10)
                ->get(['id', 'name', 'email', 'status', 'created_at'])
            : [];

        return response()->json([
            'tables'                      => $tables,
            'recent_contributor_applications' => $recent,
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    /**
     * Publish artikel ke-10 via seeder (shared hosting tanpa SSH).
     */
    public function publishArticle10()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 10 published', 200);
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

    /**
     * Buat atau perbaiki akun admin dari ADMIN_* di .env (tanpa SSH).
     */
    public function ensureAdmin()
    {
        $token = config('app.deploy_hook_token');

        if (empty($token) || ! hash_equals($token, (string) request()->query('token', ''))) {
            abort(404);
        }

        Artisan::call('config:clear');

        Artisan::call('kindo:ensure-admin', ['--reset-password' => true]);

        $output = trim(Artisan::output());

        return response()->json([
            'status'  => str_contains($output, 'created') || str_contains($output, 'updated') ? 'ok' : 'check_output',
            'message' => $output ?: 'No output',
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }
}
