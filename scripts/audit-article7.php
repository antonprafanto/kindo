<?php

/**
 * Audit artikel #7 — Memahami MQTT ESP32.
 * Usage: php scripts/audit-article7.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article7Seeder;

$passed = 0;
$failed = 0;
$slug = 'memahami-mqtt-esp32-kirim-data-sensor-broker';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article7Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #7 — Pass 1: Seeder ===\n\n";

$body = seederBody();

$requiredLinks = [
    'menghubungkan-esp32-wifi-kirim-data-server' => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32' => 'Artikel #5 DHT22',
    'membuat-web-server-esp32-monitoring-sensor-dht22' => 'Artikel #6 Web Server',
    'kontrol-lampu-esp32-mqtt-relay' => 'Artikel #8 relay',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek' => 'Artikel #9 gabungan',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22' => 'Artikel #10 dashboard',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => 'Artikel #16 Mosquitto',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32' => 'Artikel #17 TLS',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32' => 'Artikel #18 Python',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt' => 'Artikel #19 Grafana',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32' => 'Artikel #20 REST vs MQTT',
    'home-assistant-integrasi-esp32-mqtt' => 'Artikel #21 HA',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi' => 'Artikel #25 ESP-NOW',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh' => 'Artikel #26 LoRa',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt' => 'Artikel #34 NTP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link: {$label}");
}

check(substr_count($body, 'figure role="img"') >= 2, 'Ada 2 figure SVG');
check(str_contains($body, 'viewBox="0 0 620 360"'), 'SVG topologi viewBox');
check(str_contains($body, 'viewBox="0 0 620 320"'), 'SVG wiring viewBox');
check(str_contains($body, 'ESP32 publish'), 'SVG: ESP32 publish');
check(str_contains($body, 'MQTT Explorer'), 'SVG/teks: MQTT Explorer');
check(str_contains($body, 'points="190,110 300,110 300,145 410,145"'), 'SVG wiring: 3.3V ortogonal');
check(str_contains($body, 'points="190,200 340,200 340,215 410,215"'), 'SVG wiring: DATA ortogonal');
check(str_contains($body, 'x1="310" y1="95" x2="310" y2="148"'), 'SVG topologi: PUB ke broker');
check(str_contains($body, 'GANTI_SSID_WIFI'), 'Placeholder SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(! str_contains($body, 'NamaWiFiKamu'), 'NamaWiFiKamu dihapus');
check(! str_contains($body, 'PasswordWiFiKamu'), 'PasswordWiFiKamu dihapus');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password MQTT literal');
check(str_contains($body, 'Web Server ESP32 (#6)</a>'), 'Hyperlink Web Server (#6)');
check(str_contains($body, 'Menghubungkan ESP32 ke WiFi (#4)</a>'), 'Hyperlink WiFi (#4)');
check(str_contains($body, 'Membaca Sensor DHT22 (#5)</a>'), 'Hyperlink DHT22 (#5)');
check(str_contains($body, 'broker Mosquitto pribadi (#16)</a>'), 'Hyperlink Mosquitto (#16)');
check(str_contains($body, 'relay lampu via MQTT (#8)</a>'), 'Hyperlink relay (#8)');
check(str_contains($body, 'Home Assistant (#21)</a>'), 'Hyperlink HA (#21)');
check(str_contains($body, 'NVS + WiFiManager (#12)</a>'), 'Hyperlink NVS (#12)');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop()');
check(str_contains($body, 'kodingindonesia/esp32/dht22'), 'Topic sensor dht22');
check(str_contains($body, 'broker bukan website'), 'Peringatan broker');
check(str_contains($body, 'rel="noopener"'), 'Link eksternal noopener');
check(str_contains($body, 'PubSubClient'), 'PubSubClient disebut');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(substr_count($sanitized, '<svg') >= 2, 'Kedua SVG lolos sanitizer');
check(str_contains($sanitized, 'ESP32 publish'), 'Teks SVG topologi lolos sanitizer');
check(str_contains($sanitized, 'markerUnits'), 'markerUnits lolos sanitizer');

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
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);
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
    check(str_contains($html, 'relay lampu via MQTT (#8)'), 'Prod link relay (#8)');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
