<?php

/**
 * Audit artikel #38 — HTTPS & sertifikat ESP32.
 * Usage: php scripts/audit-article38.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article38Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'https-sertifikat-esp32-wificlientsecure-api-rest';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article38Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #38 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article38Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle17HttpsSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle36HttpsSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle37HttpsSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'networking', 'Kategori networking');
check($article?->is_featured === false, 'is_featured false');

$requiredTags = ['esp32', 'tls', 'networking', 'iot', 'wifi', 'https'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'blink-led-esp32-tutorial-pertama-embedded-system'             => 'Artikel #3 Blink',
    'menghubungkan-esp32-wifi-kirim-data-server'                   => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                   => 'Artikel #5 DHT22',
    'membuat-web-server-esp32-monitoring-sensor-dht22'             => 'Artikel #6 Web server',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => 'Artikel #7 MQTT',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => 'Artikel #10 Capstone',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                  => 'Artikel #11 Deep sleep',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 Mosquitto',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                    => 'Artikel #17 MQTT TLS',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'          => 'Artikel #18 Python',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'           => 'Artikel #19 Grafana',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => 'Artikel #20 REST vs MQTT',
    'home-assistant-integrasi-esp32-mqtt'                            => 'Artikel #21 Home Assistant',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => 'Artikel #23 Node-RED',
    'esp32-firebase-realtime-database-sensor-cloud'                  => 'Artikel #30 Firebase',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => 'Artikel #34 NTP',
    'sd-card-spi-esp32-logging-data-sensor-offline'                  => 'Artikel #37 SD Card',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(str_contains($body, 'WiFiClientSecure'), 'Include WiFiClientSecure');
check(str_contains($body, 'HTTPClient'), 'Include HTTPClient');
check(str_contains($body, 'setCACert'), 'setCACert disebut');
check(str_contains($body, 'setInsecure'), 'Peringatan setInsecure');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder WiFi SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(str_contains($body, '1782977400'), 'Contoh unix konsisten #34');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'language-cpp'), 'Blok C++');
check(str_contains($body, 'language-ini'), 'Blok platformio.ini');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(str_contains($body, 'Estimasi Biaya'), 'Estimasi biaya');
check(str_contains($body, '#39') || str_contains($body, 'greenhouse'), 'Teaser capstone #39');
check(str_contains($body, 'Checklist'), 'Section checklist');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 14, "Minimal 14 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 8, 'read_time_minutes ≥ 8 menit');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'HTTPS') && str_contains($html, 'WiFiClientSecure'), 'Konten HTTPS ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'setCACert') || str_contains($html, 'HTTPClient'), 'Kode TLS ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 (backlink) ===\n\n";

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22' => '#10',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'       => '#17',
    'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade'    => '#36',
    'sd-card-spi-esp32-logging-data-sensor-offline'   => '#37',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Artikel {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #38");
}

echo "\n=== Post-deploy (manual) ===\n";
echo "○ Upload cover image via Filament (daftar artikel → Upload Cover)\n";

if ($checkProduction) {
    echo "\n=== Pass 4: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $code = trim((string) shell_exec('curl -sS --max-time 30 -o NUL -w "%{http_code}" ' . escapeshellarg($prodUrl)));
    check($code === '200', "Production HTTP {$code}");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
