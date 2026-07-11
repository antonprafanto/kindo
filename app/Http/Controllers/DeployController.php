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

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        return response(trim(Artisan::output()) ?: 'Migrated', 200);
    }

    /**
     * Recategorize artikel UI/UX kontributor setelah taxonomy UI/UX live (idempotent).
     */
    public function applyUiUxTaxonomy(): Response
    {
        $this->authorizeDeployHook();

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\RecategorizeAjiUxArticlesSeeder',
            '--force' => true,
        ]);

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('UI/UX taxonomy applied to contributor articles', 200);
    }

    public function verifyUiUxTaxonomy(): JsonResponse
    {
        $this->authorizeDeployHook();

        $expectedTags = \Database\Seeders\UiUxTaxonomy::tagSlugs();
        $categoryOk = Schema::hasTable('categories')
            && DB::table('categories')
                ->where('slug', 'ui-ux-desain')
                ->whereNull('deleted_at')
                ->exists();

        $tagCount = Schema::hasTable('tags')
            ? DB::table('tags')
                ->whereIn('slug', $expectedTags)
                ->whereNull('deleted_at')
                ->count()
            : 0;

        $ajiEmail = \App\Support\EmailNormalizer::normalize('caksaaji@gmail.com');
        $ajiUserId = Schema::hasTable('users')
            ? DB::table('users')->where('email', $ajiEmail)->value('id')
            : null;

        $ajiUntaggedWebDev = 0;
        if ($ajiUserId && Schema::hasTable('articles')) {
            $webCategoryId = DB::table('categories')->where('slug', 'web-development')->value('id');
            if ($webCategoryId) {
                $ajiUntaggedWebDev = DB::table('articles')
                    ->where('user_id', $ajiUserId)
                    ->where('category_id', $webCategoryId)
                    ->whereNull('deleted_at')
                    ->whereNotIn('id', function ($query) {
                        $query->select('article_id')->from('article_tag');
                    })
                    ->count();
            }
        }

        $ok = $categoryOk && $tagCount === count($expectedTags) && $ajiUntaggedWebDev === 0;

        return response()->json([
            'ok'                        => $ok,
            'ui_ux_category'            => $categoryOk,
            'ui_ux_tags'                => $tagCount,
            'expected_ui_ux_tags'       => count($expectedTags),
            'aji_untagged_web_dev_left' => $ajiUntaggedWebDev,
        ], $ok ? 200 : 500, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

    /**
     * Publish artikel ke-14 via seeder (shared hosting tanpa SSH).
     * Juga re-seed #13, #12, #16, #11, patch #5, dan indeks #10 (5 artikel Seri 2).
     */
    public function publishArticle14(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article14Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 14 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article13Seeder',
            '--force' => true,
        ]);

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

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        $published = Article::query()
            ->where('slug', 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 14 missing after Article14Seeder on deploy hook.'));

            return response('Article 14 seed incomplete', 500);
        }

        $this->runDuplicateBme280Cleanup();

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 14 published', 200);
    }

    /**
     * Hapus duplikat BME280 manual + 301 redirect slug lama (shared hosting).
     */
    public function cleanupDuplicateBme280(): Response
    {
        $this->authorizeDeployHook();

        $this->runDuplicateBme280Cleanup();

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Duplicate BME280 cleaned up', 200);
    }

    /**
     * Publish artikel ke-15 via seeder (shared hosting tanpa SSH).
     * Juga re-seed #10–#14, #12, #11, #16, cleanup duplikat BME280.
     */
    public function publishArticle15(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article15Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 15 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article14Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article13Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article12Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article11Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $published = Article::query()
            ->where('slug', 'ota-update-firmware-esp32-via-wifi')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 15 missing after Article15Seeder on deploy hook.'));

            return response('Article 15 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 15 published', 200);
    }

    /**
     * Publish artikel ke-21 via seeder (Home Assistant + ESP32 MQTT).
     * Juga re-seed #8, #9, #10, #15, #16 + cleanup duplikat BME280.
     */
    public function publishArticle21(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article21Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 21 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article15Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article9Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article8Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article6Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $published = Article::query()
            ->where('slug', 'home-assistant-integrasi-esp32-mqtt')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 21 missing after Article21Seeder on deploy hook.'));

            return response('Article 21 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 21 published', 200);
    }

    /**
     * Publish artikel ke-22 via seeder (ESPHome flash ESP32).
     * Juga re-seed #21, #16, #15, #10, #9, #8, #7, #6 + cleanup duplikat BME280.
     */
    public function publishArticle22(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article22Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 22 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article21Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article15Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article9Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article8Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article6Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $published = Article::query()
            ->where('slug', 'esphome-flash-esp32-tanpa-coding-arduino')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 22 missing after Article22Seeder on deploy hook.'));

            return response('Article 22 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 22 published', 200);
    }

    /**
     * Publish artikel ke-23 via seeder (Node-RED dashboard MQTT).
     * Juga re-seed #22, #21, #16, #15, #10, #9, #8, #7, #6 + cleanup duplikat BME280.
     */
    public function publishArticle23(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article23Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 23 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article22Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article21Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article15Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article9Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article8Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article6Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $published = Article::query()
            ->where('slug', 'node-red-dashboard-otomasi-iot-mqtt-esp32')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 23 missing after Article23Seeder on deploy hook.'));

            return response('Article 23 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 23 published', 200);
    }

    /**
     * Publish artikel ke-24 via seeder (PIR + lampu MQTT debounce).
     * Juga re-seed #23, #22, #21, #16, #10, #9, #8, #7 + cleanup duplikat BME280.
     */
    public function publishArticle24(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article24Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 24 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article23Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article22Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article21Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article9Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article8Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $published = Article::query()
            ->where('slug', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 24 missing after Article24Seeder on deploy hook.'));

            return response('Article 24 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 24 published', 200);
    }

    /**
     * Publish artikel ke-17 via seeder (MQTT TLS, QoS, LWT, retained).
     * Juga re-seed #24, #16, #12, #10, #7 + cleanup duplikat BME280.
     */
    public function publishArticle17(): Response
    {
        $this->authorizeDeployHook();

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article17Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 17 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article24Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article23Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article22Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article21Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article15Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article12Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article8Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $published = Article::query()
            ->where('slug', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32')
            ->where('status', 'published')
            ->whereNotNull('published_at')
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 17 missing after Article17Seeder on deploy hook.'));

            return response('Article 17 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 17 published', 200);
    }

    /**
     * Publish artikel ke-34 via seeder (NTP & timestamp MQTT).
     * Juga re-seed #17, #16, #11, #24, #10 + cleanup duplikat BME280.
     */
    public function publishArticle34(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article34Seeder::class)) {
            return response('Article34Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article34Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 34 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article17Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article24Seeder',
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
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 34 missing or not visible after Article34Seeder on deploy hook.'));

            return response('Article 34 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 34 published', 200);
    }

    public function publishArticle18(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article18Seeder::class)) {
            return response('Article18Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article18Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 18 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article34Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article17Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article13Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article14Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article23Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article24Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 18 missing or not visible after Article18Seeder on deploy hook.'));

            return response('Article 18 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 18 published', 200);
    }

    public function publishArticle19(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article19Seeder::class)) {
            return response('Article19Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article19Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 19 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article18Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article34Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article17Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article13Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article14Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article23Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article24Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article21Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 19 missing or not visible after Article19Seeder on deploy hook.'));

            return response('Article 19 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 19 published', 200);
    }

    public function publishArticle20(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article20Seeder::class)) {
            return response('Article20Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article20Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 20 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article19Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article18Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article6Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article17Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 20 missing or not visible after Article20Seeder on deploy hook.'));

            return response('Article 20 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 20 published', 200);
    }

    public function publishArticle25(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article25Seeder::class)) {
            return response('Article25Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article25Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 25 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article20Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article11Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 25 missing or not visible after Article25Seeder on deploy hook.'));

            return response('Article 25 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 25 published', 200);
    }

    public function publishArticle26(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article26Seeder::class)) {
            return response('Article26Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article26Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 26 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article25Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article20Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article11Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article7Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 26 missing or not visible after Article26Seeder on deploy hook.'));

            return response('Article 26 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 26 published', 200);
    }

    public function publishArticle27(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article27Seeder::class)) {
            return response('Article27Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article27Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 27 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article26Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article6Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article20Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 27 missing or not visible after Article27Seeder on deploy hook.'));

            return response('Article 27 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 27 published', 200);
    }

    public function publishArticle28(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article28Seeder::class)) {
            return response('Article28Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article28Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 28 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article27Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article26Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article19Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article16Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 28 missing or not visible after Article28Seeder on deploy hook.'));

            return response('Article 28 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 28 published', 200);
    }

    public function publishArticle29(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article29Seeder::class)) {
            return response('Article29Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article29Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 29 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article28Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle2PlatformioSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'migrasi-platformio-esp32-vscode-project-rapi';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 29 missing or not visible after Article29Seeder on deploy hook.'));

            return response('Article 29 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 29 published', 200);
    }

    public function publishArticle30(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article30Seeder::class)) {
            return response('Article30Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article30Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 30 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article29Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle4FirebaseSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'esp32-firebase-realtime-database-sensor-cloud';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 30 missing or not visible after Article30Seeder on deploy hook.'));

            return response('Article 30 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 30 published', 200);
    }

    public function publishArticle31(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article31Seeder::class)) {
            return response('Article31Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article31Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 31 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article30Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle9FreeRTOSSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'freertos-esp32-multi-task-sensor-wifi-mqtt';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 31 missing or not visible after Article31Seeder on deploy hook.'));

            return response('Article 31 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 31 published', 200);
    }

    public function publishArticle32(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article32Seeder::class)) {
            return response('Article32Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article32Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 32 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article31Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle1BluetoothSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'bluetooth-esp32-ble-kirim-data-sensor-smartphone';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 32 missing or not visible after Article32Seeder on deploy hook.'));

            return response('Article 32 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 32 published', 200);
    }

    public function publishArticle33(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article33Seeder::class)) {
            return response('Article33Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article33Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 33 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article32Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle8ServoSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 33 missing or not visible after Article33Seeder on deploy hook.'));

            return response('Article 33 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 33 published', 200);
    }

    public function publishArticle35(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article35Seeder::class)) {
            return response('Article35Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article35Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 35 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article33Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle5AdcSeeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle27LdrSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 35 missing or not visible after Article35Seeder on deploy hook.'));

            return response('Article 35 seed incomplete', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 35 published', 200);
    }

    private function runDuplicateBme280Cleanup(): void
    {
        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder',
            '--force' => true,
        ]);
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
