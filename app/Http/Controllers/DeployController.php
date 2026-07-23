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
        if (! str_contains($body, 'oop53phpArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'oop_php_dasar.php') || ! str_contains($body, 'class Buku') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#53 (ini)') || ! str_contains($body, '6/8 menuju Capstone Laravel') || ! str_contains($body, 'type hint') || ! str_contains($body, 'oop-php-property-method-constructor')) {
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
        if (! str_contains($body, 'oop54phpArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'oop_php_property.php') || ! str_contains($body, 'class Buku') || ! str_contains($body, '__construct') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#54 (ini)') || ! str_contains($body, '6/8 menuju Capstone Laravel') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'oop-php-visibility-composition')) {
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
        if (! str_contains($body, 'oop55phpArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'oop_php_visibility.php') || ! str_contains($body, 'class Buku') || ! str_contains($body, 'class Katalog') || ! str_contains($body, 'private') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#55 (ini)') || ! str_contains($body, '6/8 menuju Capstone Laravel') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'laravel-routing-json-perpustakaan-api')) {
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

        $slug = 'laravel-routing-json-perpustakaan-api';
        $prevSlug = 'oop-php-visibility-composition';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 56 missing or not visible after Article56Seeder on deploy hook.'));

            return response('Article 56 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel56jsonArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_routing_json_demo.php') || ! str_contains($body, 'response()-&gt;json') || ! str_contains($body, 'json_encode') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#56 (ini)') || ! str_contains($body, '6/8 menuju Capstone Laravel') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'Developer Tools') || ! str_contains($body, 'merapikan daftar') || ! str_contains($body, 'Pakai') || ! str_contains($body, 'laravel-request-validasi-api')) {
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

        $slug = 'laravel-request-validasi-api';
        $prevSlug = 'laravel-routing-json-perpustakaan-api';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 57 missing or not visible after Article57Seeder on deploy hook.'));

            return response('Article 57 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel57reqArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_request_validasi_demo.php') || ! str_contains($body, 'validasiBuku') || ! str_contains($body, 'FormRequest') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#57 (ini)') || ! str_contains($body, '6/8 menuju Capstone Laravel') || ! str_contains($body, $prevSlug) || ! str_contains($body, '422') || ! str_contains($body, 'pengatur kode') || ! str_contains($body, 'laravel-controller-service-eloquent') || ! str_contains($body, 'sering disebut frontend')) {
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

        $slug = 'laravel-controller-service-eloquent';
        $prevSlug = 'laravel-request-validasi-api';

        $article = Article::published()->where('slug', $slug)->first();

        if (! $article) {
            report(new \RuntimeException('Article 58 missing or not visible after Article58Seeder on deploy hook.'));

            return response('Article 58 seed incomplete', 500);
        }

        $body = (string) $article->body;
        if (! str_contains($body, 'laravel58ctrlArrow') || ! str_contains($body, 'color:#1a1a1a') || ! str_contains($body, 'laravel_controller_service_demo.php') || ! str_contains($body, 'BukuService') || ! str_contains($body, 'BukuController') || ! str_contains($body, 'Eloquent') || ! str_contains($body, 'demo(') || ! str_contains($body, 'Seri 4') || ! str_contains($body, '#58 (ini)') || ! str_contains($body, '6/8 menuju Capstone Laravel') || ! str_contains($body, $prevSlug) || ! str_contains($body, 'pengatur kode') || ! str_contains($body, 'otentikasi') || ! str_contains($body, 'langkah kerja') || ! str_contains($body, 'validated()') || ! str_contains($body, 'JsonResponse') || ! str_contains($body, 'callable') || ! str_contains($body, 'Perintah database tersebar')) {
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

    public function publishArticle5(): Response
    {
        return $this->publishArticle('Article5Seeder', 'Article 5 published');
    }

    public function publishArticle4(): Response
    {
        return $this->publishArticle('Article4Seeder', 'Article 4 published');
    }

    public function publishArticle3(): Response
    {
        return $this->publishArticle('Article3Seeder', 'Article 3 published');
    }

    public function publishArticle2(): Response
    {
        return $this->publishArticle('Article2Seeder', 'Article 2 published');
    }

    public function publishArticle1(): Response
    {
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
