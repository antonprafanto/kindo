<?php

/**
 * Audit artikel #32 — Bluetooth BLE ESP32.
 * Usage: php scripts/audit-article32.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article32Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'bluetooth-esp32-ble-kirim-data-sensor-smartphone';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article32Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #32 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article32Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article31Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle1BluetoothSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');
check($article?->is_featured === false, 'is_featured false');

$requiredTags = ['esp32', 'bluetooth', 'ble', 'iot', 'sensor', 'smartphone'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot'              => 'Artikel #1 ESP32',
    'blink-led-esp32-tutorial-pertama-embedded-system'                => 'Artikel #3 Blink',
    'menghubungkan-esp32-wifi-kirim-data-server'                    => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                      => 'Artikel #5 DHT22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                    => 'Artikel #7 MQTT',
    'kontrol-lampu-esp32-mqtt-relay'                                  => 'Artikel #8 Relay',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'              => 'Artikel #10 Capstone',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                     => 'Artikel #11 Deep sleep',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'    => 'Artikel #12 WiFiManager',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'                => 'Artikel #25 ESP-NOW',
    'migrasi-platformio-esp32-vscode-project-rapi'                    => 'Artikel #29 PlatformIO',
    'freertos-esp32-multi-task-sensor-wifi-mqtt'                      => 'Artikel #31 FreeRTOS',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'                => 'Artikel #34 NTP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(str_contains($body, 'BLEDevice'), 'API BLEDevice');
check(str_contains($body, 'BLEServer'), 'API BLEServer');
check(str_contains($body, 'BLECharacteristic'), 'API BLECharacteristic');
check(str_contains($body, 'BLE2902'), 'Descriptor BLE2902');
check(str_contains($body, 'KindoESP32-DHT22'), 'Nama perangkat BLE');
check(str_contains($body, 'b10d4001-0001-4001-8001-000032000001'), 'Service UUID konsisten');
check(str_contains($body, 'b10d4002-0002-4002-8002-000032000002'), 'Characteristic UUID konsisten');
check(str_contains($body, 'nRF Connect'), 'App nRF Connect');
check(str_contains($body, 'DHT_PIN  4') || str_contains($body, 'DHT_PIN 4'), 'GPIO4 DHT22');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic MQTT konsisten (referensi)');
check(str_contains($body, '1782977400'), 'Contoh unix konsisten #34');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder WiFi SSID');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password MQTT');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'language-cpp'), 'Blok C++');
check(str_contains($body, 'language-ini'), 'Blok platformio.ini');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(str_contains($body, 'Estimasi Biaya'), 'Estimasi biaya');
check(str_contains($body, 'Servo') && str_contains($body, '#33'), 'Teaser Servo #33');
check(str_contains($body, 'Checklist'), 'Section checklist');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 14, "Minimal 14 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 8, 'read_time_minutes ≥ 8 menit');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(
    Illuminate\Http\Request::create('/artikel/' . $slug, 'GET')
);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'Bluetooth') && str_contains($html, 'BLE'), 'Konten BLE ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'KindoESP32-DHT22'), 'Nama perangkat ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 (backlink) ===\n\n";

$sources = [
    'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot' => '#1',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22' => '#10',
    'freertos-esp32-multi-task-sensor-wifi-mqtt'       => '#31',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Artikel {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #32");
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
