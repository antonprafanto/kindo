<?php

/**
 * Audit artikel #6 — Web Server ESP32 + DHT22.
 * Usage: php scripts/audit-article6.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article6Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'membuat-web-server-esp32-monitoring-sensor-dht22';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article6Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #6 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article6Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['esp32', 'iot', 'wifi', 'sensor', 'api'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'menghubungkan-esp32-wifi-kirim-data-server'           => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'           => 'Artikel #5 DHT22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'         => 'Artikel #7 MQTT',
    'kontrol-lampu-esp32-mqtt-relay'                       => 'Artikel #8 relay',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'     => 'Artikel #10 dashboard',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'        => 'Artikel #20 REST vs MQTT',
    'home-assistant-integrasi-esp32-mqtt'                  => 'Artikel #21 HA',
    'esp32-cam-streaming-mjpeg-capture-foto-wifi'          => 'Artikel #27 ESP32-CAM',
    'https-sertifikat-esp32-wificlientsecure-api-rest'     => 'Artikel #38 HTTPS',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'            => 'Artikel #17 TLS',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, '<svg'), 'SVG diagram alur ada');
check(str_contains($body, 'ESP32 WebServer :80'), 'SVG: teks ESP32 WebServer :80');
check(str_contains($body, 'GET /api/data'), 'SVG: label GET /api/data');
check(str_contains($body, 'Browser HP / Laptop'), 'SVG: Browser HP / Laptop');
check(str_contains($body, 'WiFi (#4)</a>'), 'Hyperlink WiFi #4 di Pendahuluan');
check(str_contains($body, 'DHT22 (#5)</a>'), 'Hyperlink DHT22 #5 di Pendahuluan');
check(str_contains($body, 'tutorial DHT22 (#5)</a>'), 'Hyperlink #5 di Wiring');
check(str_contains($body, 'Home Assistant (#21)</a>'), 'Hyperlink HA #21');
check(str_contains($body, 'MQTT (#7)</a>'), 'Hyperlink MQTT #7 di Langkah Selanjutnya');
check(str_contains($body, 'relay (#8)</a>'), 'Hyperlink relay #8');
check(str_contains($body, 'Web Server + MQTT (#10)</a>'), 'Hyperlink dashboard #10');
check(str_contains($body, 'HTTPS (#38)</a>'), 'Hyperlink HTTPS #38 di keamanan');

check(str_contains($body, 'WebServer'), 'Menyebut WebServer');
check(str_contains($body, 'language-arduino'), 'Blok kode Arduino');
check(str_contains($body, 'language-json'), 'Blok JSON API');
check(str_contains($body, '/api/data'), 'Endpoint /api/data');
check(str_contains($body, 'server.handleClient()'), 'server.handleClient()');
check(str_contains($body, 'intervalBaca'), 'Interval baca DHT22');
check(str_contains($body, 'NamaWiFiKamu'), 'Placeholder SSID');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 8, "Minimal 8 section H2 (ada {$h2Count})");

// Pastikan SVG lolos sanitizer (regresi issue #22)
$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(str_contains($sanitized, '<svg'), 'SVG bertahan setelah ArticleHtmlSanitizer');
check(str_contains($article?->body ?? '', '<svg'), 'SVG ada di body database setelah seed');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(
    Illuminate\Http\Request::create('/artikel/' . $slug, 'GET')
);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'WebServer'), 'Konten WebServer ter-render');
check(str_contains($html, '<svg'), 'SVG ter-render di HTML');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');

echo "\n=== Pass 3: Konsistensi Seri ===\n\n";

check(str_contains($body, 'esp32-cam-streaming-mjpeg-capture-foto-wifi'), 'Backlink ke #27 ESP32-CAM');
check(! str_contains($body, 'Artikel #27'), 'Tidak ada teks orphan Artikel #27');

if ($checkProduction) {
    echo "\n=== Pass 4: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $code = trim((string) shell_exec('curl -sS --max-time 30 -o NUL -w "%{http_code}" ' . escapeshellarg($prodUrl)));
    check($code === '200', "Production HTTP {$code}");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
