<?php

namespace App\Http\Controllers;

use App\Models\Article;
use App\Models\User;
use App\Services\SitemapService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class DeployController extends Controller
{
    /**
     * Clear cached config/routes/views after FTP deploy (shared hosting, no SSH).
     */
    public function clearCache(): Response
    {
        $this->authorizeDeployHook();

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
    public function migrate(): Response
    {
        $this->authorizeDeployHook();

        Artisan::call('migrate', ['--force' => true]);

        return response(trim(Artisan::output()) ?: 'Migrated', 200);
    }

    /**
     * Cek kesehatan tabel admin (contributor + contact messages) tanpa SSH.
     */
    public function health(): JsonResponse
    {
        $this->authorizeDeployHook();

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
                ->get(['id', 'name', 'email', 'status', 'user_id', 'created_at', 'reviewed_at'])
                ->map(fn ($row) => [
                    ...((array) $row),
                    'email' => $this->maskEmail((string) $row->email),
                ])
            : [];

        $contributorStats = null;

        if (Schema::hasTable('contributor_applications')) {
            $contributorStats = [
                'pending'   => DB::table('contributor_applications')->where('status', 'pending')->count(),
                'approved'  => DB::table('contributor_applications')->where('status', 'approved')->count(),
                'rejected'  => DB::table('contributor_applications')->where('status', 'rejected')->count(),
                'approved_missing_user_id' => DB::table('contributor_applications')
                    ->where('status', 'approved')
                    ->whereNull('user_id')
                    ->count(),
            ];
        }

        $authorCount = Schema::hasTable('users')
            ? User::query()->where('role', 'author')->count()
            : null;

        return response()->json([
            'tables'                          => $tables,
            'contributor_stats'               => $contributorStats,
            'author_users'                    => $authorCount,
            'password_reset_expire_minutes'   => (int) config('auth.passwords.users.expire'),
            'recent_contributor_applications' => $recent,
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function publishArticle10(): Response
    {
        return $this->publishArticle('Article10Seeder', 'Article 10 published');
    }

    /**
     * Publish artikel ke-11 via seeder (shared hosting tanpa SSH).
     * Juga re-seed artikel #10 agar backlink deep sleep ke Seri 2 ikut terbarui.
     */
    public function publishArticle11(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article11Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 11 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        $published = Article::query()
            ->where('slug', 'deep-sleep-esp32-sensor-dht22-hemat-baterai')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 11 missing after Article11Seeder on deploy hook.'));

            return response('Article 11 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 11 published', 200);
    }

    /**
     * Publish artikel ke-12 via seeder (shared hosting tanpa SSH).
     * Juga re-seed artikel #11 agar backlink NVS/WiFiManager ikut terbarui.
     */
    public function publishArticle12(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article12Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 12 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article11Seeder',
            '--force' => true,
        ]);

        $published = Article::query()
            ->where('slug', 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 12 missing after Article12Seeder on deploy hook.'));

            return response('Article 12 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 12 published', 200);
    }

    /**
     * Publish artikel ke-16 via seeder (shared hosting tanpa SSH).
     * Juga re-seed artikel #12 dan #7 agar backlink Mosquitto ikut terbarui.
     */
    public function publishArticle16(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 16 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article12Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        $published = Article::query()
            ->where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 16 missing after Article16Seeder on deploy hook.'));

            return response('Article 16 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 16 published', 200);
    }

    /**
     * Publish artikel ke-13 via seeder (shared hosting tanpa SSH).
     * Juga re-seed #12, #16, #11 dan patch #5 agar backlink BME280 ikut terbarui.
     */
    public function publishArticle13(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article13Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 13 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article12Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article11Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle5Seri2Seeder',
            '--force' => true,
        ]);

        $published = Article::query()
            ->where('slug', 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 13 missing after Article13Seeder on deploy hook.'));

            return response('Article 13 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 13 published', 200);
    }

    public function publishArticle9(): Response
    {
        return $this->publishArticle('Article9Seeder', 'Article 9 published');
    }

    public function publishArticle8(): Response
    {
        return $this->publishArticle('Article8Seeder', 'Article 8 published');
    }

    public function publishArticle7(): Response
    {
        return $this->publishArticle('Article7Seeder', 'Article 7 published');
    }

    public function publishArticle6(): Response
    {
        return $this->publishArticle('Article6Seeder', 'Article 6 published');
    }

    /**
     * Buat atau perbaiki akun admin dari ADMIN_* di .env (tanpa SSH).
     */
    public function ensureAdmin(): JsonResponse
    {
        $this->authorizeDeployHook();

        Artisan::call('config:clear');

        $exitCode = Artisan::call('kindo:ensure-admin', ['--reset-password' => true]);

        if ($exitCode !== 0) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Admin setup failed. Check server logs.',
            ], HttpResponse::HTTP_INTERNAL_SERVER_ERROR, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        }

        return response()->json([
            'status'  => 'ok',
            'message' => 'Admin account ensured successfully.',
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    private function publishArticle(string $seederClass, string $successMessage): Response
    {
        $this->authorizeDeployHook();

        Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$seederClass}", '--force' => true]);

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response($successMessage, 200);
    }

    private function authorizeDeployHook(): void
    {
        $token = config('app.deploy_hook_token');
        $provided = request()->header('X-Deploy-Token') ?? request()->query('token', '');

        if (empty($token) || ! hash_equals($token, (string) $provided)) {
            abort(404);
        }
    }

    private function maskEmail(string $email): string
    {
        if (! str_contains($email, '@')) {
            return '***';
        }

        [$local, $domain] = explode('@', $email, 2);
        $visible = mb_substr($local, 0, 1);

        return $visible . '***@' . $domain;
    }
}
