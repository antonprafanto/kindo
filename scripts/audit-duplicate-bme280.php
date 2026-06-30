<?php

/**
 * Verifikasi duplikat BME280 sudah dibersihkan.
 * Usage: php scripts/audit-duplicate-bme280.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\RemoveDuplicateBme280Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Audit Duplikat BME280 ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$canonical = Article::where('slug', RemoveDuplicateBme280Seeder::CANONICAL_SLUG)->first();
$duplicate = Article::withTrashed()->where('slug', RemoveDuplicateBme280Seeder::DUPLICATE_SLUG)->first();

check($canonical !== null, 'Artikel canonical ada');
check($canonical?->status === 'published', 'Canonical published');
check($duplicate === null || $duplicate->trashed(), 'Duplikat tidak aktif (soft-deleted atau hilang)');
check(
    str_contains($canonical?->body ?? '', 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'),
    'Canonical punya link ke artikel #14 OLED'
);

$redirects = config('article-redirects', []);
check(
    ($redirects[RemoveDuplicateBme280Seeder::DUPLICATE_SLUG] ?? null) === RemoveDuplicateBme280Seeder::CANONICAL_SLUG,
    'Config 301 redirect duplikat → canonical'
);

if ($checkProduction) {
    echo "\n=== Production ===\n\n";
    $dupUrl = 'https://kodingindonesia.com/artikel/' . RemoveDuplicateBme280Seeder::DUPLICATE_SLUG;
    $ch = curl_init($dupUrl);
    curl_setopt_array($ch, [
        CURLOPT_NOBODY => true,
        CURLOPT_FOLLOWLOCATION => false,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 30,
    ]);
    curl_exec($ch);
    $code = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $location = (string) curl_getinfo($ch, CURLINFO_REDIRECT_URL);
    curl_close($ch);

    check($code === 301, "Duplikat URL HTTP 301 (got {$code})");
    check(str_contains($location, RemoveDuplicateBme280Seeder::CANONICAL_SLUG), 'Redirect ke canonical');

    $sitemap = (string) @file_get_contents('https://kodingindonesia.com/sitemap.xml');
    check(! str_contains($sitemap, RemoveDuplicateBme280Seeder::DUPLICATE_SLUG), 'Duplikat tidak di sitemap');
    check(str_contains($sitemap, RemoveDuplicateBme280Seeder::CANONICAL_SLUG), 'Canonical ada di sitemap');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
