<?php

/**
 * Audit artikel #22 — ESPHome flash ESP32 tanpa Arduino.
 * Usage: php scripts/audit-article22.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article22Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'esphome-flash-esp32-tanpa-coding-arduino';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article22Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #22 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article22Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article21Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article15Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article9Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article8Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article6Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['esp32', 'esphome', 'homeassistant', 'mqtt', 'iot', 'smarthome', 'relay'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'home-assistant-integrasi-esp32-mqtt'                          => 'Artikel #21 Home Assistant',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                     => 'Artikel #5 DHT22',
    'kontrol-lampu-esp32-mqtt-relay'                                 => 'Artikel #8 relay',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek'                   => 'Artikel #9 gabungan',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'ota-update-firmware-esp32-via-wifi'                              => 'Artikel #15 OTA',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'   => 'Artikel #12 WiFiManager',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'ESPHome'), 'Menyebut ESPHome');
check(str_contains($body, 'Jalur C'), 'Menyebut Jalur C');
check(str_contains($body, 'language-yaml'), 'Blok kode YAML');
check(str_contains($body, 'secrets.yaml'), 'File secrets.yaml');
check(str_contains($body, 'Native API'), 'Menjelaskan Native API');
check(str_contains($body, 'GPIO4'), 'Pin DHT22 GPIO4');
check(str_contains($body, 'GPIO26'), 'Pin relay GPIO26');
check(str_contains($body, 'inverted: true'), 'Relay active LOW inverted');
check(str_contains($body, 'captive_portal'), 'Captive portal fallback WiFi');
check(str_contains($body, 'api_encryption_key'), 'API encryption key');
check(str_contains($body, 'Alur data secara singkat'), 'Diagram alur vertikal');
check(str_contains($body, 'Arduino Sketch vs ESPHome'), 'Tabel perbandingan Arduino vs ESPHome');
check(str_contains($body, 'discovery: false'), 'MQTT discovery false opsional');
check(str_contains($body, 'test.mosquitto.org'), 'Peringatan broker publik');
check(str_contains($body, 'numeric_state'), 'Automasi numeric_state');
check(str_contains($body, 'device_class'), 'device_class sensor');
check(str_contains($body, 'Artikel #23'), 'Teaser Node-RED #23');
check(str_contains($body, 'Artikel #24'), 'Teaser PIR #24');
check(str_contains($body, 'Artikel #17'), 'Teaser MQTT TLS #17');
check(str_contains($body, 'Keamanan &amp; Produksi'), 'Section Keamanan & Produksi');
check(str_contains($body, 'Pro tip'), 'Pro tip friendly_name');
check(str_contains($body, 'greenhouse'), 'Teaser capstone #39');
check(str_contains($body, 'Seri 2'), 'Menyebut Seri 2');
check(str_contains($body, '2.4 GHz'), 'Troubleshooting WiFi 2.4 GHz');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 12, "Minimal 12 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 5, 'read_time_minutes ≥ 5 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(
    Illuminate\Http\Request::create('/artikel/' . $slug, 'GET')
);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'ESPHome'), 'Konten ESPHome ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'kindo-esp32-node'), 'YAML config ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a21 = Article::where('slug', 'home-assistant-integrasi-esp32-mqtt')->first();
check($a21 !== null, 'Artikel #21 ada');
check(str_contains($a21?->body ?? '', 'esphome-flash-esp32-tanpa-coding-arduino'), 'Artikel #21 backlink → #22');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check($a10 !== null, 'Artikel #10 ada');
check(str_contains($a10?->body ?? '', 'esphome-flash-esp32-tanpa-coding-arduino'), 'Artikel #10 indeks → #22');

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
check($a16 !== null, 'Artikel #16 ada');

$a9 = Article::where('slug', 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek')->first();
check($a9 !== null, 'Artikel #9 ada');

echo "\n=== Post-deploy (manual) ===\n";
echo "○ Upload cover image via Filament (daftar artikel → Upload Cover)\n";

if ($checkProduction) {
    echo "\n=== Pass 4: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $code = trim((string) shell_exec('curl -sS --max-time 30 -o NUL -w "%{http_code}" ' . escapeshellarg($prodUrl)));
    check($code === '200', "Production HTTP {$code}");
    if ($code === '200') {
        $prodHtml = shell_exec('curl -sS --max-time 30 ' . escapeshellarg($prodUrl));
        check(str_contains((string) $prodHtml, 'kindo-esp32-node'), 'Production: YAML ESPHome ter-render');
    }
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
