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

        try {
            Artisan::call('migrate', ['--force' => true]);
        } catch (\Throwable $e) {
            report($e);

            return response('Migrate failed: '.$e->getMessage(), 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        $output = trim(Artisan::output()) ?: 'Migrated';

        return response($output, 200);
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
        $this->authorizeDeployHook();

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

    public function publishArticle36(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article36Seeder::class)) {
            return response('Article36Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article36Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 36 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle1Esp8266Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle35Esp8266Seeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 36 missing or not visible after Article36Seeder on deploy hook.'));

            return response('Article 36 seed incomplete', 500);
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

        return response('Article 36 published', 200);
    }

    public function publishArticle37(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article37Seeder::class)) {
            return response('Article37Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article37Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 37 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle36SdCardSeeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle27SdCardSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'sd-card-spi-esp32-logging-data-sensor-offline';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 37 missing or not visible after Article37Seeder on deploy hook.'));

            return response('Article 37 seed incomplete', 500);
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

        return response('Article 37 published', 200);
    }

    public function publishArticle38(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article38Seeder::class)) {
            return response('Article38Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article38Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 38 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle17HttpsSeeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle36HttpsSeeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle37HttpsSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'https-sertifikat-esp32-wificlientsecure-api-rest';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 38 missing or not visible after Article38Seeder on deploy hook.'));

            return response('Article 38 seed incomplete', 500);
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

        return response('Article 38 published', 200);
    }

    public function publishArticle39(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article39Seeder::class)) {
            return response('Article39Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article39Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 39 seed failed', 500);
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article10Seeder',
            '--force' => true,
        ]);

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle38GreenhouseSeeder',
            '--force' => true,
        ]);

        $this->runDuplicateBme280Cleanup();

        $slug = 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 39 missing or not visible after Article39Seeder on deploy hook.'));

            return response('Article 39 seed incomplete', 500);
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

        return response('Article 39 published', 200);
    }

    public function publishArticle40(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $seederPath = base_path('database/seeders/Article40Seeder.php');
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }

        if (! class_exists(\Database\Seeders\Article40Seeder::class)) {
            return response('Article40Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 40 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article40Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 40 seed failed', 500);
        }

        $slug = 'mengenal-oop-cara-berpikir-dengan-objek-python';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 40 missing or not visible after Article40Seeder on deploy hook.'));

            return response('Article 40 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, '720 340') || ! str_contains($body, 'color:#1a1a1a') || str_contains($body, 'stroke-dasharray')) {
            report(new \RuntimeException('Article 40 body missing expected visual fixes after seed.'));

            return response('Article 40 body visual fixes missing', 500);
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

        return response('Article 40 published', 200);
    }

    public function publishArticle41(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\Article41Seeder::class)) {
            return response('Article41Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 41 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article41Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 41 seed failed', 500);
        }

        $slug = 'class-dan-object-pertama-python';

        $published = Article::published()
            ->where('slug', $slug)
            ->exists();

        if (! $published) {
            report(new \RuntimeException('Article 41 missing or not visible after Article41Seeder on deploy hook.'));

            return response('Article 41 seed incomplete', 500);
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

        return response('Article 41 published', 200);
    }

    public function publishArticle42(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $seederPath = base_path('database/seeders/Article42Seeder.php');
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }

        if (! class_exists(\Database\Seeders\Article42Seeder::class)) {
            return response('Article42Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 42 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article42Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 42 seed failed', 500);
        }

        $slug = 'attribute-method-constructor-init-python';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 42 missing or not visible after Article42Seeder on deploy hook.'));

            return response('Article 42 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop42Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'def pinjam(self)')) {
            report(new \RuntimeException('Article 42 body missing expected content after seed.'));

            return response('Article 42 body content checks failed', 500);
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

        return response('Article 42 published', 200);
    }

    public function publishArticle43(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $seederPath = base_path('database/seeders/Article43Seeder.php');
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }

        if (! class_exists(\Database\Seeders\Article43Seeder::class)) {
            return response('Article43Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 43 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article43Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 43 seed failed', 500);
        }

        $slug = 'encapsulation-property-python-oop';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 43 missing or not visible after Article43Seeder on deploy hook.'));

            return response('Article 43 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop43Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, '@property')) {
            report(new \RuntimeException('Article 43 body missing expected content after seed.'));

            return response('Article 43 body content checks failed', 500);
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

        return response('Article 43 published', 200);
    }

    public function publishArticle44(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $seederPath = base_path('database/seeders/Article44Seeder.php');
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }

        if (! class_exists(\Database\Seeders\Article44Seeder::class)) {
            return response('Article44Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 44 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article44Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 44 seed failed', 500);
        }

        $slug = 'inheritance-pewarisan-class-python';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 44 missing or not visible after Article44Seeder on deploy hook.'));

            return response('Article 44 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop44Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'super().__init__') || ! str_contains($body, 'EbookSalah') || ! str_contains($body, 'Audiobook') || ! str_contains($body, 'menggantikan')) {
            report(new \RuntimeException('Article 44 body missing expected content after seed.'));

            return response('Article 44 body content checks failed', 500);
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

        return response('Article 44 published', 200);
    }

    public function publishArticle45(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $seederPath = base_path('database/seeders/Article45Seeder.php');
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }

        if (! class_exists(\Database\Seeders\Article45Seeder::class)) {
            return response('Article45Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 45 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article45Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 45 seed failed', 500);
        }

        $slug = 'polymorphism-python-oop';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 45 missing or not visible after Article45Seeder on deploy hook.'));

            return response('Article 45 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop45Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'KatalogEntry') || ! str_contains($body, 'cetak_benar') || ! str_contains($body, 'for item in koleksi') || ! str_contains($body, 'cek tipe anak') || ! str_contains($body, 'tipe object yang sebenarnya')) {
            report(new \RuntimeException('Article 45 body missing expected content after seed.'));

            return response('Article 45 body content checks failed', 500);
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

        return response('Article 45 published', 200);
    }

    public function publishArticle46(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $seederPath = base_path('database/seeders/Article46Seeder.php');
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }

        if (! class_exists(\Database\Seeders\Article46Seeder::class)) {
            return response('Article46Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 46 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article46Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 46 seed failed', 500);
        }

        $slug = 'abstraction-abc-python-oop';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 46 missing or not visible after Article46Seeder on deploy hook.'));

            return response('Article 46 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop46Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'class Pinjaman') || ! str_contains($body, 'BukuFisik') || ! str_contains($body, 'EbookLisensi') || ! str_contains($body, 'BukuBelumSiap') || ! str_contains($body, 'EntriDuck') || ! str_contains($body, 'kontrak_pinjaman.py') || ! str_contains($body, 'abstractmethod')) {
            report(new \RuntimeException('Article 46 body missing expected content after seed.'));

            return response('Article 46 body content checks failed', 500);
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

        return response('Article 46 published', 200);
    }

    public function publishArticle47(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $seederPath = base_path('database/seeders/Article47Seeder.php');
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }

        if (! class_exists(\Database\Seeders\Article47Seeder::class)) {
            return response('Article47Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 47 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article47Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 47 seed failed', 500);
        }

        $slug = 'composition-vs-inheritance-python';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 47 missing or not visible after Article47Seeder on deploy hook.'));

            return response('Article 47 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop47Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'PerpustakaanSalah') || ! str_contains($body, 'self.koleksi') || ! str_contains($body, 'KatalogSalah') || ! str_contains($body, 'perpustakaan_komposisi.py') || ! str_contains($body, 'class Perpustakaan')) {
            report(new \RuntimeException('Article 47 body missing expected content after seed.'));

            return response('Article 47 body content checks failed', 500);
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

        return response('Article 47 published', 200);
    }

    public function publishArticle48(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        $seederPath = base_path('database/seeders/Article48Seeder.php');
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }

        if (! class_exists(\Database\Seeders\Article48Seeder::class)) {
            return response('Article48Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 48 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article48Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 48 seed failed', 500);
        }

        $slug = 'special-methods-dataclass-python';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 48 missing or not visible after Article48Seeder on deploy hook.'));

            return response('Article 48 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop48Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, '__str__') || ! str_contains($body, '__repr__') || ! str_contains($body, '__eq__') || ! str_contains($body, 'dataclass') || ! str_contains($body, 'buku_special_methods.py')) {
            report(new \RuntimeException('Article 48 body missing expected content after seed.'));

            return response('Article 48 body content checks failed', 500);
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

        return response('Article 48 published', 200);
    }

    /**
     * Publish artikel ke-49 via seeder (shared hosting tanpa SSH).
     * Juga re-seed #48 + #40 agar backlink/indeks Capstone ikut terbarui.
     */
    public function publishArticle49(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article49Seeder.php',
            'database/seeders/Article48Seeder.php',
            'database/seeders/Article40Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article49Seeder::class)) {
            return response('Article49Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 49 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article49Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 49 seed failed', 500);
        }

        // Backlink Capstone: teaser #48 + indeks #40
        foreach ([
            'Database\\Seeders\\Article48Seeder' => 'Article 49 backlink #48 seed failed',
            'Database\\Seeders\\Article40Seeder' => 'Article 49 backlink #40 seed failed',
        ] as $class => $failMsg) {
            if (! class_exists($class)) {
                return response($failMsg.' (class missing)', 500);
            }
            $backExit = Artisan::call('db:seed', [
                '--class' => $class,
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response($failMsg, 500);
            }
        }

        $slug = 'capstone-sistem-perpustakaan-mini-oop-python';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 49 missing or not visible after Article49Seeder on deploy hook.'));

            return response('Article 49 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop49Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'perpustakaan_mini.py') || ! str_contains($body, 'dataclass') || ! str_contains($body, 'class Perpustakaan') || ! str_contains($body, 'demo(') || ! str_contains($body, '__str__') || ! str_contains($body, '10/10')) {
            report(new \RuntimeException('Article 49 body missing expected content after seed.'));

            return response('Article 49 body content checks failed', 500);
        }

        $capstoneSlug = 'capstone-sistem-perpustakaan-mini-oop-python';
        $a48 = Article::published()->where('slug', 'special-methods-dataclass-python')->first();
        if (! $a48 || ! str_contains((string) $a48->body, $capstoneSlug)) {
            report(new \RuntimeException('Article 49 backlink missing on #48 after reseed.'));

            return response('Article 49 backlink #48 incomplete', 500);
        }
        $a40 = Article::published()->where('slug', 'mengenal-oop-cara-berpikir-dengan-objek-python')->first();
        if (! $a40 || ! str_contains((string) $a40->body, $capstoneSlug)) {
            report(new \RuntimeException('Article 49 backlink missing on #40 after reseed.'));

            return response('Article 49 backlink #40 incomplete', 500);
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

        return response('Article 49 published', 200);
    }

    /**
     * Publish artikel ke-50 via seeder (shared hosting tanpa SSH).
     * Juga re-seed #49 agar teaser Tier 2 / backlink Factory ikut terbarui saat di-ship.
     */
    public function publishArticle50(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article50Seeder.php',
            'database/seeders/Article49Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article50Seeder::class)) {
            return response('Article50Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 50 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article50Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 50 seed failed', 500);
        }

        // Backlink Tier 2: teaser #49 → #50 (setelah Article49Seeder memuat hardlink)
        if (class_exists(\Database\Seeders\Article49Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article49Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 50 backlink #49 seed failed', 500);
            }
        }

        $slug = 'design-pattern-factory-strategy-python';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 50 missing or not visible after Article50Seeder on deploy hook.'));

            return response('Article 50 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop50Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'factory_strategy_perpustakaan.py') || ! str_contains($body, 'buat_item') || ! str_contains($body, 'DendaFlat') || ! str_contains($body, 'DendaPerHari') || ! str_contains($body, 'StrategiDenda') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Tier 2') || ! str_contains($body, 'lib.items') || ! str_contains($body, 'encapsulation-property-python-oop')) {
            report(new \RuntimeException('Article 50 body missing expected content after seed.'));

            return response('Article 50 body content checks failed', 500);
        }

        $a49 = Article::published()->where('slug', 'capstone-sistem-perpustakaan-mini-oop-python')->first();
        if (! $a49 || ! str_contains((string) $a49->body, $slug)) {
            report(new \RuntimeException('Article 50 backlink missing on #49 after reseed.'));

            return response('Article 50 backlink #49 incomplete', 500);
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

        return response('Article 50 published', 200);
    }

    /**
     * Publish artikel ke-51 via seeder (shared hosting tanpa SSH).
     * Juga re-seed #50, #49, dan #40 agar teaser/backlink MicroPython ikut terbarui saat di-ship.
     */
    public function publishArticle51(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article51Seeder.php',
            'database/seeders/Article50Seeder.php',
            'database/seeders/Article49Seeder.php',
            'database/seeders/Article40Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article51Seeder::class)) {
            return response('Article51Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 51 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article51Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 51 seed failed', 500);
        }

        if (class_exists(\Database\Seeders\Article50Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article50Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 51 backlink #50 seed failed', 500);
            }
        }

        if (class_exists(\Database\Seeders\Article49Seeder::class)) {
            $capExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article49Seeder',
                '--force' => true,
            ]);
            if ($capExit !== 0) {
                return response('Article 51 backlink #49 seed failed', 500);
            }
        }

        if (class_exists(\Database\Seeders\Article40Seeder::class)) {
            $idxExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article40Seeder',
                '--force' => true,
            ]);
            if ($idxExit !== 0) {
                return response('Article 51 backlink #40 seed failed', 500);
            }
        }

        $slug = 'oop-micropython-esp32-class-sensor';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 51 missing or not visible after Article51Seeder on deploy hook.'));

            return response('Article 51 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop51Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'node_micropython_oop.py') || ! str_contains($body, 'FakePin') || ! str_contains($body, 'class Node') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Tier 2') || ! str_contains($body, 'MicroPython') || ! str_contains($body, 'label(suhu)') || ! str_contains($body, 'from machine import Pin')) {
            report(new \RuntimeException('Article 51 body missing expected content after seed.'));

            return response('Article 51 body content checks failed', 500);
        }

        $a50 = Article::published()->where('slug', 'design-pattern-factory-strategy-python')->first();
        if (! $a50 || ! str_contains((string) $a50->body, $slug)) {
            report(new \RuntimeException('Article 51 backlink missing on #50 after reseed.'));

            return response('Article 51 backlink #50 incomplete', 500);
        }

        $a49 = Article::published()->where('slug', 'capstone-sistem-perpustakaan-mini-oop-python')->first();
        if (! $a49 || ! str_contains((string) $a49->body, $slug)) {
            report(new \RuntimeException('Article 51 backlink missing on #49 after reseed.'));

            return response('Article 51 backlink #49 incomplete', 500);
        }

        $a40 = Article::published()->where('slug', 'mengenal-oop-cara-berpikir-dengan-objek-python')->first();
        if (! $a40 || ! str_contains((string) $a40->body, $slug)) {
            report(new \RuntimeException('Article 51 backlink missing on #40 after reseed.'));

            return response('Article 51 backlink #40 incomplete', 500);
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

        return response('Article 51 published', 200);
    }

    /**
     * Publish artikel ke-52 via seeder (shared hosting tanpa SSH).
     * Juga re-seed #51, #50, dan #49 agar teaser/backlink Flask/FastAPI ikut terbarui saat di-ship.
     */
    public function publishArticle52(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article52Seeder.php',
            'database/seeders/Article51Seeder.php',
            'database/seeders/Article50Seeder.php',
            'database/seeders/Article49Seeder.php',
            'database/seeders/Article40Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article52Seeder::class)) {
            return response('Article52Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 52 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article52Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 52 seed failed', 500);
        }

        foreach ([
            'Database\\Seeders\\Article51Seeder' => 'Article 52 backlink #51 seed failed',
            'Database\\Seeders\\Article50Seeder' => 'Article 52 backlink #50 seed failed',
            'Database\\Seeders\\Article49Seeder' => 'Article 52 backlink #49 seed failed',
            'Database\\Seeders\\Article40Seeder' => 'Article 52 backlink #40 seed failed',
        ] as $class => $failMsg) {
            if (class_exists($class)) {
                $backExit = Artisan::call('db:seed', [
                    '--class' => $class,
                    '--force' => true,
                ]);
                if ($backExit !== 0) {
                    return response($failMsg, 500);
                }
            }
        }

        $slug = 'oop-flask-fastapi-class-api';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 52 missing or not visible after Article52Seeder on deploy hook.'));

            return response('Article 52 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop52Arrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'perpustakaan_api_oop.py') || ! str_contains($body, 'PerpustakaanService') || ! str_contains($body, 'HttpResponse') || ! str_contains($body, 'AppShell') || ! str_contains($body, 'handle_create') || ! str_contains($body, 'JSONResponse') || ! str_contains($body, 'inheritance-pewarisan-class-python') || ! str_contains($body, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt') || ! str_contains($body, 'Status selalu 200') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Tier 2') || ! str_contains($body, 'Flask') || ! str_contains($body, 'FastAPI')) {
            report(new \RuntimeException('Article 52 body missing expected content after seed.'));

            return response('Article 52 body content checks failed', 500);
        }

        $a51 = Article::published()->where('slug', 'oop-micropython-esp32-class-sensor')->first();
        if (! $a51 || ! str_contains((string) $a51->body, $slug)) {
            report(new \RuntimeException('Article 52 backlink missing on #51 after reseed.'));

            return response('Article 52 backlink #51 incomplete', 500);
        }

        $a50 = Article::published()->where('slug', 'design-pattern-factory-strategy-python')->first();
        if (! $a50 || ! str_contains((string) $a50->body, $slug)) {
            report(new \RuntimeException('Article 52 backlink missing on #50 after reseed.'));

            return response('Article 52 backlink #50 incomplete', 500);
        }

        $a49 = Article::published()->where('slug', 'capstone-sistem-perpustakaan-mini-oop-python')->first();
        if (! $a49 || ! str_contains((string) $a49->body, $slug)) {
            report(new \RuntimeException('Article 52 backlink missing on #49 after reseed.'));

            return response('Article 52 backlink #49 incomplete', 500);
        }

        $a40 = Article::published()->where('slug', 'mengenal-oop-cara-berpikir-dengan-objek-python')->first();
        if (! $a40 || ! str_contains((string) $a40->body, $slug)) {
            report(new \RuntimeException('Article 52 backlink missing on #40 after reseed.'));

            return response('Article 52 backlink #40 incomplete', 500);
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

        return response('Article 52 published', 200);
    }

    /**
     * Publish artikel #53 OOP PHP (slug baru) + pastikan slug Flask-era tetap unpublished.
     * Re-seed #52 untuk hardlink teaser.
     */
    public function publishArticle53(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article53Seeder.php',
            'database/seeders/Article52Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article53Seeder::class)) {
            return response('Article53Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 53 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article53Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 53 seed failed', 500);
        }

        if (class_exists(\Database\Seeders\Article52Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article52Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 53 backlink #52 seed failed', 500);
            }
        }

        $slug = 'mengenal-oop-cara-berpikir-dengan-objek-php';
        $oldSlug = 'http-rest-kontrak-stub-flask-oop';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 53 missing or not visible after Article53Seeder on deploy hook.'));

            return response('Article 53 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop53phpArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'oop_php_dasar.php') || ! str_contains($body, 'class Buku') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#53 (ini)') || ! str_contains($body, '8/8 Capstone Laravel selesai') || ! str_contains($body, 'type hint') || ! str_contains($body, 'oop-php-property-method-constructor')) {
            report(new \RuntimeException('Article 53 body missing expected content after seed.'));

            return response('Article 53 body content checks failed', 500);
        }

        if (Article::published()->where('slug', $oldSlug)->exists()) {
            report(new \RuntimeException('Old Article 53 Flask-era slug still published.'));

            return response('Article 53 old slug still published', 500);
        }

        $a52 = Article::published()->where('slug', 'oop-flask-fastapi-class-api')->first();
        if (! $a52 || ! str_contains((string) $a52->body, $slug)) {
            report(new \RuntimeException('Article 53 backlink missing on #52 after reseed.'));

            return response('Article 53 backlink #52 incomplete', 500);
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

        return response('Article 53 published', 200);
    }

    public function publishArticle54(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article54Seeder.php',
            'database/seeders/Article53Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article54Seeder::class)) {
            return response('Article54Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 54 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article54Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 54 seed failed', 500);
        }

        if (class_exists(\Database\Seeders\Article53Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article53Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 54 backlink #53 seed failed', 500);
            }
        }

        $slug = 'oop-php-property-method-constructor';
        $prevSlug = 'mengenal-oop-cara-berpikir-dengan-objek-php';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 54 missing or not visible after Article54Seeder on deploy hook.'));

            return response('Article 54 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop54phpArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'oop_php_property.php') || ! str_contains($body, 'class Buku') || ! str_contains($body, '__construct') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#54 (ini)') || ! str_contains($body, '8/8 Capstone Laravel selesai') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'oop-php-visibility-composition')) {
            report(new \RuntimeException('Article 54 body missing expected content after seed.'));

            return response('Article 54 body content checks failed', 500);
        }

        $a53 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a53 || ! str_contains((string) $a53->body, $slug)) {
            report(new \RuntimeException('Article 54 backlink missing on #53 after reseed.'));

            return response('Article 54 backlink #53 incomplete', 500);
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

        return response('Article 54 published', 200);
    }

    public function publishArticle55(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article55Seeder.php',
            'database/seeders/Article54Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article55Seeder::class)) {
            return response('Article55Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 55 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article55Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 55 seed failed', 500);
        }

        $slug = 'oop-php-visibility-composition';
        $prevSlug = 'oop-php-property-method-constructor';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 55 missing or not visible after Article55Seeder on deploy hook.'));

            return response('Article 55 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'oop55phpArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'oop_php_visibility.php') || ! str_contains($body, 'class Buku') || ! str_contains($body, 'class Katalog') || ! str_contains($body, 'private') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#55 (ini)') || ! str_contains($body, '3/3 selesai') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'laravel-instalasi-proyek-pertama')) {
            report(new \RuntimeException('Article 55 body missing expected content after seed.'));

            return response('Article 55 body content checks failed', 500);
        }

        $a54 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a54) {
            report(new \RuntimeException('Article 54 missing while publishing #55.'));

            return response('Article 55 prerequisite #54 missing', 500);
        }

        if (class_exists(\Database\Seeders\Article54Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article54Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 55 backlink #54 seed failed', 500);
            }
        }

        $a54 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a54 || ! str_contains((string) $a54->body, $slug)) {
            report(new \RuntimeException('Article 55 backlink missing on #54 after reseed.'));

            return response('Article 55 backlink #54 incomplete', 500);
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

        return response('Article 55 published', 200);
    }

    public function publishArticle56(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article56Seeder.php',
            'database/seeders/Article55Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article56Seeder::class)) {
            return response('Article56Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 56 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article56Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 56 seed failed', 500);
        }

        $slug = 'laravel-instalasi-proyek-pertama';
        $prevSlug = 'oop-php-visibility-composition';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 56 missing or not visible after Article56Seeder on deploy hook.'));

            return response('Article 56 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel56installArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_instalasi_proyek_pertama_demo.php') || ! str_contains($body, 'create-project') || ! str_contains($body, 'composer create-project') || ! str_contains($body, 'artisan serve') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#56 (ini)') || ! str_contains($body, '1/8') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Composer') || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Laravel 13+')) {
            report(new \RuntimeException('Article 56 body missing expected content after seed.'));

            return response('Article 56 body content checks failed', 500);
        }

        $a55 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a55) {
            report(new \RuntimeException('Article 55 missing while publishing #56.'));

            return response('Article 56 prerequisite #55 missing', 500);
        }

        if (class_exists(\Database\Seeders\Article55Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article55Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 56 backlink #55 seed failed', 500);
            }
        }

        $a55 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a55 || ! str_contains((string) $a55->body, $slug)) {
            report(new \RuntimeException('Article 56 backlink missing on #55 after reseed.'));

            return response('Article 56 backlink #55 incomplete', 500);
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

        return response('Article 56 published', 200);
    }

    public function publishArticle57(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article57Seeder.php',
            'database/seeders/Article56Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article57Seeder::class)) {
            return response('Article57Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 57 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article57Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 57 seed failed', 500);
        }

        $slug = 'laravel-struktur-env-artisan';
        $prevSlug = 'laravel-instalasi-proyek-pertama';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 57 missing or not visible after Article57Seeder on deploy hook.'));

            return response('Article 57 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel57structArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_struktur_env_artisan_demo.php') || ! str_contains($body, 'key:generate') || ! str_contains($body, 'DB_CONNECTION') || ! str_contains($body, 'sqlite') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#57 (ini)') || ! str_contains($body, '2/8') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Laravel 13+') || ! str_contains($body, 'Artisan')) {
            report(new \RuntimeException('Article 57 body missing expected content after seed.'));

            return response('Article 57 body content checks failed', 500);
        }

        $a56 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a56) {
            report(new \RuntimeException('Article 56 missing while publishing #57.'));

            return response('Article 57 prerequisite #56 missing', 500);
        }

        if (class_exists(\Database\Seeders\Article56Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article56Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 57 backlink #56 seed failed', 500);
            }
        }

        $a56 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a56 || ! str_contains((string) $a56->body, $slug)) {
            report(new \RuntimeException('Article 57 backlink missing on #56 after reseed.'));

            return response('Article 57 backlink #56 incomplete', 500);
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

        return response('Article 57 published', 200);
    }

    public function publishArticle58(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article58Seeder.php',
            'database/seeders/Article57Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article58Seeder::class)) {
            return response('Article58Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 58 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article58Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 58 seed failed', 500);
        }

        $slug = 'laravel-routing-json-perpustakaan-api';
        $prevSlug = 'laravel-struktur-env-artisan';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 58 missing or not visible after Article58Seeder on deploy hook.'));

            return response('Article 58 seed incomplete', 500);
        }

        $body = (string) $article->body;
        $hasJsonHelper = str_contains($body, 'response()->json') || str_contains($body, 'response()-&gt;json');
        if (! str_contains($body, 'laravel58routeArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_routing_json_perpustakaan_demo.php') || ! $hasJsonHelper || ! str_contains($body, '/api/buku') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#58 (ini)') || ! str_contains($body, '3/8') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Laravel 13+') || ! str_contains($body, 'JSON')) {
            report(new \RuntimeException('Article 58 body missing expected content after seed.'));

            return response('Article 58 body content checks failed', 500);
        }

        $a57 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a57) {
            report(new \RuntimeException('Article 57 missing while publishing #58.'));

            return response('Article 58 prerequisite #57 missing', 500);
        }

        if (class_exists(\Database\Seeders\Article57Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article57Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 58 backlink #57 seed failed', 500);
            }
        }

        $a57 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a57 || ! str_contains((string) $a57->body, $slug)) {
            report(new \RuntimeException('Article 58 backlink missing on #57 after reseed.'));

            return response('Article 58 backlink #57 incomplete', 500);
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

        return response('Article 58 published', 200);
    }

    public function publishArticle59(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article59Seeder.php',
            'database/seeders/Article58Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article59Seeder::class)) {
            return response('Article59Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 59 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article59Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 59 seed failed', 500);
        }

        $slug = 'laravel-request-validasi-api';
        $prevSlug = 'laravel-routing-json-perpustakaan-api';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 59 missing or not visible after Article59Seeder on deploy hook.'));

            return response('Article 59 seed incomplete', 500);
        }

        $body = (string) $article->body;
        $hasValidate = str_contains($body, 'validate') || str_contains($body, 'validated');
        if (! str_contains($body, 'laravel59reqArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_request_validasi_api_demo.php') || ! $hasValidate || ! str_contains($body, 'StoreBukuRequest') || ! str_contains($body, '422') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#59 (ini)') || ! str_contains($body, '4/8') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Laravel 13+') || ! str_contains($body, 'Form Request')) {
            report(new \RuntimeException('Article 59 body missing expected content after seed.'));

            return response('Article 59 body content checks failed', 500);
        }

        $a58 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a58) {
            report(new \RuntimeException('Article 58 missing while publishing #59.'));

            return response('Article 59 prerequisite #58 missing', 500);
        }

        if (class_exists(\Database\Seeders\Article58Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article58Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 59 backlink #58 seed failed', 500);
            }
        }

        $a58 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a58 || ! str_contains((string) $a58->body, $slug)) {
            report(new \RuntimeException('Article 59 backlink missing on #58 after reseed.'));

            return response('Article 59 backlink #58 incomplete', 500);
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

        return response('Article 59 published', 200);
    }

    public function publishArticle60(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article60Seeder.php',
            'database/seeders/Article59Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article60Seeder::class)) {
            return response('Article60Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 60 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article60Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 60 seed failed', 500);
        }

        $slug = 'laravel-controller-service-eloquent';
        $prevSlug = 'laravel-request-validasi-api';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 60 missing or not visible after Article60Seeder on deploy hook.'));

            return response('Article 60 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel60cseArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_controller_service_eloquent_demo.php') || ! str_contains($body, 'BukuController') || ! str_contains($body, 'BukuService') || ! str_contains($body, 'Eloquent') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#60 (ini)') || ! str_contains($body, '5/8') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Laravel 13+') || ! str_contains($body, 'Alat yang dipakai') || ! str_contains($body, 'terminal kedua')) {
            report(new \RuntimeException('Article 60 body missing expected content after seed.'));

            return response('Article 60 body content checks failed', 500);
        }

        $a59 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a59) {
            report(new \RuntimeException('Article 59 missing while publishing #60.'));

            return response('Article 60 prerequisite #59 missing', 500);
        }

        if (class_exists(\Database\Seeders\Article59Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article59Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 60 backlink #59 seed failed', 500);
            }
        }

        $a59 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a59 || ! str_contains((string) $a59->body, $slug)) {
            report(new \RuntimeException('Article 60 backlink missing on #59 after reseed.'));

            return response('Article 60 backlink #59 incomplete', 500);
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

        return response('Article 60 published', 200);
    }

    public function publishArticle61(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article61Seeder.php',
            'database/seeders/Article60Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article61Seeder::class)) {
            return response('Article61Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 61 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article61Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 61 seed failed', 500);
        }

        $slug = 'laravel-auth-api-dasar';
        $prevSlug = 'laravel-controller-service-eloquent';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 61 missing or not visible after Article61Seeder on deploy hook.'));

            return response('Article 61 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel61authArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_auth_api_dasar_demo.php') || ! str_contains($body, 'AuthController') || ! str_contains($body, 'Sanctum') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#61 (ini)') || ! str_contains($body, '6/8') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Laravel 13+') || ! str_contains($body, 'Alat yang dipakai') || ! str_contains($body, 'terminal kedua') || ! str_contains($body, 'curl.exe') || ! str_contains($body, 'Belum diizinkan')) {
            report(new \RuntimeException('Article 61 body missing expected content after seed.'));

            return response('Article 61 body content checks failed', 500);
        }

        $a60 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a60) {
            report(new \RuntimeException('Article 60 missing while publishing #61.'));

            return response('Article 61 prerequisite #60 missing', 500);
        }

        if (class_exists(\Database\Seeders\Article60Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article60Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 61 backlink #60 seed failed', 500);
            }
        }

        $a60 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a60 || ! str_contains((string) $a60->body, $slug)) {
            report(new \RuntimeException('Article 61 backlink missing on #60 after reseed.'));

            return response('Article 61 backlink #60 incomplete', 500);
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

        return response('Article 61 published', 200);
    }

    public function publishArticle62(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('set_time_limit')) {
            @set_time_limit(150);
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article62Seeder.php',
            'database/seeders/Article61Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article62Seeder::class)) {
            return response('Article62Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 62 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article62Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 62 seed failed', 500);
        }

        $slug = 'capstone-api-perpustakaan-laravel';
        $prevSlug = 'laravel-auth-api-dasar';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 62 missing or not visible after Article62Seeder on deploy hook.'));

            return response('Article 62 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel62capstoneArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_capstone_api_perpustakaan_demo.php') || ! str_contains($body, 'BukuController') || ! str_contains($body, 'auth:sanctum') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#62 (ini)') || ! str_contains($body, '7/8') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Laravel 13+') || ! str_contains($body, 'Alat yang dipakai') || ! str_contains($body, 'terminal kedua') || ! str_contains($body, 'curl.exe') || ! str_contains($body, 'Belum diizinkan') || ! str_contains($body, 'Capstone')) {
            report(new \RuntimeException('Article 62 body missing expected content after seed.'));

            return response('Article 62 body content checks failed', 500);
        }

        $a61 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a61) {
            report(new \RuntimeException('Article 61 missing while publishing #62.'));

            return response('Article 62 prerequisite #61 missing', 500);
        }

        if (class_exists(\Database\Seeders\Article61Seeder::class)) {
            $backExit = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article61Seeder',
                '--force' => true,
            ]);
            if ($backExit !== 0) {
                return response('Article 62 backlink #61 seed failed', 500);
            }
        }

        $a61 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a61 || ! str_contains((string) $a61->body, $slug)) {
            report(new \RuntimeException('Article 62 backlink missing on #61 after reseed.'));

            return response('Article 62 backlink #61 incomplete', 500);
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

        return response('Article 62 published', 200);
    }

    public function publishArticle63(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('set_time_limit')) {
            @set_time_limit(150);
        }

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article63Seeder.php',
            'database/seeders/Article62Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article63Seeder::class)) {
            return response('Article63Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 63 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article63Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 63 seed failed', 500);
        }

        $slug = 'laravel-crud-api-buku-ubah-hapus';
        $prevSlug = 'capstone-api-perpustakaan-laravel';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 63 missing or not visible after Article63Seeder on deploy hook.'));

            return response('Article 63 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel63crudArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_crud_api_buku_ubah_hapus_demo.php') || ! str_contains($body, 'BukuController') || ! str_contains($body, 'auth:sanctum') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#63 (ini)') || ! str_contains($body, '8/8') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Laravel 13+') || ! str_contains($body, 'Alat yang dipakai') || ! str_contains($body, 'terminal kedua') || ! str_contains($body, 'curl.exe') || ! str_contains($body, 'Buku tidak ditemukan') || ! str_contains($body, 'destroy')) {
            report(new \RuntimeException('Article 63 body missing expected content after seed.'));

            return response('Article 63 body content checks failed', 500);
        }

        $bodyEn = (string) $article->body_en;
        if (! filled($article->title_en) || ! filled($bodyEn) || ! str_contains($bodyEn, 'laravel63crudArrow') || ! str_contains($bodyEn, '#63 (this article)') || ! str_contains($bodyEn, 'Beginner:') || ! str_contains($bodyEn, 'curl.exe') || ! str_contains($bodyEn, 'Tools used')) {
            report(new \RuntimeException('Article 63 English body missing expected content after seed.'));

            return response('Article 63 English content checks failed', 500);
        }

        $a62 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a62) {
            report(new \RuntimeException('Article 62 missing while publishing #63.'));

            return response('Article 63 prerequisite #62 missing', 500);
        }

        // Hardlink #62 -> #63 (aktif setelah #63 LIVE + EN).
        $b62 = (string) $a62->body;
        if (! str_contains($b62, $slug)) {
            $exit62 = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article62Seeder',
                '--force' => true,
            ]);
            if ($exit62 !== 0) {
                return response('Article 62 hardlink reseed failed', 500);
            }
            $a62 = Article::published()->where('slug', $prevSlug)->first();
            if (! $a62 || ! str_contains((string) $a62->body, $slug)) {
                report(new \RuntimeException('Article 62 hardlink to #63 missing after reseed.'));

                return response('Article 62 hardlink to #63 failed', 500);
            }
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

        return response('Article 63 published', 200);
    }

    public function publishArticle64(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        foreach ([
            'database/seeders/Article64Seeder.php',
            'database/seeders/Article63Seeder.php',
        ] as $relative) {
            $seederPath = base_path($relative);
            clearstatcache(true, $seederPath);
            if (function_exists('opcache_invalidate')) {
                opcache_invalidate($seederPath, true);
            }
        }

        if (! class_exists(\Database\Seeders\Article64Seeder::class)) {
            return response('Article64Seeder class not found on server', 500);
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 64 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article64Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 64 seed failed', 500);
        }

        $slug = 'laravel-eloquent-relasi-peminjaman';
        $prevSlug = 'laravel-crud-api-buku-ubah-hapus';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 64 missing or not visible after Article64Seeder on deploy hook.'));

            return response('Article 64 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel64relasiArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_eloquent_relasi_peminjaman_demo.php') || ! str_contains($body, 'belongsTo') || ! str_contains($body, 'hasMany') || ! str_contains($body, 'relasi') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 5') || ! str_contains($body, '#64 (ini)') || ! str_contains($body, '1/7') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Pola Dasar') || ! str_contains($body, 'Persiapan') || ! str_contains($body, 'notepad app\Models\Anggota.php')) {
            report(new \RuntimeException('Article 64 body missing expected content after seed.'));

            return response('Article 64 body content checks failed', 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 64 English fields are incomplete after seed.'));

            return response('Article 64 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        if (! str_contains($bodyEn, '#64 (this article)') || ! str_contains($bodyEn, 'Beginner:') || ! str_contains($bodyEn, 'Tools used in this article') || ! str_contains($bodyEn, 'Preparation') || ! str_contains($bodyEn, 'notepad app\Models\Anggota.php') || ! str_contains($bodyEn, 'belongsTo') || ! str_contains($bodyEn, 'hasMany')) {
            report(new \RuntimeException('Article 64 EN body missing expected content after seed.'));

            return response('Article 64 EN body content checks failed', 500);
        }

        $a63 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a63) {
            report(new \RuntimeException('Article 63 missing while publishing #64.'));

            return response('Article 64 prerequisite #63 missing', 500);
        }

        if (! str_contains((string) $a63->body, $slug)) {
            $exit63 = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article63Seeder',
                '--force' => true,
            ]);
            if ($exit63 !== 0) {
                return response('Article 63 hardlink reseed failed', 500);
            }
            $a63 = Article::published()->where('slug', $prevSlug)->first();
            if (! $a63 || ! str_contains((string) $a63->body, $slug) || ! str_contains((string) $a63->body_en, $slug)) {
                report(new \RuntimeException('Article 63 hardlink to #64 missing after reseed.'));

                return response('Article 63 hardlink to #64 failed', 500);
            }
        }

        // Hardlink #64 → #65 setelah reseed #64, jika #65 sudah LIVE (CI: step #65 jalan sebelum #64).
        $nextSlug = 'laravel-pagination-filter-pencarian';
        $a65 = Article::published()->where('slug', $nextSlug)->first();
        $article = Article::published()->where('slug', $slug)->first();
        if ($a65 && $article && ! str_contains((string) $article->body, $nextSlug)) {
            if ($this->ensureSeederClass('database/seeders/Article64Hardlink65Seeder.php', \Database\Seeders\Article64Hardlink65Seeder::class)) {
                $exitHl = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article64Hardlink65Seeder',
                    '--force' => true,
                ]);
                $article = Article::published()->where('slug', $slug)->first();
                if ($exitHl !== 0 || ! $article || ! str_contains((string) $article->body, $nextSlug)) {
                    report(new \RuntimeException('Article 64 hardlink to #65 missing after patch in publishArticle64.'));
                }
            }
        }

        $this->refreshLaravelSeriesExcerpts();

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

        return response('Article 64 published', 200);
    }

    public function publishArticle65(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article65Seeder.php', \Database\Seeders\Article65Seeder::class)) {
            return response('Article65Seeder class not found on server', 500);
        }

        $prevSlug = 'laravel-eloquent-relasi-peminjaman';

        // Bootstrap #64 jika hilang di prod (recovery path).
        $a64 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a64) {
            if (! $this->ensureSeederClass('database/seeders/Article64Seeder.php', \Database\Seeders\Article64Seeder::class)) {
                return response('Article64Seeder class not found on server', 500);
            }
            $bootstrap64 = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article64Seeder',
                '--force' => true,
            ]);
            if ($bootstrap64 !== 0) {
                return response('Article 64 bootstrap seed failed: '.trim(Artisan::output()), 500);
            }
            $a64 = Article::published()->where('slug', $prevSlug)->first();
            if (! $a64) {
                report(new \RuntimeException('Article 64 still missing after bootstrap seed in publishArticle65.'));

                return response('Article 64 bootstrap incomplete', 500);
            }
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 65 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article65Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 65 seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'laravel-pagination-filter-pencarian';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 65 missing or not visible after Article65Seeder on deploy hook.'));

            return response('Article 65 seed incomplete', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            'laravel65pageArrow',
            'color:#1a1a1a',
            'laravel_pagination_filter_pencarian_demo.php',
            'paginate',
            'array_slice',
            'demo(',
            'Seri 5',
            '#65 (ini)',
            '2/7',
            $prevSlug,
            'Pola Dasar',
            'Persiapan',
            'notepad app\Http\Controllers\PeminjamanController.php',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 65 body missing expected content after seed: '.implode(', ', $missingBody)));

            return response('Article 65 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 65 English fields are incomplete after seed.'));

            return response('Article 65 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#65 (this article)',
            'Beginner:',
            'Tools used in this article',
            'Preparation',
            'notepad app\Http\Controllers\PeminjamanController.php',
            'paginate',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 65 EN body missing expected content after seed: '.implode(', ', $missingEn)));

            return response('Article 65 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        $a64 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a64) {
            report(new \RuntimeException('Article 64 missing after bootstrap while publishing #65.'));

            return response('Article 65 prerequisite #64 missing', 500);
        }

        // Hardlink #64 → #65: setelah #65 LIVE, patch #64 tanpa reseed penuh.
        // Non-fatal — #65 LIVE lebih penting; hardlink bisa diulang lewat hook berikutnya.
        if (! str_contains((string) $a64->body, $slug)) {
            if ($this->ensureSeederClass('database/seeders/Article64Hardlink65Seeder.php', \Database\Seeders\Article64Hardlink65Seeder::class)) {
                $exit64 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article64Hardlink65Seeder',
                    '--force' => true,
                ]);
                $a64 = Article::published()->where('slug', $prevSlug)->first();
                if ($exit64 !== 0 || ! $a64 || ! str_contains((string) $a64->body, $slug)) {
                    report(new \RuntimeException('Article 64 hardlink to #65 deferred: patch exit='.$exit64.' slug_present='.(int) ($a64 && str_contains((string) $a64->body, $slug))));
                }
            } else {
                report(new \RuntimeException('Article64Hardlink65Seeder class not found — hardlink deferred.'));
            }
        }

        // Hardlink #65 → #66 setelah reseed #65, jika #66 sudah LIVE (CI: step #66 jalan sebelum #65).
        $nextSlug66 = 'laravel-policy-otorisasi-api';
        $a66 = Article::published()->where('slug', $nextSlug66)->first();
        if ($a66 && $article && ! str_contains((string) $article->body, $nextSlug66)) {
            if ($this->ensureSeederClass('database/seeders/Article65Hardlink66Seeder.php', \Database\Seeders\Article65Hardlink66Seeder::class)) {
                $exitHl66 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article65Hardlink66Seeder',
                    '--force' => true,
                ]);
                $article = Article::published()->where('slug', $slug)->first();
                if ($exitHl66 !== 0 || ! $article || ! str_contains((string) $article->body, $nextSlug66)) {
                    report(new \RuntimeException('Article 65 hardlink to #66 missing after patch in publishArticle65.'));
                }
            }
        }

        // Refresh excerpt kartu listing (#N basi di /artikel setelah renumber seri).
        $this->refreshLaravelSeriesExcerpts();

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

        return response('Article 65 published', 200);
    }

    public function publishArticle66(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article66Seeder.php', \Database\Seeders\Article66Seeder::class)) {
            return response('Article66Seeder class not found on server', 500);
        }

        $prevSlug = 'laravel-pagination-filter-pencarian';

        // Bootstrap #65 jika hilang di prod (recovery path).
        $a65 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a65) {
            if (! $this->ensureSeederClass('database/seeders/Article65Seeder.php', \Database\Seeders\Article65Seeder::class)) {
                return response('Article65Seeder class not found on server', 500);
            }
            $bootstrap65 = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article65Seeder',
                '--force' => true,
            ]);
            if ($bootstrap65 !== 0) {
                return response('Article 65 bootstrap seed failed: '.trim(Artisan::output()), 500);
            }
            $a65 = Article::published()->where('slug', $prevSlug)->first();
            if (! $a65) {
                report(new \RuntimeException('Article 65 still missing after bootstrap seed in publishArticle66.'));

                return response('Article 65 bootstrap incomplete', 500);
            }
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 66 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article66Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 66 seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'laravel-policy-otorisasi-api';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 66 missing or not visible after Article66Seeder on deploy hook.'));

            return response('Article 66 seed incomplete', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            'laravel66policyArrow',
            'color:#1a1a1a',
            'laravel_policy_otorisasi_api_demo.php',
            'authorize',
            'PeminjamanPolicy',
            '403',
            'demo(',
            'Seri 5',
            '#66 (ini)',
            '3/7',
            $prevSlug,
            'Pola Dasar',
            'Persiapan',
            'notepad app\Http\Controllers\PeminjamanController.php',
            'izin-cek.php',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 66 body missing expected content after seed: '.implode(', ', $missingBody)));

            return response('Article 66 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 66 English fields are incomplete after seed.'));

            return response('Article 66 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#66 (this article)',
            'Beginner:',
            'Tools used in this article',
            'Preparation',
            'authorize',
            'PeminjamanPolicy',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 66 EN body missing expected content after seed: '.implode(', ', $missingEn)));

            return response('Article 66 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        $a65 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a65) {
            report(new \RuntimeException('Article 65 missing after bootstrap while publishing #66.'));

            return response('Article 66 prerequisite #65 missing', 500);
        }

        // Hardlink #65 → #66: setelah #66 LIVE, patch #65 tanpa reseed penuh.
        // Non-fatal — #66 LIVE lebih penting; hardlink bisa diulang lewat hook berikutnya.
        if (! str_contains((string) $a65->body, $slug)) {
            if ($this->ensureSeederClass('database/seeders/Article65Hardlink66Seeder.php', \Database\Seeders\Article65Hardlink66Seeder::class)) {
                $exit65 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article65Hardlink66Seeder',
                    '--force' => true,
                ]);
                $a65 = Article::published()->where('slug', $prevSlug)->first();
                if ($exit65 !== 0 || ! $a65 || ! str_contains((string) $a65->body, $slug)) {
                    report(new \RuntimeException('Article 65 hardlink to #66 deferred: patch exit='.$exit65.' slug_present='.(int) ($a65 && str_contains((string) $a65->body, $slug))));
                }
            } else {
                report(new \RuntimeException('Article65Hardlink66Seeder class not found — hardlink deferred.'));
            }
        }

        // Hardlink #66 → #67 setelah reseed #66, jika #67 sudah LIVE (CI: step #67 jalan sebelum #66).
        $nextSlug67 = 'laravel-api-resource-json';
        $a67 = Article::published()->where('slug', $nextSlug67)->first();
        if ($a67 && $article && ! str_contains((string) $article->body, $nextSlug67)) {
            if ($this->ensureSeederClass('database/seeders/Article66Hardlink67Seeder.php', \Database\Seeders\Article66Hardlink67Seeder::class)) {
                $exitHl67 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article66Hardlink67Seeder',
                    '--force' => true,
                ]);
                $article = Article::published()->where('slug', $slug)->first();
                if ($exitHl67 !== 0 || ! $article || ! str_contains((string) $article->body, $nextSlug67)) {
                    report(new \RuntimeException('Article 66 hardlink to #67 missing after patch in publishArticle66.'));
                }
            }
        }

        $this->refreshLaravelSeriesExcerpts();

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

        return response('Article 66 published', 200);
    }

    public function publishArticle67(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article67Seeder.php', \Database\Seeders\Article67Seeder::class)) {
            return response('Article67Seeder class not found on server', 500);
        }

        $prevSlug = 'laravel-policy-otorisasi-api';

        // Bootstrap #66 jika hilang di prod (recovery path).
        $a66 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a66) {
            if (! $this->ensureSeederClass('database/seeders/Article66Seeder.php', \Database\Seeders\Article66Seeder::class)) {
                return response('Article66Seeder class not found on server', 500);
            }
            $bootstrap66 = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article66Seeder',
                '--force' => true,
            ]);
            if ($bootstrap66 !== 0) {
                return response('Article 66 bootstrap seed failed: '.trim(Artisan::output()), 500);
            }
            $a66 = Article::published()->where('slug', $prevSlug)->first();
            if (! $a66) {
                report(new \RuntimeException('Article 66 still missing after bootstrap seed in publishArticle67.'));

                return response('Article 66 bootstrap incomplete', 500);
            }
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 67 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article67Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 67 seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'laravel-api-resource-json';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 67 missing or not visible after Article67Seeder on deploy hook.'));

            return response('Article 67 seed incomplete', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            'laravel67resourceArrow',
            'color:#1a1a1a',
            'laravel_api_resource_json_demo.php',
            'JsonResource',
            'PeminjamanResource',
            'toArray',
            'demo(',
            'Seri 5',
            '#67 (ini)',
            '4/7',
            $prevSlug,
            'Pola Dasar',
            'Persiapan',
            'notepad app\Http\Resources\PeminjamanResource.php',
            'rapikan-cek.php',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 67 body missing expected content after seed: '.implode(', ', $missingBody)));

            return response('Article 67 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 67 English fields are incomplete after seed.'));

            return response('Article 67 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#67 (this article)',
            'Beginner:',
            'Tools used in this article',
            'Preparation',
            'JsonResource',
            'PeminjamanResource',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 67 EN body missing expected content after seed: '.implode(', ', $missingEn)));

            return response('Article 67 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        $a66 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a66) {
            report(new \RuntimeException('Article 66 missing after bootstrap while publishing #67.'));

            return response('Article 67 prerequisite #66 missing', 500);
        }

        // Hardlink #66 → #67: setelah #67 LIVE, patch #66 tanpa reseed penuh.
        // Non-fatal — #67 LIVE lebih penting; hardlink bisa diulang lewat hook berikutnya.
        if (! str_contains((string) $a66->body, $slug)) {
            if ($this->ensureSeederClass('database/seeders/Article66Hardlink67Seeder.php', \Database\Seeders\Article66Hardlink67Seeder::class)) {
                $exit66 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article66Hardlink67Seeder',
                    '--force' => true,
                ]);
                $a66 = Article::published()->where('slug', $prevSlug)->first();
                if ($exit66 !== 0 || ! $a66 || ! str_contains((string) $a66->body, $slug)) {
                    report(new \RuntimeException('Article 66 hardlink to #67 deferred: patch exit='.$exit66.' slug_present='.(int) ($a66 && str_contains((string) $a66->body, $slug))));
                }
            } else {
                report(new \RuntimeException('Article66Hardlink67Seeder class not found — hardlink deferred.'));
            }
        }

        // Hardlink #67 → #68 setelah reseed #67, jika #68 sudah LIVE (CI: step #68 jalan sebelum #67).
        $nextSlug68 = 'laravel-feature-test-api';
        $a68 = Article::published()->where('slug', $nextSlug68)->first();
        if ($a68 && $article && ! str_contains((string) $article->body, $nextSlug68)) {
            if ($this->ensureSeederClass('database/seeders/Article67Hardlink68Seeder.php', \Database\Seeders\Article67Hardlink68Seeder::class)) {
                $exitHl68 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article67Hardlink68Seeder',
                    '--force' => true,
                ]);
                $article = Article::published()->where('slug', $slug)->first();
                if ($exitHl68 !== 0 || ! $article || ! str_contains((string) $article->body, $nextSlug68)) {
                    report(new \RuntimeException('Article 67 hardlink to #68 missing after patch in publishArticle67.'));
                }
            }
        }

        $this->refreshLaravelSeriesExcerpts();

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

        return response('Article 67 published', 200);
    }

    public function publishArticle68(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article68Seeder.php', \Database\Seeders\Article68Seeder::class)) {
            return response('Article68Seeder class not found on server', 500);
        }

        $prevSlug = 'laravel-api-resource-json';

        // Bootstrap #67 jika hilang di prod (recovery path).
        $a67 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a67) {
            if (! $this->ensureSeederClass('database/seeders/Article67Seeder.php', \Database\Seeders\Article67Seeder::class)) {
                return response('Article67Seeder class not found on server', 500);
            }
            $bootstrap67 = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article67Seeder',
                '--force' => true,
            ]);
            if ($bootstrap67 !== 0) {
                return response('Article 67 bootstrap seed failed: '.trim(Artisan::output()), 500);
            }
            $a67 = Article::published()->where('slug', $prevSlug)->first();
            if (! $a67) {
                report(new \RuntimeException('Article 67 still missing after bootstrap seed in publishArticle68.'));

                return response('Article 67 bootstrap incomplete', 500);
            }
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 68 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article68Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 68 seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'laravel-feature-test-api';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 68 missing or not visible after Article68Seeder on deploy hook.'));

            return response('Article 68 seed incomplete', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            'laravel68testArrow',
            'color:#1a1a1a',
            'laravel_feature_test_api_demo.php',
            'assertJson',
            'assertStatus',
            'demo(',
            'Seri 5',
            '#68 (ini)',
            '5/7',
            $prevSlug,
            'Pola Dasar',
            'Persiapan',
            'uji-cek.php',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 68 body missing expected content after seed: '.implode(', ', $missingBody)));

            return response('Article 68 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! str_contains($body, 'php artisan test') && ! str_contains($body, 'vendor/bin/phpunit')) {
            report(new \RuntimeException('Article 68 body missing php artisan test or vendor/bin/phpunit after seed.'));

            return response('Article 68 body content checks failed: php artisan test', 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 68 English fields are incomplete after seed.'));

            return response('Article 68 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#68 (this article)',
            'Beginner:',
            'Tools used in this article',
            'Preparation',
            'assertJson',
            'Feature Test',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 68 EN body missing expected content after seed: '.implode(', ', $missingEn)));

            return response('Article 68 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        $a67 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a67) {
            report(new \RuntimeException('Article 67 missing after bootstrap while publishing #68.'));

            return response('Article 68 prerequisite #67 missing', 500);
        }

        // Hardlink #67 → #68: setelah #68 LIVE, patch #67 tanpa reseed penuh.
        // Non-fatal — #68 LIVE lebih penting; hardlink bisa diulang lewat hook berikutnya.
        if (! str_contains((string) $a67->body, $slug)) {
            if ($this->ensureSeederClass('database/seeders/Article67Hardlink68Seeder.php', \Database\Seeders\Article67Hardlink68Seeder::class)) {
                $exit67 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article67Hardlink68Seeder',
                    '--force' => true,
                ]);
                $a67 = Article::published()->where('slug', $prevSlug)->first();
                if ($exit67 !== 0 || ! $a67 || ! str_contains((string) $a67->body, $slug)) {
                    report(new \RuntimeException('Article 67 hardlink to #68 deferred: patch exit='.$exit67.' slug_present='.(int) ($a67 && str_contains((string) $a67->body, $slug))));
                }
            } else {
                report(new \RuntimeException('Article67Hardlink68Seeder class not found — hardlink deferred.'));
            }
        }

        // Hardlink #68 → #69 setelah reseed #68, jika #69 sudah LIVE (CI: step #69 jalan sebelum #68).
        $nextSlug69 = 'laravel-rate-limiting-api';
        $a69 = Article::published()->where('slug', $nextSlug69)->first();
        if ($a69 && $article && ! str_contains((string) $article->body, $nextSlug69)) {
            if ($this->ensureSeederClass('database/seeders/Article68Hardlink69Seeder.php', \Database\Seeders\Article68Hardlink69Seeder::class)) {
                $exitHl69 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article68Hardlink69Seeder',
                    '--force' => true,
                ]);
                $article = Article::published()->where('slug', $slug)->first();
                if ($exitHl69 !== 0 || ! $article || ! str_contains((string) $article->body, $nextSlug69)) {
                    report(new \RuntimeException('Article 68 hardlink to #69 missing after patch in publishArticle68.'));
                }
            }
        }

        $this->refreshLaravelSeriesExcerpts();

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

        return response('Article 68 published', 200);
    }

    public function publishArticle69(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article69Seeder.php', \Database\Seeders\Article69Seeder::class)) {
            return response('Article69Seeder class not found on server', 500);
        }

        $prevSlug = 'laravel-feature-test-api';

        // Bootstrap #68 jika hilang di prod (recovery path).
        $a68 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a68) {
            if (! $this->ensureSeederClass('database/seeders/Article68Seeder.php', \Database\Seeders\Article68Seeder::class)) {
                return response('Article68Seeder class not found on server', 500);
            }
            $bootstrap68 = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article68Seeder',
                '--force' => true,
            ]);
            if ($bootstrap68 !== 0) {
                return response('Article 68 bootstrap seed failed: '.trim(Artisan::output()), 500);
            }
            $a68 = Article::published()->where('slug', $prevSlug)->first();
            if (! $a68) {
                report(new \RuntimeException('Article 68 still missing after bootstrap seed in publishArticle69.'));

                return response('Article 68 bootstrap incomplete', 500);
            }
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 69 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article69Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 69 seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'laravel-rate-limiting-api';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 69 missing or not visible after Article69Seeder on deploy hook.'));

            return response('Article 69 seed incomplete', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            'laravel69rateArrow',
            'color:#1a1a1a',
            'laravel_rate_limiting_api_demo.php',
            'RateLimiter',
            'throttle',
            '429',
            'demo(',
            'Seri 5',
            '#69 (ini)',
            '6/7',
            $prevSlug,
            'Pola Dasar',
            'Persiapan',
            'batas-cek.php',
            'curl.exe',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 69 body missing expected content after seed: '.implode(', ', $missingBody)));

            return response('Article 69 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 69 English fields are incomplete after seed.'));

            return response('Article 69 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#69 (this article)',
            'Beginner:',
            'Tools used in this article',
            'Preparation',
            'RateLimiter',
            'throttle',
            'Capstone',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 69 EN body missing expected content after seed: '.implode(', ', $missingEn)));

            return response('Article 69 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        $a68 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a68) {
            report(new \RuntimeException('Article 68 missing after bootstrap while publishing #69.'));

            return response('Article 69 prerequisite #68 missing', 500);
        }

        // Hardlink #68 → #69: setelah #69 LIVE, patch #68 tanpa reseed penuh.
        // Non-fatal — #69 LIVE lebih penting; hardlink bisa diulang lewat hook berikutnya.
        if (! str_contains((string) $a68->body, $slug)) {
            if ($this->ensureSeederClass('database/seeders/Article68Hardlink69Seeder.php', \Database\Seeders\Article68Hardlink69Seeder::class)) {
                $exit68 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article68Hardlink69Seeder',
                    '--force' => true,
                ]);
                $a68 = Article::published()->where('slug', $prevSlug)->first();
                if ($exit68 !== 0 || ! $a68 || ! str_contains((string) $a68->body, $slug)) {
                    report(new \RuntimeException('Article 68 hardlink to #69 deferred: patch exit='.$exit68.' slug_present='.(int) ($a68 && str_contains((string) $a68->body, $slug))));
                }
            } else {
                report(new \RuntimeException('Article68Hardlink69Seeder class not found — hardlink deferred.'));
            }
        }

        // Hardlink #69 → #70 setelah reseed #69, jika #70 sudah LIVE (CI: step #70 jalan sebelum #69).
        $nextSlug70 = 'capstone-pinjam-kembali-laravel';
        $a70 = Article::published()->where('slug', $nextSlug70)->first();
        if ($a70 && $article && ! str_contains((string) $article->body, $nextSlug70)) {
            if ($this->ensureSeederClass('database/seeders/Article69Hardlink70Seeder.php', \Database\Seeders\Article69Hardlink70Seeder::class)) {
                $exitHl70 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article69Hardlink70Seeder',
                    '--force' => true,
                ]);
                $article = Article::published()->where('slug', $slug)->first();
                if ($exitHl70 !== 0 || ! $article || ! str_contains((string) $article->body, $nextSlug70)) {
                    report(new \RuntimeException('Article 69 hardlink to #70 missing after patch in publishArticle69.'));
                }
            }
        }

        $this->refreshLaravelSeriesExcerpts();

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

        return response('Article 69 published', 200);
    }

    public function publishArticle70(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article70Seeder.php', \Database\Seeders\Article70Seeder::class)) {
            return response('Article70Seeder class not found on server', 500);
        }

        $prevSlug = 'laravel-rate-limiting-api';

        $a69 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a69) {
            if (! $this->ensureSeederClass('database/seeders/Article69Seeder.php', \Database\Seeders\Article69Seeder::class)) {
                return response('Article69Seeder class not found on server', 500);
            }
            $bootstrap69 = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article69Seeder',
                '--force' => true,
            ]);
            if ($bootstrap69 !== 0) {
                return response('Article 69 bootstrap seed failed: '.trim(Artisan::output()), 500);
            }
            $a69 = Article::published()->where('slug', $prevSlug)->first();
            if (! $a69) {
                report(new \RuntimeException('Article 69 still missing after bootstrap seed in publishArticle70.'));

                return response('Article 69 bootstrap incomplete', 500);
            }
        }

        $tagExit = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\TagSeeder',
            '--force' => true,
        ]);

        if ($tagExit !== 0) {
            return response('Article 70 tag seed failed', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article70Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 70 seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'capstone-pinjam-kembali-laravel';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 70 missing or not visible after Article70Seeder on deploy hook.'));

            return response('Article 70 seed incomplete', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            'laravel70capArrow',
            'color:#1a1a1a',
            'capstone_pinjam_kembali_laravel_demo.php',
            'alur-cek.php',
            'authorize',
            'throttle:pinjam',
            'demo(',
            'Seri 5',
            '#70 (ini)',
            '7/7',
            $prevSlug,
            'Pola Dasar',
            'Persiapan',
            'curl.exe',
            'Piranti Bergerak',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 70 body missing expected content after seed: '.implode(', ', $missingBody)));

            return response('Article 70 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 70 English fields are incomplete after seed.'));

            return response('Article 70 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#70 (this article)',
            'Beginner:',
            'Tools used in this article',
            'Preparation',
            'authorize',
            'throttle:pinjam',
            'Mobile Devices',
            '7/7 — complete',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 70 EN body missing expected content after seed: '.implode(', ', $missingEn)));

            return response('Article 70 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        $a69 = Article::published()->where('slug', $prevSlug)->first();
        if (! $a69) {
            report(new \RuntimeException('Article 69 missing after bootstrap while publishing #70.'));

            return response('Article 70 prerequisite #69 missing', 500);
        }

        if (! str_contains((string) $a69->body, $slug)) {
            if ($this->ensureSeederClass('database/seeders/Article69Hardlink70Seeder.php', \Database\Seeders\Article69Hardlink70Seeder::class)) {
                $exit69 = Artisan::call('db:seed', [
                    '--class' => 'Database\\Seeders\\Article69Hardlink70Seeder',
                    '--force' => true,
                ]);
                $a69 = Article::published()->where('slug', $prevSlug)->first();
                if ($exit69 !== 0 || ! $a69 || ! str_contains((string) $a69->body, $slug)) {
                    report(new \RuntimeException('Article 69 hardlink to #70 deferred: patch exit='.$exit69.' slug_present='.(int) ($a69 && str_contains((string) $a69->body, $slug))));
                }
            } else {
                report(new \RuntimeException('Article69Hardlink70Seeder class not found — hardlink deferred.'));
            }
        }

        $this->refreshLaravelSeriesExcerpts();

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

        return response('Article 70 published', 200);
    }

    /**
     * Pre-launch B: seed #71 / FS-01 as draft only — never publish until jalur rilis.
     * Admin reviews via Filament Pratinjau (signed URL). Public /artikel/{slug} must stay 404.
     */
    public function seedArticle71Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article71Seeder.php', \Database\Seeders\Article71Seeder::class)) {
            return response('Article71Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article71Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 71 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-apa-itu-iot';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 71 missing after Article71Seeder draft seed.'));

            return response('Article 71 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 71 refused to stay draft after seed.'));

            return response('Article 71 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 71 unexpectedly visible via published() scope.'));

            return response('Article 71 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#71 (ini)',
            'FS-01',
            'Stasiun Ruang Belajar',
            'ESP32-DevKitC-1',
            'Persiapan',
            'Kesalahan yang sering terjadi',
            'Tidak ada perintah sintaks hari ini',
            'Cara pakai artikel ini',
            'Buka alat ini dulu',
            'esp32-devkitc-overview.jpg',
            'Espressif Systems',
            'kit-tv-remote.jpg',
            'kit-smart-bulbs.jpg',
            'kit-smart-plugs.jpg',
            'Arti sederhana',
            'fsiot-iot-checklist',
            '/belajar/fullstack-iot',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 71 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 71 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 71 English fields are incomplete after draft seed.'));

            return response('Article 71 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#71 (this article)',
            'Study Room Station',
            'ESP32-DevKitC-1',
            'Preparation',
            'There is no syntax to run today',
            'How to use this article',
            'Open this tool first',
            'esp32-devkitc-overview.jpg',
            'Espressif Systems',
            'kit-tv-remote.jpg',
            'kit-smart-bulbs.jpg',
            'kit-smart-plugs.jpg',
            'Simple meaning',
            'Common mistakes',
            'fsiot-iot-checklist',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 71 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 71 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        // Do NOT write sitemap — draft must not appear in public sitemap.
        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 71 seeded as draft (pre-launch B)', 200);
    }

    /**
     * Pre-launch B: seed #72 / FS-02 as draft only — never publish until jalur rilis.
     */
    public function seedArticle72Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article72Seeder.php', \Database\Seeders\Article72Seeder::class)) {
            return response('Article72Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article72Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 72 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-satu-gambar-jalur';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 72 missing after Article72Seeder draft seed.'));

            return response('Article 72 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 72 refused to stay draft after seed.'));

            return response('Article 72 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 72 unexpectedly visible via published() scope.'));

            return response('Article 72 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#72 (ini)',
            'FS-02',
            'SATU GAMBAR',
            'ZERO',
            'BUILDER',
            'HERO',
            'Stasiun Ruang Belajar',
            'Tidak ada perintah sintaks hari ini',
            'ESP32-DevKitC-1',
            '/belajar/fullstack-iot',
            'fs02-real-world-lamp.jpg',
            'esp32-devkitc-overview.jpg',
            'Cara pakai artikel ini',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'fondasi dari nol',
            'Perangkat',
            'Penyimpanan',
            'fsiot-worksheet-boxes',
            'fsiot-layer-roles',
            'worksheet interaktif',
            'perangkat lunak',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 72 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 72 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 72 English fields are incomplete after draft seed.'));

            return response('Article 72 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#72 (this article)',
            'In short:',
            'How to use this article',
            'ONE PICTURE',
            'Study Room Station',
            'There is no syntax to run today',
            'ESP32-DevKitC-1',
            'you are here',
            'fs02-real-world-lamp.jpg',
            'esp32-devkitc-overview.jpg',
            'Common mistakes',
            'foundation from zero',
            'fsiot-worksheet-boxes',
            'interactive worksheet',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 72 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 72 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 72 seeded as draft (pre-launch B)', 200);
    }

    /**
     * Pre-launch B: seed #73 / FS-03 as draft only — never publish until jalur rilis.
     */
    public function seedArticle73Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article73Seeder.php', \Database\Seeders\Article73Seeder::class)) {
            return response('Article73Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article73Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 73 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-kamus-mini';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 73 missing after Article73Seeder draft seed.'));

            return response('Article 73 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 73 refused to stay draft after seed.'));

            return response('Article 73 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 73 unexpectedly visible via published() scope.'));

            return response('Article 73 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#73 (ini)',
            'FS-03',
            'Tidak ada perintah sintaks hari ini',
            'Cara pakai artikel ini',
            'Intinya:',
            'Arti sederhana',
            'Kesalahan yang sering terjadi',
            'perangkat lunak',
            'kit-dht22.jpg',
            'kit-led-5mm.jpg',
            'esp32-devkitc-overview.jpg',
            'Sensor',
            'GPIO',
            'SQLite',
            'OTA',
            '1B',
            '12/15',
            'ESP32-DevKitC-1',
            '/belajar/fullstack-iot',
            'perangkat → sistem',
            'sistem → perangkat',
            'fsiot-kuis-matching',
            'kuis interaktif',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 73 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 73 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 73 English fields are incomplete after draft seed.'));

            return response('Article 73 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#73 (this article)',
            'In short:',
            'How to use this article',
            'Plain meaning',
            'Common mistakes',
            'There is no syntax to run today',
            'kit-dht22.jpg',
            'Actuator',
            'Microcontroller',
            '12/15',
            'ESP32-DevKitC-1',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 73 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 73 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 73 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle74Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article74Seeder.php', \Database\Seeders\Article74Seeder::class)) {
            return response('Article74Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article74Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 74 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-buka-kotak-kit';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 74 missing after Article74Seeder draft seed.'));

            return response('Article 74 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 74 refused to stay draft after seed.'));

            return response('Article 74 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 74 unexpectedly visible via published() scope.'));

            return response('Article 74 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#74 (ini)',
            'FS-04',
            'Tidak ada perintah sintaks hari ini',
            'Cara pakai artikel ini',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Belanja bertahap',
            'ESP32-DevKitC-1',
            'esp32-devkitc-overview.jpg',
            'esp32-devkitc-1-pinlayout.jpg',
            'Espressif Systems',
            'kit-breadboard.jpg',
            'commons.wikimedia.org',
            'Breadboard',
            'charge-only',
            'fsiot-kit-checklist',
            'checklist interaktif',
            'FS-05',
            '/belajar/fullstack-iot',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 74 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 74 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 74 English fields are incomplete after draft seed.'));

            return response('Article 74 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#74 (this article)',
            'In short:',
            'How to use this article',
            'Common mistakes',
            'Shopping in stages',
            'There is no syntax to run today',
            'ESP32-DevKitC-1',
            'kit-led-5mm.jpg',
            'commons.wikimedia.org',
            'interactive checklist',
            'fsiot-kit-checklist',
            'FS-05',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 74 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 74 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 74 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle75Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article75Seeder.php', \Database\Seeders\Article75Seeder::class)) {
            return response('Article75Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article75Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 75 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-keselamatan-sebelum-listrik';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 75 missing after Article75Seeder draft seed.'));

            return response('Article 75 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 75 refused to stay draft after seed.'));

            return response('Article 75 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 75 unexpectedly visible via published() scope.'));

            return response('Article 75 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#75 (ini)',
            'FS-05',
            'Tidak ada perintah sintaks hari ini',
            'Cara pakai artikel ini',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Short circuit',
            '3.3V',
            '5V',
            'cabut USB',
            'charge-only',
            'fsiot-safety-checklist',
            'checklist interaktif',
            'FS-06',
            '/belajar/fullstack-iot',
            'esp32-devkitc-overview.jpg',
            'esp-dev-kits',
            'kit-multimeter.jpg',
            'kit-led-5mm.jpg',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 75 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 75 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 75 English fields are incomplete after draft seed.'));

            return response('Article 75 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#75 (this article)',
            'In short:',
            'How to use this article',
            'Common mistakes',
            'There is no syntax to run today',
            'short circuit',
            'unplug USB',
            'interactive checklist',
            'fsiot-safety-checklist',
            'FS-06',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 75 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 75 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 75 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle76Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article76Seeder.php', \Database\Seeders\Article76Seeder::class)) {
            return response('Article76Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article76Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 76 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-komputer-siap-driver-arduino-ide';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 76 missing after Article76Seeder draft seed.'));

            return response('Article 76 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 76 refused to stay draft after seed.'));

            return response('Article 76 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 76 unexpectedly visible via published() scope.'));

            return response('Article 76 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#76 (ini)',
            'FS-06',
            'Cara pakai artikel ini',
            'Tidak perlu hari ini',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'multimeter dasar',
            'Arduino IDE',
            'Device Manager',
            'package_esp32_index.json',
            'ESP32 Dev Module',
            'Done uploading',
            'void setup',
            'fsiot-setup-checklist',
            'checklist interaktif',
            'FS-07',
            '/belajar/fullstack-iot',
            'esp32-devkitc-overview.jpg',
            'esp-dev-kits',
            'fs06-device-manager-esp32.png',
            'fs06-arduino-ide-overview.png',
            'silabs.com',
            'wch-ic.com',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 76 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 76 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 76 English fields are incomplete after draft seed.'));

            return response('Article 76 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#76 (this article)',
            'In short:',
            'How to use this article',
            'Common mistakes',
            'Not needed today',
            'basic multimeter',
            'Arduino IDE',
            'Device Manager',
            'Done uploading',
            'interactive checklist',
            'fsiot-setup-checklist',
            'FS-07',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 76 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 76 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 76 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle77Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article77Seeder.php', \Database\Seeders\Article77Seeder::class)) {
            return response('Article77Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article77Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 77 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-multimeter-untuk-awam';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 77 missing after Article77Seeder draft seed.'));

            return response('Article 77 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 77 refused to stay draft after seed.'));

            return response('Article 77 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 77 unexpectedly visible via published() scope.'));

            return response('Article 77 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#77 (ini)',
            'FS-07',
            'Multimeter digital',
            'V DC',
            'continuity',
            '3V3',
            '5V',
            '5V0',
            'kit-multimeter.jpg',
            'kit-multimeter-jacks.jpg',
            'esp32-devkitc-1-pinlayout.jpg',
            'fsiot-multimeter-checklist',
            'checklist interaktif',
            'Cara pakai artikel ini',
            'Tidak perlu hari ini',
            'FS-08',
            '/belajar/fullstack-iot',
            'Tidak ada perintah Arduino',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 77 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 77 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 77 English fields are incomplete after draft seed.'));

            return response('Article 77 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#77 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'V DC',
            'continuity',
            '5V0',
            'kit-multimeter-jacks.jpg',
            'interactive checklist',
            'fsiot-multimeter-checklist',
            'FS-08',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 77 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 77 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 77 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle78Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article78Seeder.php', \Database\Seeders\Article78Seeder::class)) {
            return response('Article78Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article78Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 78 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-listrik-mini-tegangan-arus-resistansi';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 78 missing after Article78Seeder draft seed.'));

            return response('Article 78 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 78 refused to stay draft after seed.'));

            return response('Article 78 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 78 unexpectedly visible via published() scope.'));

            return response('Article 78 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#78 (ini)',
            'FS-08',
            'Analogi air',
            'V = I x R',
            'kit-led-5mm.jpg',
            'kit-resistor-220ohm.jpg',
            'resistor-color-code.jpg',
            'Cara pakai artikel ini',
            'Tidak perlu hari ini',
            '220',
            '330',
            'fsiot-resistor-calc-root',
            'fsiot-electric-checklist',
            'FS-09',
            '/belajar/fullstack-iot',
            'Tidak ada wiring breadboard',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 78 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 78 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 78 English fields are incomplete after draft seed.'));

            return response('Article 78 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#78 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'Water analogy',
            'V = I x R',
            'kit-resistor-220ohm.jpg',
            'resistor-color-code.jpg',
            'fsiot-resistor-calc-root',
            'fsiot-electric-checklist',
            'FS-09',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 78 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 78 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 78 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle79Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article79Seeder.php', \Database\Seeders\Article79Seeder::class)) {
            return response('Article79Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article79Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 79 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-led-resistor-di-breadboard';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 79 missing after Article79Seeder draft seed.'));

            return response('Article 79 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 79 refused to stay draft after seed.'));

            return response('Article 79 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 79 unexpectedly visible via published() scope.'));

            return response('Article 79 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#79 (ini)',
            'FS-09',
            'kit-breadboard.jpg',
            'kit-jumper-wires.jpg',
            'kit-led-5mm.jpg',
            'kit-resistor-220ohm.jpg',
            'fs09-led-breadboard-wiring.png',
            'Gambar utama',
            'Skema berlabel',
            'Jangan sambungkan 3V3 dan GND',
            'Orientasi foto',
            'kolom 7',
            'esp32-devkitc-1-pinlayout.jpg',
            'Cari 2 pin ini di board kamu',
            'Cara pakai artikel ini',
            'Tidak perlu hari ini',
            'kolom 2',
            'ESP32 dipasang di breadboard',
            'foto rangkaian</strong> + <strong>skema berlabel',
            'fsiot-led-circuit-checklist',
            'FS-10',
            '/belajar/fullstack-iot',
            'belum upload sketch',
            'cabut jumper',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 79 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 79 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 79 English fields are incomplete after draft seed.'));

            return response('Article 79 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#79 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'kit-breadboard.jpg',
            'kit-led-5mm.jpg',
            'kit-resistor-220ohm.jpg',
            'esp32-devkitc-1-pinlayout.jpg',
            'fs09-led-breadboard-wiring.png',
            'Main diagram',
            'Labeled schematic',
            'Never put 3V3 and GND',
            'Photo orientation',
            'column 2',
            'column 7',
            'ESP32 sits on the breadboard',
            'circuit photo</strong> + <strong>labeled schematic',
            'fsiot-led-circuit-checklist',
            'FS-10',
            'no sketch upload',
            'unplug',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 79 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 79 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 79 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle80Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article80Seeder.php', \Database\Seeders\Article80Seeder::class)) {
            return response('Article80Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article80Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 80 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-digital-analog-high-low-pull-resistor';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 80 missing after Article80Seeder draft seed.'));

            return response('Article 80 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 80 refused to stay draft after seed.'));

            return response('Article 80 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 80 unexpectedly visible via published() scope.'));

            return response('Article 80 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#80 (ini)',
            'FS-10',
            'kit-tactile-button.jpg',
            'kit-breadboard.jpg',
            'kit-jumper-wires.jpg',
            'kit-multimeter.jpg',
            'esp32-devkitc-1-pinlayout.jpg',
            'kit-resistor-10kohm.jpg',
            'Ceramic_Composition_Resistor_10k.png',
            'fs10-button-pulldown-wiring.png',
            'fs10-button-pulldown-wiring.svg',
            'fs10-pullup-pulldown.svg',
            'Gambar utama',
            'Skema berlabel',
            'Orientasi foto',
            'belum ada kabel ke pin GPIO',
            'Alur hari ini',
            'Cara pakai artikel ini',
            'Buka alat ini dulu',
            'tabel ukur tombol',
            'Uji dengan multimeter',
            'pull-down',
            '10 kΩ',
            'cokelat–hitam–oranye',
            'Kertas + pena',
            'FS-05',
            'FS-09',
            'fsiot-signal-checklist',
            'FS-11',
            '/belajar/fullstack-iot',
            'Belum upload sketch',
            'Wiring langkah demi langkah',
            'kaki A',
            'Ke GND',
            '3V3 → tombol → titik sinyal',
            'Kesalahan yang sering terjadi',
            'bukan perintah sintaks',
            'Analogi:',
            'Intinya:',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 80 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 80 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 80 English fields are incomplete after draft seed.'));

            return response('Article 80 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#80 (this article)',
            'Analogy:',
            'In short:',
            'kit-tactile-button.jpg',
            'kit-breadboard.jpg',
            'kit-jumper-wires.jpg',
            'kit-multimeter.jpg',
            'esp32-devkitc-1-pinlayout.jpg',
            'kit-resistor-10kohm.jpg',
            'Ceramic_Composition_Resistor_10k.png',
            'fs10-button-pulldown-wiring.png',
            'fs10-button-pulldown-wiring.svg',
            'green box',
            'fs10-pullup-pulldown.svg',
            'Main diagram',
            'Labeled schematic',
            'Photo orientation',
            'no wire to any GPIO',
            'flow — FS-10',
            'How to use this article',
            'Open this tool first',
            'measurement table',
            'Test with a multimeter',
            'pull-down',
            '10 kΩ',
            'brown–black–orange',
            'Paper + pen',
            'float',
            'interactive checklist',
            'fsiot-signal-checklist',
            'FS-11',
            'No sketch upload',
            'button leg A',
            'To GND',
            '3V3 → button → signal node',
            'Common mistakes',
            'not syntax commands',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 80 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 80 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 80 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle81Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article81Seeder.php', \Database\Seeders\Article81Seeder::class)) {
            return response('Article81Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article81Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 81 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-sketch-setup-loop';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 81 missing after Article81Seeder draft seed.'));

            return response('Article 81 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 81 refused to stay draft after seed.'));

            return response('Article 81 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 81 unexpectedly visible via published() scope.'));

            return response('Article 81 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#81 (ini)',
            'FS-11',
            'FS11_hello',
            'Arduino IDE',
            'File Explorer',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'Done compiling',
            'Done uploading',
            'setup()',
            'loop()',
            'fs11-ide-overview-cite.png',
            'esp32-devkitc-overview.jpg',
            'fsiot-sketch-checklist',
            'Ide-2-overview.png',
            'Verify di Arduino IDE 2',
            'Upload di Arduino IDE 2',
            'bukan IDE 1.x',
            'FS-06',
            'FS-10',
            'FS-12',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 81 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 81 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 81 English fields are incomplete after draft seed.'));

            return response('Article 81 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#81 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'Arduino IDE',
            'File Explorer',
            'Done compiling',
            'Done uploading',
            'FS11_hello',
            'fs11-ide-overview-cite.png',
            'fsiot-sketch-checklist',
            'FS-12',
            'setup()',
            'loop()',
            'Common mistakes',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 81 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 81 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 81 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle82Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article82Seeder.php', \Database\Seeders\Article82Seeder::class)) {
            return response('Article82Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article82Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 82 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-variabel-tipe-data-serial';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 82 missing after Article82Seeder draft seed.'));

            return response('Article 82 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 82 refused to stay draft after seed.'));

            return response('Article 82 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 82 unexpectedly visible via published() scope.'));

            return response('Article 82 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#82 (ini)',
            'FS-12',
            'FS12_hello',
            'Arduino IDE',
            'Serial Monitor',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'Open Serial Monitor',
            'Samakan baud',
            '115200',
            'bukan 9600',
            'screenshot',
            'FS-11',
            'FS-13',
            'esp32-devkitc-overview.jpg',
            'fsiot-var-checklist',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 82 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 82 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 82 English fields are incomplete after draft seed.'));

            return response('Article 82 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#82 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'Arduino IDE',
            'Serial Monitor',
            'Serial.begin(115200)',
            'FS12_hello',
            'Match the baud',
            'esp32-devkitc-overview.jpg',
            'fsiot-var-checklist',
            'FS-13',
            'Common mistakes',
            'How to test the code above',
            '9600',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 82 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 82 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 82 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle83Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article83Seeder.php', \Database\Seeders\Article83Seeder::class)) {
            return response('Article83Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article83Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 83 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-serial-monitor-debug';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 83 missing after Article83Seeder draft seed.'));

            return response('Article 83 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 83 refused to stay draft after seed.'));

            return response('Article 83 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 83 unexpectedly visible via published() scope.'));

            return response('Article 83 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#83 (ini)',
            'FS-13',
            'FS13_detak',
            'Arduino IDE',
            'Serial Monitor',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'Serial.begin(115200)',
            'delay(1000)',
            'FS-12',
            'FS-14',
            'esp32-devkitc-overview.jpg',
            'fsiot-sm-checklist',
            '115200',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 83 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 83 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 83 English fields are incomplete after draft seed.'));

            return response('Article 83 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#83 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'Arduino IDE',
            'Serial Monitor',
            'Serial.begin(115200)',
            'delay(1000)',
            'FS13_detak',
            'esp32-devkitc-overview.jpg',
            'fsiot-sm-checklist',
            'FS-14',
            'Common mistakes',
            'How to test the code above',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 83 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 83 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 83 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle84Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article84Seeder.php', \Database\Seeders\Article84Seeder::class)) {
            return response('Article84Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article84Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 84 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-if-else';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 84 missing after Article84Seeder draft seed.'));

            return response('Article 84 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 84 refused to stay draft after seed.'));

            return response('Article 84 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 84 unexpectedly visible via published() scope.'));

            return response('Article 84 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#84 (ini)',
            'FS-14',
            'FS14_panas',
            'Arduino IDE',
            'Serial Monitor',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'Serial.begin(115200)',
            'else if',
            'FS-13',
            'FS-15',
            'esp32-devkitc-overview.jpg',
            'fsiot-if-checklist',
            '115200',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'PANAS',
            '==',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 84 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 84 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 84 English fields are incomplete after draft seed.'));

            return response('Article 84 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#84 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'Arduino IDE',
            'Serial Monitor',
            'Serial.begin(115200)',
            'else if',
            'FS14_panas',
            'esp32-devkitc-overview.jpg',
            'fsiot-if-checklist',
            'FS-15',
            'Common mistakes',
            'How to test the code above',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 84 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 84 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 84 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle85Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article85Seeder.php', \Database\Seeders\Article85Seeder::class)) {
            return response('Article85Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article85Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 85 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-for-while-loop';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 85 missing after Article85Seeder draft seed.'));

            return response('Article 85 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 85 refused to stay draft after seed.'));

            return response('Article 85 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 85 unexpectedly visible via published() scope.'));

            return response('Article 85 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#85 (ini)',
            'FS-15',
            'FS15_hitung',
            'Arduino IDE',
            'Serial Monitor',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'Serial.begin(115200)',
            'for (int i = 1',
            // After ArticleHtmlSanitizer/DOM, raw "<=" becomes "&lt;="
            'while (n &lt;= 10)',
            'FS-14',
            'FS-16',
            'esp32-devkitc-overview.jpg',
            'fsiot-fw-checklist',
            '115200',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'millis()',
            'EN (7)',
            'Simpan sebagai',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 85 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 85 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 85 English fields are incomplete after draft seed.'));

            return response('Article 85 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#85 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'Arduino IDE',
            'Serial Monitor',
            'Serial.begin(115200)',
            'FS15_hitung',
            'esp32-devkitc-overview.jpg',
            'fsiot-fw-checklist',
            'FS-16',
            'Common mistakes',
            'How to test the code above',
            'while (true)',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 85 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 85 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 85 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle86Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article86Seeder.php', \Database\Seeders\Article86Seeder::class)) {
            return response('Article86Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article86Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 86 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-fungsi';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 86 missing after Article86Seeder draft seed.'));

            return response('Article 86 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 86 refused to stay draft after seed.'));

            return response('Article 86 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 86 unexpectedly visible via published() scope.'));

            return response('Article 86 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#86 (ini)',
            'FS-16',
            'FS16_status',
            'Arduino IDE',
            'Serial Monitor',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'Serial.begin(115200)',
            'cetakStatus(int suhu)',
            'FS-15',
            'FS-17',
            'esp32-devkitc-overview.jpg',
            'fs11-ide-overview-cite.png',
            'fsiot-fn-checklist',
            '115200',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'Simpan sebagai',
            'EN (7)',
            'BENAR',
            'SALAH',
            'sejajar dengan',
            'status: PANAS',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 86 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 86 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 86 English fields are incomplete after draft seed.'));

            return response('Article 86 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#86 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'Arduino IDE',
            'Serial Monitor',
            'Serial.begin(115200)',
            'FS16_status',
            'printStatus(int temp)',
            'esp32-devkitc-overview.jpg',
            'fs11-ide-overview-cite.png',
            'fsiot-fn-checklist',
            'FS-17',
            'Common mistakes',
            'How to test the code above',
            'CORRECT',
            'WRONG',
            'EN (7)',
            'status: HOT',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 86 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 86 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 86 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle87Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article87Seeder.php', \Database\Seeders\Article87Seeder::class)) {
            return response('Article87Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article87Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 87 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-peta-pin-devkitc-1';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 87 missing after Article87Seeder draft seed.'));

            return response('Article 87 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 87 refused to stay draft after seed.'));

            return response('Article 87 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 87 unexpectedly visible via published() scope.'));

            return response('Article 87 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#87 (ini)',
            'FS-17',
            'BUILDER',
            'esp32-devkitc-1-pinlayout.jpg',
            'esp32-devkitc-overview.jpg',
            'IO6',
            'IO11',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-pin-checklist',
            'GPIO 27',
            'FS-16',
            'FS-18',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji pemahaman di atas',
            'EN (7)',
            'boards/ESP32-DevKitC-1.html',
            'Mulai dari 3 label',
            'proses menyala',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 87 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 87 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 87 English fields are incomplete after draft seed.'));

            return response('Article 87 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#87 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'esp32-devkitc-1-pinlayout.jpg',
            'fsiot-pin-checklist',
            'FS-18',
            'Common mistakes',
            'How to test the understanding above',
            'EN (7)',
            'boards/ESP32-DevKitC-1.html',
            'input-only',
            'Start with only 3 labels',
            'power-on',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 87 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 87 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 87 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle88Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article88Seeder.php', \Database\Seeders\Article88Seeder::class)) {
            return response('Article88Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article88Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 88 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-led-dari-kode';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 88 missing after Article88Seeder draft seed.'));

            return response('Article 88 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 88 refused to stay draft after seed.'));

            return response('Article 88 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 88 unexpectedly visible via published() scope.'));

            return response('Article 88 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#88 (ini)',
            'FS-18',
            'BUILDER',
            'FS18_blink',
            'pinMode',
            'digitalWrite',
            'GPIO 2',
            '220',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-blink-checklist',
            'FS-17',
            'FS-09',
            'FS-19',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'EN (7)',
            'functions/digital-io/pinmode',
            'functions/digital-io/digitalwrite',
            'kit-led-5mm.jpg',
            'kit-resistor-220ohm.jpg',
            'fs18-led-gpio2-breadboard.png',
            'Gambar utama',
            'fs11-ide-overview-cite.png',
            'polaritas LED',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 88 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 88 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 88 English fields are incomplete after draft seed.'));

            return response('Article 88 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#88 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS18_blink',
            'pinMode',
            'digitalWrite',
            'fsiot-blink-checklist',
            'FS-19',
            'Common mistakes',
            'How to test the commands above',
            'EN (7)',
            'functions/digital-io/pinmode',
            'functions/digital-io/digitalwrite',
            'fs18-led-gpio2-breadboard.png',
            'Main figure',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 88 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 88 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 88 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle89Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article89Seeder.php', \Database\Seeders\Article89Seeder::class)) {
            return response('Article89Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article89Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 89 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-tombol-debounce';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 89 missing after Article89Seeder draft seed.'));

            return response('Article 89 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 89 refused to stay draft after seed.'));

            return response('Article 89 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 89 unexpectedly visible via published() scope.'));

            return response('Article 89 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#89 (ini)',
            'FS-19',
            'BUILDER',
            'FS19_btn_debounce',
            'digitalRead',
            'INPUT_PULLUP',
            'millis',
            'GPIO 4',
            'GPIO 2',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-btn-checklist',
            'FS-18',
            'FS-10',
            'FS-15',
            'FS-20',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'EN (7)',
            'functions/digital-io/digitalread',
            'functions/time/millis',
            'kit-tactile-button.jpg',
            'kit-led-5mm.jpg',
            'fs11-ide-overview-cite.png',
            'esp32-devkitc-overview.jpg',
            'fs19-btn-gpio4-breadboard.png',
            'fs19-button-pullup-wiring.png',
            'Gambar utama',
            'Catatan pola FS-10',
            'parit tengah',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 89 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 89 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (str_contains($body, 'fs10-button-pulldown-wiring')) {
            report(new \RuntimeException('Article 89 still embeds confusing FS-10 GPIO0 reference photo.'));

            return response('Article 89 must not embed fs10-button-pulldown-wiring', 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 89 English fields are incomplete after draft seed.'));

            return response('Article 89 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#89 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS19_btn_debounce',
            'digitalRead',
            'INPUT_PULLUP',
            'millis',
            'fsiot-btn-checklist',
            'FS-20',
            'Common mistakes',
            'How to test the commands above',
            'EN (7)',
            'functions/digital-io/digitalread',
            'functions/time/millis',
            'kit-tactile-button.jpg',
            'Main figure',
            'fs19-btn-gpio4-breadboard.png',
            'fs19-button-pullup-wiring.png',
            'FS-10 pattern note',
            'center ditch',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 89 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 89 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        if (str_contains($bodyEn, 'fs10-button-pulldown-wiring')) {
            report(new \RuntimeException('Article 89 EN still embeds confusing FS-10 GPIO0 reference photo.'));

            return response('Article 89 EN must not embed fs10-button-pulldown-wiring', 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 89 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle90Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article90Seeder.php', \Database\Seeders\Article90Seeder::class)) {
            return response('Article90Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article90Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 90 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-pwm-redupkan-led';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 90 missing after Article90Seeder draft seed.'));

            return response('Article 90 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 90 refused to stay draft after seed.'));

            return response('Article 90 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 90 unexpectedly visible via published() scope.'));

            return response('Article 90 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#90 (ini)',
            'FS-20',
            'BUILDER',
            'FS20_led_fade',
            'analogWrite',
            'duty cycle',
            'GPIO 2',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-pwm-checklist',
            'FS-18',
            'FS-21',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'EN (7)',
            'functions/analog-io/analogwrite',
            'api/ledc.html',
            'fs18-led-gpio2-breadboard.png',
            'fs20-duty-cycle-examples.png',
            'fs20-pwm-5steps.png',
            'Gambar utama',
            'napas',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 90 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 90 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 90 English fields are incomplete after draft seed.'));

            return response('Article 90 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#90 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS20_led_fade',
            'analogWrite',
            'fsiot-pwm-checklist',
            'FS-21',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'fs18-led-gpio2-breadboard.png',
            'fs20-duty-cycle-examples.png',
            'breathing LED',
            'duty cycle',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 90 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 90 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 90 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle91Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! $this->ensureSeederClass('database/seeders/Article91Seeder.php', \Database\Seeders\Article91Seeder::class)) {
            return response('Article91Seeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\Article91Seeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 91 draft seed failed: '.trim(Artisan::output()), 500);
        }

        $slug = 'fullstack-iot-sensor-dht22-serial';
        $article = Article::where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 91 missing after Article91Seeder draft seed.'));

            return response('Article 91 draft seed incomplete', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            $article->status = 'draft';
            $article->published_at = null;
            $article->save();
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 91 refused to stay draft after seed.'));

            return response('Article 91 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 91 unexpectedly visible via published() scope.'));

            return response('Article 91 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#91 (ini)',
            'FS-21',
            'BUILDER',
            'FS21_dht22_serial',
            'DHT22',
            'kelembapan',
            'GPIO 4',
            'Library Manager',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-dht-checklist',
            'FS-18',
            'FS-17',
            'FS-22',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'EN (7)',
            'kit-dht22.jpg',
            'fs21-dht22-breadboard.png',
            'fs21-dht22-wiring.png',
            'Gambar utama',
            'isnan',
            'Adafruit',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 91 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 91 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 91 English fields are incomplete after draft seed.'));

            return response('Article 91 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#91 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS21_dht22_serial',
            'DHT22',
            'humidity',
            'fsiot-dht-checklist',
            'FS-22',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'fs21-dht22-breadboard.png',
            'fs21-dht22-wiring.png',
            'Library Manager',
            'isnan',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 91 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 91 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 91 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle92Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article92Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 92 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-ldr-adc-seberapa-terang';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 92 missing after draft seed.'));

            return response('Article 92 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 92 refused to stay draft after seed.'));

            return response('Article 92 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 92 unexpectedly visible via published() scope.'));

            return response('Article 92 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#92 (ini)',
            'FS-22',
            'BUILDER',
            'FS22_ldr_adc',
            'LDR',
            'GPIO 34',
            'analogRead',
            '10 kΩ',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-ldr-checklist',
            'FS-18',
            'FS-17',
            'FS-21',
            'FS-23',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'EN (7)',
            'kit-ldr.jpg',
            'fs22-ldr-breadboard.png',
            'fs22-ldr-wiring.png',
            'Gambar utama',
            'Skema bantu',
            'GELAP',
            'Arduino Docs',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 92 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 92 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 92 English fields are incomplete after draft seed.'));

            return response('Article 92 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#92 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS22_ldr_adc',
            'LDR',
            'GPIO 34',
            'analogRead',
            'fsiot-ldr-checklist',
            'FS-23',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs22-ldr-breadboard.png',
            'fs22-ldr-wiring.png',
            'DARK',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 92 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 92 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 92 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle93Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article93Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 93 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-relay-aman-beban-kecil';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 93 missing after draft seed.'));

            return response('Article 93 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 93 refused to stay draft after seed.'));

            return response('Article 93 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 93 unexpectedly visible via published() scope.'));

            return response('Article 93 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#93 (ini)',
            'FS-23',
            'BUILDER',
            'FS23_relay_klik',
            'GPIO 26',
            'relay',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-relay-checklist',
            'FS-18',
            'FS-05',
            'FS-24',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'EN (7)',
            'kit-relay-5v.jpg',
            'fs23-relay-breadboard.png',
            'fs23-relay-wiring.png',
            'Gambar utama',
            'Skema bantu',
            'AnalogReadSerial',
            'Baud: 115200',
            'Normally Closed',
            'otomasi',
            'Buka Arduino IDE dulu',
            '220V',
            'AKTIF_LOW',
            'Arduino Docs',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 93 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 93 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 93 English fields are incomplete after draft seed.'));

            return response('Article 93 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#93 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS23_relay_klik',
            'GPIO 26',
            'relay',
            'fsiot-relay-checklist',
            'FS-24',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs23-relay-breadboard.png',
            'fs23-relay-wiring.png',
            'AnalogReadSerial',
            'Baud: 115200',
            'Normally Closed',
            'Open Arduino IDE first',
            'ACTIVE_LOW',
            '220V',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 93 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 93 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 93 seeded as draft (pre-launch B)', 200);
    }


    public function seedArticle94Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article94Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 94 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-otomasi-lokal-panas-relay';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 94 missing after draft seed.'));

            return response('Article 94 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 94 refused to stay draft after seed.'));

            return response('Article 94 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 94 unexpectedly visible via published() scope.'));

            return response('Article 94 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#94 (ini)',
            'FS-24',
            'BUILDER',
            'FS24_panas_relay',
            'GPIO 4',
            'GPIO 26',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-auto-checklist',
            'FS-21',
            'FS-23',
            'FS-14',
            'FS-25',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'histeresis',
            'AMBANG_ON',
            'kit-dht22.jpg',
            'kit-relay-5v.jpg',
            'fs24-otomasi-wiring.png',
            'fs24-otomasi-breadboard.png',
            'Gambar utama',
            'Skema bantu',
            'AnalogReadSerial',
            'Baud: 115200',
            '220V',
            'AKTIF_LOW',
            'Buka Arduino IDE dulu',
            'Arduino Docs',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 94 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 94 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 94 English fields are incomplete after draft seed.'));

            return response('Article 94 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#94 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS24_panas_relay',
            'GPIO 4',
            'GPIO 26',
            'fsiot-auto-checklist',
            'FS-25',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs24-otomasi-wiring.png',
            'fs24-otomasi-breadboard.png',
            'AnalogReadSerial',
            'Baud: 115200',
            'ACTIVE_LOW',
            'hysteresis',
            'Open Arduino IDE first',
            '220V',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 94 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 94 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 94 seeded as draft (pre-launch B)', 200);
    }


    public function seedArticle95Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article95Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 95 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-pir-gerak';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 95 missing after draft seed.'));

            return response('Article 95 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 95 refused to stay draft after seed.'));

            return response('Article 95 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 95 unexpectedly visible via published() scope.'));

            return response('Article 95 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#95 (ini)',
            'FS-25',
            'BUILDER',
            'FS25_pir_gerak',
            'GPIO 25',
            'GPIO 2',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-pir-checklist',
            'FS-19',
            'FS-14',
            'FS-26',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'settle',
            'kit-pir-hcsr501.jpg',
            'fs25-pir-breadboard.png',
            'fs25-pir-wiring.png',
            'Gambar utama',
            'Skema bantu',
            'potensiometer',
            'Jangan tebak dari warna kabel foto',
            'AnalogReadSerial',
            'Baud: 115200',
            'Buka Arduino IDE dulu',
            'HC-SR501',
            'digitalRead',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 95 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 95 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 95 English fields are incomplete after draft seed.'));

            return response('Article 95 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#95 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS25_pir_gerak',
            'GPIO 25',
            'GPIO 2',
            'fsiot-pir-checklist',
            'FS-26',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs25-pir-breadboard.png',
            'fs25-pir-wiring.png',
            'AnalogReadSerial',
            'Baud: 115200',
            'Open Arduino IDE first',
            'settle',
            'HC-SR501',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 95 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 95 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 95 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle96Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article96Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 96 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-servo-pwm';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 96 missing after draft seed.'));

            return response('Article 96 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 96 refused to stay draft after seed.'));

            return response('Article 96 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 96 unexpectedly visible via published() scope.'));

            return response('Article 96 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#96 (ini)',
            'FS-26',
            'BUILDER',
            'FS26_servo_sudut',
            'GPIO 13',
            'ESP32Servo',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-servo-checklist',
            'FS-20',
            'FS-14',
            'FS-27',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'Library Manager',
            'kit-servo-sg90.jpg',
            'fs26-servo-breadboard.png',
            'fs26-servo-wiring.png',
            'fs26-library-manager.png',
            'fs26-servo-timing.png',
            'Gambar utama',
            'Skema bantu',
            'AnalogReadSerial',
            'Baud: 115200',
            'Buka Arduino IDE dulu',
            'SG90',
            'Wajib sebelum Verify',
            'mikro servo',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 96 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 96 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 96 English fields are incomplete after draft seed.'));

            return response('Article 96 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#96 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'FS26_servo_sudut',
            'GPIO 13',
            'ESP32Servo',
            'fsiot-servo-checklist',
            'FS-27',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs26-servo-breadboard.png',
            'fs26-servo-wiring.png',
            'fs26-library-manager.png',
            'fs26-servo-timing.png',
            'Library Manager',
            'AnalogReadSerial',
            'Baud: 115200',
            'Open Arduino IDE first',
            'SG90',
            'Required before Verify',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 96 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 96 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 96 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle97Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article97Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 97 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-bus-uart-i2c-spi';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 97 missing after draft seed.'));

            return response('Article 97 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 97 refused to stay draft after seed.'));

            return response('Article 97 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 97 unexpectedly visible via published() scope.'));

            return response('Article 97 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#97 (ini)',
            'FS-27',
            'BUILDER',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-bus-checklist',
            'FS-17',
            'FS-14',
            'FS-28',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji pemahaman di atas',
            'UART',
            'I2C',
            'SPI',
            'fs27-bus-compare.png',
            'Gambar utama',
            'Buka artikel ini di browser',
            'GPIO 21',
            'GPIO 22',
            'BME280',
            'OLED',
            'microSD',
            'fs27-i2c-labeled.png',
            'fs27-spi-labeled.png',
            'pengendali',
            'Chip Select',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 97 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 97 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 97 English fields are incomplete after draft seed.'));

            return response('Article 97 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#97 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'fsiot-bus-checklist',
            'FS-28',
            'Common mistakes',
            'How to test the understanding above',
            'Main figure',
            'fs27-bus-compare.png',
            'Open this article in the browser',
            'UART',
            'I2C',
            'SPI',
            'GPIO 21',
            'GPIO 22',
            'fs27-i2c-labeled.png',
            'fs27-spi-labeled.png',
            'Chip Select',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 97 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 97 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 97 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle98Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article98Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 98 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-i2c-bme280-oled';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 98 missing after draft seed.'));

            return response('Article 98 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 98 refused to stay draft after seed.'));

            return response('Article 98 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 98 unexpectedly visible via published() scope.'));

            return response('Article 98 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#98 (ini)',
            'FS-28',
            'BUILDER',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-i2c-checklist',
            'FS-27',
            'FS-21',
            'FS-14',
            'FS-17',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'BME280',
            'OLED',
            'GPIO 21',
            'GPIO 22',
            'FS28_bme280_oled',
            'Adafruit',
            'fs28-i2c-breadboard.png',
            'fs28-i2c-wiring.png',
            'Gambar utama',
            'Skema bantu',
            'SCK = SCL',
            'VDD = VCC',
            '3V3 dan 5V',
            'Buka Arduino IDE dulu',
            'Library Manager',
            '115200',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 98 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 98 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 98 English fields are incomplete after draft seed.'));

            return response('Article 98 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#98 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'BUILDER',
            'fsiot-i2c-checklist',
            'FS-27',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs28-i2c-breadboard.png',
            'fs28-i2c-wiring.png',
            'SCK = SCL',
            'VDD = VCC',
            '3V3 and 5V',
            'Open Arduino IDE first',
            'BME280',
            'OLED',
            'GPIO 21',
            'GPIO 22',
            'FS28_bme280_oled',
            'Library Manager',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 98 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 98 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 98 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle99Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article99Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 99 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-wifi-dari-nol';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 99 missing after draft seed.'));

            return response('Article 99 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 99 refused to stay draft after seed.'));

            return response('Article 99 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 99 unexpectedly visible via published() scope.'));

            return response('Article 99 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#99 (ini)',
            'FS-29',
            'CONNECTED',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-wifi-checklist',
            'FS-28',
            'FS-19',
            'FS-14',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'WiFi.begin',
            '2,4 GHz',
            'FS29_wifi_begin',
            'WL_CONNECTED',
            'fs29-wifi-station.png',
            'Gambar utama',
            'Skema bantu',
            'Buka Arduino IDE dulu',
            '115200',
            'YOUR_SSID',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 99 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 99 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 99 English fields are incomplete after draft seed.'));

            return response('Article 99 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#99 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'CONNECTED',
            'fsiot-wifi-checklist',
            'FS-28',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs29-wifi-station.png',
            'Open Arduino IDE first',
            'WiFi.begin',
            '2.4 GHz',
            'FS29_wifi_begin',
            'WL_CONNECTED',
            '115200',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 99 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 99 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 99 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle100Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article100Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 100 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-http-json';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 100 missing after draft seed.'));

            return response('Article 100 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Article 100 refused to stay draft after seed.'));

            return response('Article 100 must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 100 unexpectedly visible via published() scope.'));

            return response('Article 100 leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            '#100 (ini)',
            'FS-30',
            'CONNECTED',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'fsiot-http-checklist',
            'FS-29',
            'FS-14',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Cara menguji perintah di atas',
            'HTTPClient',
            'FS30_http_get',
            'jsonplaceholder',
            'fs30-http-get.png',
            'Gambar utama',
            'Skema bantu',
            'Buka browser dulu',
            '115200',
            'YOUR_SSID',
            'HTTP 200',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Article 100 body missing expected content after draft seed: '.implode(', ', $missingBody)));

            return response('Article 100 body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 100 English fields are incomplete after draft seed.'));

            return response('Article 100 EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            '#100 (this article)',
            'Analogy:',
            'How to use this article',
            'Not needed today',
            'CONNECTED',
            'fsiot-http-checklist',
            'FS-29',
            'Common mistakes',
            'How to test the commands above',
            'Main figure',
            'Helper schematic',
            'fs30-http-get.png',
            'Open a browser first',
            'HTTPClient',
            'FS30_http_get',
            'jsonplaceholder',
            '115200',
            'HTTP 200',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 100 EN body missing expected content after draft seed: '.implode(', ', $missingEn)));

            return response('Article 100 EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 100 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle101Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article101Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 101 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-web-server-lokal-sensor';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Article 101 missing after draft seed.'));

            return response('Article 101 not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null || Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Article 101 refused to stay private draft after seed.'));

            return response('Article 101 must remain draft (pre-launch B)', 500);
        }

        $requiredId = [
            '#101 (ini)', 'FS-31', 'CONNECTED', 'WebServer', 'DHT22', 'FS31_web_server_suhu',
            'WiFi.localIP', 'server.handleClient', '115200', 'YOUR_SSID', 'YOUR_PASS',
            'Cara menguji perintah di atas', 'Gambar utama', 'Skema bantu',
            'fsiot-webserver-checklist', 'localhost', '/belajar/fullstack-iot',
        ];
        $missingId = array_values(array_filter($requiredId, fn (string $needle): bool => ! str_contains((string) $article->body, $needle)));
        if ($missingId !== []) {
            report(new \RuntimeException('Article 101 ID body missing expected content: '.implode(', ', $missingId)));

            return response('Article 101 ID content checks failed: '.implode(', ', $missingId), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            report(new \RuntimeException('Article 101 English fields are incomplete after draft seed.'));

            return response('Article 101 EN fields incomplete', 500);
        }

        $requiredEn = [
            '#101 (this article)', 'FS-31', 'CONNECTED', 'WebServer', 'DHT22', 'FS31_web_server_suhu',
            'WiFi.localIP', 'server.handleClient', '115200', 'YOUR_SSID', 'YOUR_PASS',
            'How to test the commands above', 'Main figure', 'Helper schematic',
            'fsiot-webserver-checklist', 'localhost', '/belajar/fullstack-iot',
        ];
        $missingEn = array_values(array_filter($requiredEn, fn (string $needle): bool => ! str_contains((string) $article->body_en, $needle)));
        if ($missingEn !== []) {
            report(new \RuntimeException('Article 101 EN body missing expected content: '.implode(', ', $missingEn)));

            return response('Article 101 EN content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Article 101 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle102Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article102Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 102 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-mqtt-broker-topic-publish-subscribe';
        $article = Article::where('slug', $slug)->first();
        if (! $article || $article->status !== 'draft' || $article->published_at !== null || Article::published()->where('slug', $slug)->exists()) {
            return response('Article 102 must remain draft (pre-launch B)', 500);
        }

        $requiredId = ['#102 (ini)', 'FS-32', 'MQTTX', 'broker', 'topic', 'publish', 'subscribe', 'FS-33', 'localhost'];
        $missingId = array_values(array_filter($requiredId, fn (string $needle): bool => ! str_contains((string) $article->body, $needle)));
        if ($missingId !== []) {
            return response('Article 102 ID content checks failed: '.implode(', ', $missingId), 500);
        }

        $requiredEn = ['#102 (this article)', 'FS-32', 'MQTTX', 'broker', 'topic', 'publish', 'subscribe', 'FS-33', 'localhost'];
        $missingEn = array_values(array_filter($requiredEn, fn (string $needle): bool => ! str_contains((string) $article->body_en, $needle)));
        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en) || $missingEn !== []) {
            return response('Article 102 EN content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');

        return response('Article 102 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle103Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article103Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 103 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-mosquitto-broker-lokal-mqttx';
        $article = Article::where('slug', $slug)->first();
        if (! $article || $article->status !== 'draft' || $article->published_at !== null || Article::published()->where('slug', $slug)->exists()) {
            return response('Article 103 must remain draft (pre-launch B)', 500);
        }

        $requiredId = ['#103 (ini)', 'FS-33', 'Mosquitto', 'MQTTX', '127.0.0.1', '1883', 'PowerShell', 'FS-34'];
        $missingId = array_values(array_filter($requiredId, fn (string $needle): bool => ! str_contains((string) $article->body, $needle)));
        if ($missingId !== []) {
            return response('Article 103 ID content checks failed: '.implode(', ', $missingId), 500);
        }

        $requiredEn = ['#103 (this article)', 'FS-33', 'Mosquitto', 'MQTTX', '127.0.0.1', '1883', 'PowerShell', 'FS-34'];
        $missingEn = array_values(array_filter($requiredEn, fn (string $needle): bool => ! str_contains((string) $article->body_en, $needle)));
        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en) || $missingEn !== []) {
            return response('Article 103 EN content checks failed: '.implode(', ', $missingEn), 500);
        }

        if (! str_contains((string) $article->cover_image, 'fs33-cover-mosquitto')) {
            return response('Article 103 cover check failed', 500);
        }

        Artisan::call('view:clear');

        return response('Article 103 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle104Draft(): Response
    {
        $this->authorizeDeployHook();

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article104Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 104 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-esp32-dht22-mqtt-json-telemetry';
        $article = Article::where('slug', $slug)->first();
        if (! $article || $article->status !== 'draft' || $article->published_at !== null || Article::published()->where('slug', $slug)->exists()) {
            return response('Article 104 must remain draft (pre-launch B)', 500);
        }

        $requiredId = ['#104 (ini)', 'FS-34', 'DHT22', 'ArduinoMqttClient', 'ArduinoJson', 'MQTT_HOST', 'ipconfig', 'listener_allow_anonymous', 'FS-35'];
        $missingId = array_values(array_filter($requiredId, fn (string $needle): bool => ! str_contains((string) $article->body, $needle)));
        if ($missingId !== []) {
            return response('Article 104 ID content checks failed: '.implode(', ', $missingId), 500);
        }

        $requiredEn = ['#104 (this article)', 'FS-34', 'DHT22', 'ArduinoMqttClient', 'ArduinoJson', 'MQTT_HOST', 'ipconfig', 'listener_allow_anonymous', 'FS-35'];
        $missingEn = array_values(array_filter($requiredEn, fn (string $needle): bool => ! str_contains((string) $article->body_en, $needle)));
        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en) || $missingEn !== []) {
            return response('Article 104 EN content checks failed: '.implode(', ', $missingEn), 500);
        }

        if (! str_contains((string) $article->cover_image, 'fs34-cover-telemetry')) {
            return response('Article 104 cover check failed', 500);
        }

        Artisan::call('view:clear');

        return response('Article 104 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle105Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article105Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 105 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-esp32-mqtt-command-relay';
        $article = Article::where('slug', $slug)->first();
        if (! $article || $article->status !== 'draft' || $article->published_at !== null || Article::published()->where('slug', $slug)->exists()) {
            return response('Article 105 must remain draft (pre-launch B)', 500);
        }

        $requiredId = ['#105 (ini)', 'FS-35', 'GPIO 26', 'ArduinoMqttClient', 'ArduinoJson', 'MQTT_HOST', 'ipconfig', 'listener_allow_anonymous', 'Subscribe command siap.', 'FS-36'];
        $missingId = array_values(array_filter($requiredId, fn (string $needle): bool => ! str_contains((string) $article->body, $needle)));
        if ($missingId !== []) {
            return response('Article 105 ID content checks failed: '.implode(', ', $missingId), 500);
        }

        $requiredEn = ['#105 (this article)', 'FS-35', 'GPIO 26', 'ArduinoMqttClient', 'ArduinoJson', 'MQTT_HOST', 'ipconfig', 'listener_allow_anonymous', 'Subscribe command siap.', 'FS-36'];
        $missingEn = array_values(array_filter($requiredEn, fn (string $needle): bool => ! str_contains((string) $article->body_en, $needle)));
        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en) || $missingEn !== []) {
            return response('Article 105 EN content checks failed: '.implode(', ', $missingEn), 500);
        }

        if (! str_contains((string) $article->cover_image, 'fs35-cover-command')) {
            return response('Article 105 cover check failed', 500);
        }

        Artisan::call('view:clear');

        return response('Article 105 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle106Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article106Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 106 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-esp32-microsd-log-csv';
        $article = Article::where('slug', $slug)->first();
        if (! $article || $article->status !== 'draft' || $article->published_at !== null || Article::published()->where('slug', $slug)->exists()) {
            return response('Article 106 must remain draft (pre-launch B)', 500);
        }

        $requiredId = ['#106 (ini)', 'FS-36', 'GPIO 5', 'FAT32', 'log.csv', 'PAKAI_NTP', 'Kartu siap. Menulis /log.csv', 'FS-37'];
        $missingId = array_values(array_filter($requiredId, fn (string $needle): bool => ! str_contains((string) $article->body, $needle)));
        if ($missingId !== []) {
            return response('Article 106 ID content checks failed: '.implode(', ', $missingId), 500);
        }

        $requiredEn = ['#106 (this article)', 'FS-36', 'GPIO 5', 'FAT32', 'log.csv', 'PAKAI_NTP', 'Kartu siap. Menulis /log.csv', 'FS-37'];
        $missingEn = array_values(array_filter($requiredEn, fn (string $needle): bool => ! str_contains((string) $article->body_en, $needle)));
        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en) || $missingEn !== []) {
            return response('Article 106 EN content checks failed: '.implode(', ', $missingEn), 500);
        }

        if (! str_contains((string) $article->cover_image, 'fs36-cover-sd')) {
            return response('Article 106 cover check failed', 500);
        }

        Artisan::call('view:clear');

        return response('Article 106 seeded as draft (pre-launch B)', 200);
    }

    public function seedArticle107Draft(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Article107Seeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Article 107 draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-esp32-sd-store-and-forward';
        $article = Article::where('slug', $slug)->first();
        if (! $article || $article->status !== 'draft' || $article->published_at !== null || Article::published()->where('slug', $slug)->exists()) {
            return response('Article 107 must remain draft (pre-launch B)', 500);
        }

        $requiredId = ['#107 (ini)', 'FS-37', 'pending.csv', 'from_sd', 'Kartu siap. Antrian di /pending.csv', 'Kirim ulang dari kartu:', 'FS-38'];
        $missingId = array_values(array_filter($requiredId, fn (string $needle): bool => ! str_contains((string) $article->body, $needle)));
        if ($missingId !== []) {
            return response('Article 107 ID content checks failed: '.implode(', ', $missingId), 500);
        }

        $requiredEn = ['#107 (this article)', 'FS-37', 'pending.csv', 'from_sd', 'Kartu siap. Antrian di /pending.csv', 'Kirim ulang dari kartu:', 'FS-38'];
        $missingEn = array_values(array_filter($requiredEn, fn (string $needle): bool => ! str_contains((string) $article->body_en, $needle)));
        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en) || $missingEn !== []) {
            return response('Article 107 EN content checks failed: '.implode(', ', $missingEn), 500);
        }

        if (! str_contains((string) $article->cover_image, 'fs37-cover-forward')) {
            return response('Article 107 cover check failed', 500);
        }

        Artisan::call('view:clear');

        return response('Article 107 seeded as draft (pre-launch B)', 200);
    }

    public function seedGateBuilderDraft(): Response
    {
        $this->authorizeDeployHook();

        // Avoid stale OPcache after FTP/curl (new seeder vs old needle checks).
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        try {
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\ArticleGateBuilderSeeder',
                '--force' => true,
            ]);
        } catch (\Throwable $e) {
            report($e);

            return response('Gate BUILDER draft seed failed: '.$e->getMessage(), 500);
        }

        $slug = 'fullstack-iot-gate-builder';
        $article = Article::where('slug', $slug)->first();
        if (! $article) {
            report(new \RuntimeException('Gate BUILDER missing after draft seed.'));

            return response('Gate BUILDER not found after draft seed', 500);
        }

        if ($article->status !== 'draft' || $article->published_at !== null) {
            report(new \RuntimeException('Gate BUILDER refused to stay draft after seed.'));

            return response('Gate BUILDER must remain draft (pre-launch B)', 500);
        }

        if (Article::published()->where('slug', $slug)->exists()) {
            report(new \RuntimeException('Gate BUILDER unexpectedly visible via published() scope.'));

            return response('Gate BUILDER leaked into published scope', 500);
        }

        $body = (string) $article->body;
        $bodyNeedles = [
            'Gate BUILDER (ini)',
            'CONNECTED',
            'fsiot-kuis-matching',
            'fsiot-kuis-kunci',
            'fsiot-gate-builder-checklist',
            '12/15',
            'Tidak perlu hari ini',
            'Cara pakai artikel ini',
            'Cara menguji',
            'FS-28',
            'FS-29',
            '/belajar/fullstack-iot',
            'Analogi:',
            'Intinya:',
            'Kesalahan yang sering terjadi',
            'Buka browser',
            'Histeresis',
            'fs-gate-builder-criteria.png',
            'fs-gate-builder-relay-contacts.png',
            'fs-gate-builder-wiring-example.png',
            'Gambar utama',
            'Ringkasnya:',
            'kotak kuis interaktif',
            'COM / NO / NC',
            'data-timer-seconds="720"',
            'Batas waktu 12 menit',
        ];
        $missingBody = array_values(array_filter($bodyNeedles, fn (string $needle): bool => ! str_contains($body, $needle)));
        if ($missingBody !== []) {
            report(new \RuntimeException('Gate BUILDER body missing: '.implode(', ', $missingBody)));

            return response('Gate BUILDER body content checks failed: '.implode(', ', $missingBody), 500);
        }

        if (! filled($article->title_en) || ! filled($article->body_en) || ! filled($article->seo_title_en) || ! filled($article->seo_description_en)) {
            return response('Gate BUILDER EN fields incomplete', 500);
        }

        $bodyEn = (string) $article->body_en;
        $enNeedles = [
            'BUILDER gate (this article)',
            'CONNECTED',
            'fsiot-kuis-matching',
            'fsiot-kuis-kunci',
            'fsiot-gate-builder-checklist',
            '12/15',
            'Not needed today',
            'How to use this article',
            'How to test',
            'FS-28',
            'FS-29',
            'Analogy:',
            'In short:',
            'Common mistakes',
            'Open a browser',
            'Hysteresis',
            'Main figure',
            'fs-gate-builder-criteria.png',
            'fs-gate-builder-relay-contacts.png',
            'fs-gate-builder-wiring-example.png',
            'interactive quiz box',
            'COM / NO / NC',
            'data-timer-seconds="720"',
            '12-minute limit',
        ];
        $missingEn = array_values(array_filter($enNeedles, fn (string $needle): bool => ! str_contains($bodyEn, $needle)));
        if ($missingEn !== []) {
            return response('Gate BUILDER EN body content checks failed: '.implode(', ', $missingEn), 500);
        }

        Artisan::call('view:clear');
        Artisan::call('route:clear');
        Artisan::call('config:clear');

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        return response('Gate BUILDER seeded as draft (pre-launch B)', 200);
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
        $this->authorizeDeployHook();

        return $this->publishArticle('Article9Seeder', 'Article 9 published');
    }

    public function publishArticle8(): Response
    {
        $this->authorizeDeployHook();

        return $this->publishArticle('Article8Seeder', 'Article 8 published');
    }

    public function publishArticle7(): Response
    {
        $this->authorizeDeployHook();

        return $this->publishArticle('Article7Seeder', 'Article 7 published');
    }

    public function publishArticle6(): Response
    {
        $this->authorizeDeployHook();

        return $this->publishArticle('Article6Seeder', 'Article 6 published');
    }

    public function publishArticle5(): Response
    {
        $this->authorizeDeployHook();

        return $this->publishArticle('Article5Seeder', 'Article 5 published');
    }

    public function publishArticle4(): Response
    {
        $this->authorizeDeployHook();

        return $this->publishArticle('Article4Seeder', 'Article 4 published');
    }

    public function publishArticle3(): Response
    {
        $this->authorizeDeployHook();

        return $this->publishArticle('Article3Seeder', 'Article 3 published');
    }

    public function publishArticle2(): Response
    {
        $this->authorizeDeployHook();

        return $this->publishArticle('Article2Seeder', 'Article 2 published');
    }

    public function publishArticle1(): Response
    {
        $this->authorizeDeployHook();

        return $this->publishArticle('Article1Seeder', 'Article 1 published');
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

    private function refreshLaravelSeriesExcerpts(): void
    {
        if (! $this->ensureSeederClass('database/seeders/RefreshLaravelSeriesExcerptsSeeder.php', \Database\Seeders\RefreshLaravelSeriesExcerptsSeeder::class)) {
            report(new \RuntimeException('RefreshLaravelSeriesExcerptsSeeder class not found — excerpt refresh skipped.'));

            return;
        }

        Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\RefreshLaravelSeriesExcerptsSeeder',
            '--force' => true,
        ]);
    }

    private function ensureSeederClass(string $relativePath, string $class): bool
    {
        $seederPath = base_path($relativePath);
        clearstatcache(true, $seederPath);
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($seederPath, true);
        }
        if (! class_exists($class) && is_readable($seederPath)) {
            require_once $seederPath;
        }

        return class_exists($class);
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

    /**
     * Patch formatting artikel #39 (tabel artikel + FAQ terpisah) — tanpa re-seed artikel lain.
     */
    public function patchArticle39Formatting(): Response
    {
        $this->authorizeDeployHook();

        if (function_exists('opcache_reset')) {
            opcache_reset();
        }

        if (! class_exists(\Database\Seeders\PatchArticle39FormattingSeeder::class)) {
            return response('PatchArticle39FormattingSeeder class not found on server', 500);
        }

        $exitCode = Artisan::call('db:seed', [
            '--class' => 'Database\\Seeders\\PatchArticle39FormattingSeeder',
            '--force' => true,
        ]);

        if ($exitCode !== 0) {
            return response('Article 39 formatting patch failed', 500);
        }

        try {
            app(SitemapService::class)->writeToDisk();
        } catch (\Throwable $e) {
            report($e);
        }

        Artisan::call('view:clear');

        return response('Article 39 formatting patched', 200);
    }

    /**
     * Sanitize + normalize body image URLs, then mirror cover/body files into PUBLIC_HTML_STORAGE.
     * Fixes contributor uploads that exist on disk but 404 via /storage/... URLs.
     */
    public function remirrorArticleImages(): JsonResponse
    {
        $this->authorizeDeployHook();

        if (! config('filesystems.public_html_storage')) {
            return response()->json([
                'ok' => false,
                'error' => 'PUBLIC_HTML_STORAGE is not configured',
            ], 500);
        }

        $mirror = app(\App\Services\PublicHtmlStorageMirror::class);
        $sanitizer = app(\App\Services\ArticleHtmlSanitizer::class);
        $covers = 0;
        $bodyFiles = 0;
        $bodiesNormalized = 0;
        $articlesScanned = 0;
        $avatars = 0;

        Article::query()
            ->select(['id', 'slug', 'cover_image', 'body'])
            ->orderBy('id')
            ->chunkById(50, function ($articles) use ($mirror, $sanitizer, &$covers, &$bodyFiles, &$bodiesNormalized, &$articlesScanned) {
                foreach ($articles as $article) {
                    $articlesScanned++;

                    if ($article->cover_image && $mirror->mirror($article->cover_image)) {
                        $covers++;
                    }

                    $original = (string) ($article->body ?? '');
                    $cleaned = $sanitizer->sanitize($original);

                    if ($cleaned !== $original) {
                        $article->updateQuietly(['body' => $cleaned]);
                        $bodiesNormalized++;
                    }

                    $bodyFiles += $mirror->mirrorPathsFromHtml($cleaned);
                }
            });

        User::query()
            ->whereNotNull('avatar')
            ->where('avatar', '!=', '')
            ->select(['id', 'avatar'])
            ->orderBy('id')
            ->chunkById(50, function ($users) use ($mirror, &$avatars) {
                foreach ($users as $user) {
                    if ($mirror->mirror($user->avatar)) {
                        $avatars++;
                    }
                }
            });

        Artisan::call('view:clear');

        return response()->json([
            'ok' => true,
            'articles_scanned' => $articlesScanned,
            'covers_mirrored' => $covers,
            'bodies_normalized' => $bodiesNormalized,
            'body_files_mirrored' => $bodyFiles,
            'avatars_mirrored' => $avatars,
        ]);
    }

    /**
     * Clamp contributor/admin SEO fields that exceed Google snippet limits.
     */
    public function clampArticleSeoFields(): JsonResponse
    {
        $this->authorizeDeployHook();

        $result = \App\Support\ArticleSeoLimits::clampAllArticles();

        Artisan::call('view:clear');

        return response()->json([
            'ok' => true,
            ...$result,
        ]);
    }

    private function authorizeDeployHook(): void
    {
        $token = config('app.deploy_hook_token');
        $provided = request()->header('X-Deploy-Token');

        if (empty($token) || ! is_string($provided) || ! hash_equals($token, $provided)) {
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
