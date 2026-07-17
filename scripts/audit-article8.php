<?php

/**
 * Audit artikel #8 — Kontrol Lampu ESP32 MQTT + Relay.
 * Usage: php scripts/audit-article8.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article8Seeder;

$passed = 0;
$failed = 0;
$slug = 'kontrol-lampu-esp32-mqtt-relay';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article8Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #8 — Pass 1: Seeder ===\n\n";

$body = seederBody();

$requiredLinks = [
    'menghubungkan-esp32-wifi-kirim-data-server' => 'Artikel #4 WiFi',
    'membuat-web-server-esp32-monitoring-sensor-dht22' => 'Artikel #6 Web Server',
    'memahami-mqtt-esp32-kirim-data-sensor-broker' => 'Artikel #7 MQTT',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek' => 'Artikel #9 gabungan',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => 'Artikel #16 Mosquitto',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32' => 'Artikel #17 TLS',
    'home-assistant-integrasi-esp32-mqtt' => 'Artikel #21 HA',
    'esphome-flash-esp32-tanpa-coding-arduino' => 'Artikel #22 ESPHome',
    'node-red-dashboard-otomasi-iot-mqtt-esp32' => 'Artikel #23 Node-RED',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce' => 'Artikel #24 PIR',
    'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi' => 'Artikel #33 Servo',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link: {$label}");
}

check(substr_count($body, 'figure role="img"') >= 2, 'Ada 2 figure SVG');
check(str_contains($body, 'viewBox="0 0 620 360"'), 'SVG topologi viewBox');
check(str_contains($body, 'viewBox="0 0 620 340"'), 'SVG wiring viewBox');
check(str_contains($body, 'topic · .../lampu/kontrol'), 'SVG: topic kontrol');
check(str_contains($body, 'MQTT Explorer'), 'SVG/teks: MQTT Explorer');
check(str_contains($body, 'GPIO 26'), 'Wiring/kode GPIO 26');
check(str_contains($body, 'points="190,110 300,110 300,150 410,150"'), 'SVG wiring: 5V ortogonal');
check(str_contains($body, 'points="190,160 320,160 320,190 410,190"'), 'SVG wiring: GND ortogonal');
check(str_contains($body, 'points="190,210 340,210 340,230 410,230"'), 'SVG wiring: IN ortogonal');
check(str_contains($body, 'x1="200" y1="60" x2="298" y2="60"'), 'SVG topologi: PUB Explorer→broker');
check(str_contains($body, 'x1="360" y1="90" x2="220" y2="158"'), 'SVG topologi: SUB broker→ESP32');
check(str_contains($body, 'x1="280" y1="195" x2="378" y2="195"'), 'SVG topologi: ESP32→Relay');
check(str_contains($body, 'GANTI_SSID_WIFI'), 'Placeholder SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(! str_contains($body, 'NamaWiFiKamu'), 'NamaWiFiKamu dihapus');
check(! str_contains($body, 'PasswordWiFiKamu'), 'PasswordWiFiKamu dihapus');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password MQTT literal');
check(str_contains($body, 'Memahami MQTT dengan ESP32 (#7)</a>'), 'Hyperlink MQTT (#7)');
check(str_contains($body, 'Menghubungkan ESP32 ke WiFi (#4)</a>'), 'Hyperlink WiFi (#4)');
check(str_contains($body, 'Web Server ESP32 + DHT22 (#6)</a>'), 'Hyperlink Web Server (#6)');
check(str_contains($body, 'proyek gabungan DHT22 + relay (#9)</a>'), 'Hyperlink gabungan (#9)');
check(str_contains($body, 'NVS + WiFiManager (#12)</a>'), 'Hyperlink NVS (#12)');
check(str_contains($body, 'Home Assistant (#21)</a>'), 'Hyperlink HA (#21)');
check(str_contains($body, 'Mosquitto pribadi (#16)</a>'), 'Hyperlink Mosquitto (#16)');
check(str_contains($body, 'Kontrol Servo &amp; PWM via MQTT (#33)</a>'), 'Hyperlink Servo (#33) di seeder');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop()');
check(str_contains($body, 'kodingindonesia/esp32/lampu/kontrol'), 'Topic kontrol lampu');
check(str_contains($body, 'Broker bukan website'), 'Peringatan broker');
check(str_contains($body, 'rel="noopener"'), 'Link eksternal noopener');
check(str_contains($body, 'active LOW'), 'active LOW disebut');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(substr_count($sanitized, '<svg') >= 2, 'Kedua SVG lolos sanitizer');
check(str_contains($sanitized, 'topic · .../lampu/kontrol'), 'Teks SVG topologi lolos sanitizer');
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
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article8Seeder', '--force' => true]);
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
    check(str_contains($html, 'Kontrol Servo') && str_contains($html, '#33'), 'Prod Servo #33');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
