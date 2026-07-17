<?php

/**
 * Audit artikel #9 — Gabungkan DHT22 + Relay MQTT.
 * Usage: php scripts/audit-article9.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article9Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug = 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article9Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #9 — Pass 1: Seeder ===\n\n";

$body = seederBody();

$requiredLinks = [
    'membaca-sensor-dht22-suhu-kelembaban-esp32' => 'Artikel #5 DHT22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker' => 'Artikel #7 MQTT',
    'kontrol-lampu-esp32-mqtt-relay' => 'Artikel #8 relay',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22' => 'Artikel #10 dashboard',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai' => 'Artikel #11 deep sleep',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => 'Artikel #16 Mosquitto',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32' => 'Artikel #18 Python',
    'home-assistant-integrasi-esp32-mqtt' => 'Artikel #21 HA',
    'esphome-flash-esp32-tanpa-coding-arduino' => 'Artikel #22 ESPHome',
    'node-red-dashboard-otomasi-iot-mqtt-esp32' => 'Artikel #23 Node-RED',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce' => 'Artikel #24 PIR',
    'freertos-esp32-multi-task-sensor-wifi-mqtt' => 'Artikel #31 FreeRTOS',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link: {$label}");
}

check(substr_count($body, 'figure role="img"') >= 2, 'Ada 2 figure SVG');
check(str_contains($body, 'viewBox="0 0 620 360"'), 'SVG topologi viewBox');
check(str_contains($body, 'viewBox="0 0 620 380"'), 'SVG wiring viewBox');
check(str_contains($body, 'PUB · .../dht22/data'), 'SVG: publish topic');
check(str_contains($body, 'SUB · .../lampu/kontrol'), 'SVG: subscribe topic');
check(str_contains($body, 'x1="385" y1="260" x2="365" y2="202"'), 'SVG topologi: SUB garis lurus');
check(str_contains($body, 'x1="255" y1="200" x2="235" y2="258"'), 'SVG topologi: PUB garis lurus');
check(str_contains($body, 'x1="370" y1="130" x2="470" y2="77"'), 'SVG topologi: panah ESP32→Relay');
check(str_contains($body, 'circle cx="320" cy="165"'), 'SVG wiring: junction GND bertemu');
check(str_contains($body, 'points="180,130 240,130 240,280 410,280"'), 'SVG wiring: 5V ortogonal tanpa silang GND');
check(! str_contains($body, 'points="390,260 390,230 370,202"'), 'SVG topologi: tanpa siku SUB lama');
check(str_contains($body, 'GPIO 26'), 'Wiring/kode GPIO 26 relay');
check(str_contains($body, 'GPIO 4'), 'Wiring/kode GPIO 4 DHT');
check(str_contains($body, 'GANTI_SSID_WIFI'), 'Placeholder SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(! str_contains($body, 'NamaWiFiKamu'), 'NamaWiFiKamu dihapus');
check(! str_contains($body, 'PasswordWiFiKamu'), 'PasswordWiFiKamu dihapus');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password MQTT literal');
check(str_contains($body, 'tutorial DHT22 (#5)</a>'), 'Hyperlink DHT22 (#5)');
check(str_contains($body, 'artikel MQTT (#7)</a>'), 'Hyperlink MQTT (#7)');
check(str_contains($body, 'artikel relay (#8)</a>'), 'Hyperlink relay (#8)');
check(str_contains($body, 'Dashboard hybrid Web Server + MQTT (#10)</a>'), 'Hyperlink dashboard (#10)');
check(str_contains($body, 'Home Assistant (#21)</a>'), 'Hyperlink HA (#21)');
check(str_contains($body, 'Mosquitto pribadi (#16)</a>'), 'Hyperlink Mosquitto (#16)');
check(str_contains($body, 'subscriber Python → MySQL (#18)</a>'), 'Hyperlink Python (#18)');
check(str_contains($body, 'deep sleep (#11)</a>'), 'Hyperlink deep sleep (#11)');
check(str_contains($body, 'FreeRTOS multi-task (#31)</a>'), 'Hyperlink FreeRTOS (#31) di seeder');
check(str_contains($body, 'NVS + WiFiManager (#12)</a>'), 'Hyperlink NVS (#12)');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop()');
check(str_contains($body, 'setBufferSize(512)'), 'setBufferSize(512)');
check(str_contains($body, 'ArduinoJson'), 'ArduinoJson disebut');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor /data');
check(str_contains($body, 'kodingindonesia/esp32/lampu/kontrol'), 'Topic kontrol lampu');
check(str_contains($body, 'Broker bukan website'), 'Peringatan broker');
check(str_contains($body, 'rel="noopener"'), 'Link eksternal noopener');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(substr_count($sanitized, '<svg') >= 2, 'Kedua SVG lolos sanitizer');
check(str_contains($sanitized, 'PUB · .../dht22/data'), 'Teks SVG topologi lolos sanitizer');

$plainBody = preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '';
$plainBody = preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $plainBody) ?? '';
$plainBody = preg_replace('/<svg\b[^>]*>.*?<\/svg>/is', '', $plainBody) ?? '';
preg_match_all('/#\d+(?![0-9a-fA-F])/', $plainBody, $plainRefs);
$residualPlain = array_values(array_unique($plainRefs[0] ?? []));
check($residualPlain === [], 'Tidak ada plain #N residual: ' . implode(', ', $residualPlain));

preg_match_all('/href="\/artikel\/([^"]+)">([^<]*)<\/a>/', $body, $am, PREG_SET_ORDER);
$bareAnchors = [];
foreach ($am as $hit) {
    if ($hit[1] !== '' && ! str_contains($hit[2], '#')) {
        $bareAnchors[] = $hit[1] . '=>' . $hit[2];
    }
}
check($bareAnchors === [], 'Tidak ada bare anchor: ' . implode('; ', $bareAnchors));

try {
    if (Article::count() > 0) {
        echo "\n=== Pass 2: DB seed (jika DB tersedia) ===\n\n";
        Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article9Seeder', '--force' => true]);
        $article = Article::where('slug', $slug)->first();
        check($article !== null, 'Artikel ada setelah seed');
        check($article?->status === 'published', 'Status published');
    }
} catch (Throwable $e) {
    echo "\n! Skip Pass 2 DB: {$e->getMessage()}\n";
}

if ($checkProduction) {
    echo "\n=== Pass 3: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $html = (string) shell_exec('curl -sS --max-time 30 ' . escapeshellarg($prodUrl));
    check(str_contains($html, '620 360') || str_contains($html, 'viewBox'), 'Prod SVG topologi');
    check(str_contains($html, 'GANTI_SSID_WIFI'), 'Prod WiFi placeholder');
    check(! str_contains($html, 'NamaWiFiKamu'), 'Prod tanpa NamaWiFiKamu');
    check(str_contains($html, 'FreeRTOS multi-task (#31)'), 'Prod FreeRTOS #31');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
