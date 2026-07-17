<?php

/**
 * Audit artikel #11 — Deep Sleep ESP32 + DHT22 MQTT.
 * Usage: php scripts/audit-article11.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article11Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article11Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #11 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article11Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);

$article = Article::where('slug', 'deep-sleep-esp32-sensor-dht22-hemat-baterai')->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device');
check($article?->is_featured === true, 'is_featured true (pembuka Seri 2)');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder (null/ada)');

$requiredTags = ['esp32', 'dht22', 'sensor', 'iot', 'wifi', 'mqtt', 'deep-sleep'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'menghubungkan-esp32-wifi-kirim-data-server'     => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'     => 'Artikel #5 DHT22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'   => 'Artikel #7 MQTT',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek'   => 'Artikel #9 gabungan',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22' => 'Artikel #10 capstone',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'      => 'Artikel #13 BME280',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'   => 'Artikel #14 OLED',
    'ota-update-firmware-esp32-via-wifi'             => 'Artikel #15 OTA',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => 'Artikel #16 Mosquitto',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi' => 'Artikel #25 ESP-NOW',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'  => 'Artikel #26 LoRa',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt' => 'Artikel #34 NTP',
];

foreach ($requiredLinks as $slug => $label) {
    check(str_contains($body, '/artikel/' . $slug), "Link internal: {$label}");
    check(Article::where('slug', $slug)->exists(), "Target exists: {$slug}");
}

check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'MQTT topic sensor konsisten');
check(str_contains($body, 'test.mosquitto.org'), 'Broker latihan test.mosquitto.org');
check(str_contains($body, 'Pro tip'), 'Pro tip topic unik (konsisten Seri 1)');
check(str_contains($body, 'Broker bukan website'), 'Peringatan broker bukan website');
check(str_contains($body, 'esp_deep_sleep_start'), 'Kode: esp_deep_sleep_start');
check(str_contains($body, 'esp_sleep_enable_timer_wakeup'), 'Kode: timer wakeup');
check(str_contains($body, 'esp_sleep_enable_ext0_wakeup'), 'Menyebut ext0 wakeup (learning outcome)');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop() sebelum publish');
check(str_contains($body, 'setBufferSize(512)'), 'PubSubClient setBufferSize(512)');
check(str_contains($body, 'dht.begin()'), 'DHT begin');
check(
    preg_match('/dht\.begin\(\)[\s\S]{0,120}delay\(2000\)/', $body),
    'delay(2000) setelah dht.begin() di kode'
);
check(str_contains($body, 'WiFi.mode(WIFI_OFF)'), 'Matikan WiFi sebelum sleep');
check(str_contains($body, 'setCpuFrequencyMhz(80)'), 'setCpuFrequencyMhz(80)');
check(str_contains($body, '#define DHT_PIN  4'), 'DHT GPIO 4');
check(str_contains($body, 'Install Library'), 'Section install library');
check(str_contains($body, 'PubSubClient'), 'Library PubSubClient disebut');
check(str_contains($body, 'Seri 2'), 'Menyebut Seri 2');
check(str_contains($body, 'janji') && str_contains($body, 'deep sleep'), 'Link janji roadmap artikel #10');
check(str_contains($body, 'NVS'), 'Teaser artikel #12 NVS/WiFiManager');
check(str_contains($body, 'Multimeter'), 'Tips multimeter');
check(str_contains($body, 'Light sleep'), 'Perbandingan light vs deep sleep');
check(str_contains($body, 'language-arduino'), 'Blok kode Arduino');
check(str_contains($body, 'language-bash'), 'Blok mosquitto_sub bash');
check(str_contains($body, '<table>'), 'Ada tabel');
check(substr_count($body, 'figure role="img"') >= 2, 'Ada 2 figure SVG (wiring + arsitektur)');
check(str_contains($body, 'viewBox="0 0 620 320"'), 'SVG wiring viewBox');
check(str_contains($body, 'viewBox="0 0 620 430"'), 'SVG arsitektur viewBox');
check(str_contains($body, 'GPIO 4 → DATA'), 'SVG wiring: legend GPIO 4 → DATA');
check(str_contains($body, 'esp_deep_sleep_start() → ulang'), 'SVG siklus: deep sleep ulang');
check(str_contains($body, 'Deep sleep · ~10 µA') || str_contains($body, 'Deep sleep · ~10'), 'SVG: deep sleep microamp');
check(str_contains($body, 'GANTI_SSID_WIFI'), 'Placeholder SSID WiFi');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(! str_contains($body, 'NamaWiFiKamu'), 'Placeholder lama NamaWiFiKamu dihapus');
check(! str_contains($body, 'PasswordWiFiKamu'), 'Placeholder lama PasswordWiFiKamu dihapus');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password MQTT literal');

check(str_contains($body, 'WiFi ESP32 (#4)</a>'), 'Hyperlink prasyarat WiFi (#4)');
check(str_contains($body, 'sensor DHT22 (#5)</a>'), 'Hyperlink prasyarat DHT22 (#5)');
check(str_contains($body, 'publish MQTT (#7)</a>'), 'Hyperlink prasyarat MQTT (#7)');
check(str_contains($body, 'proyek gabungan (#9)</a>'), 'Hyperlink proyek gabungan (#9)');
check(str_contains($body, 'dashboard capstone (#10)</a>'), 'Hyperlink dashboard (#10)');
check(str_contains($body, 'artikel MQTT (#7)</a>'), 'Hyperlink MQTT (#7)');
check(str_contains($body, 'NVS + WiFiManager (#12)</a>'), 'Hyperlink NVS (#12)');
check(str_contains($body, 'NVS + WiFiManager ESP32 (#12)</a>'), 'Hyperlink langkah #12');
check(str_contains($body, 'Broker Mosquitto pribadi (#16)</a>'), 'Hyperlink Mosquitto (#16)');
check(str_contains($body, 'Sensor BME280 via I2C (#13)</a>'), 'Hyperlink BME280 (#13)');
check(str_contains($body, 'OLED SSD1306 (#14)</a>'), 'Hyperlink OLED (#14)');
check(str_contains($body, 'OTA update firmware (#15)</a>'), 'Hyperlink OTA (#15)');
check(str_contains($body, 'WiFiManager (#12)</a>'), 'Hyperlink OTA→#12');
check(str_contains($body, 'dashboard capstone Seri 1 (#10)</a>'), 'Hyperlink capstone (#10)');
check(! preg_match('/setelah #12 WiFiManager/', $body), 'Tidak ada plain #12 residual di OTA');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(substr_count($sanitized, '<svg') >= 2, 'Kedua SVG lolos sanitizer');
check(str_contains($sanitized, 'GPIO 4 → DATA'), 'Wiring legend lolos sanitizer');

$plainBody = preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '';
$plainBody = preg_replace('/<svg\b[^>]*>.*?<\/svg>/is', '', $plainBody) ?? '';
preg_match_all('/#\d+(?![0-9a-fA-F])/', $plainBody, $plainRefs);
$residualPlain = array_values(array_unique($plainRefs[0] ?? []));
check($residualPlain === [], 'Tidak ada plain #N residual: ' . implode(', ', $residualPlain));

// Anchor tanpa nomor (kecuali href="/artikel" indeks)
preg_match_all('/href="\/artikel\/([^"]+)">([^<]*)<\/a>/', $body, $am, PREG_SET_ORDER);
$bareAnchors = [];
foreach ($am as $hit) {
    if ($hit[1] !== '' && ! str_contains($hit[2], '#')) {
        $bareAnchors[] = $hit[1] . '=>' . $hit[2];
    }
}
check($bareAnchors === [], 'Tidak ada bare anchor: ' . implode('; ', $bareAnchors));

check(str_contains($body, 'rel="noopener"'), 'Link eksternal noopener');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = preg_match_all('/<h2>/', $body);
check($h2Count >= 9, "Minimal 9 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 5, 'read_time_minutes ≥ 5 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai', 'GET');
$response = $kernel->handle($request);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'Deep Sleep ESP32'), 'Judul tampil di halaman');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'esp_deep_sleep_start'), 'Kode deep sleep ter-render');
check(str_contains($html, 'mqttClient.loop'), 'mqttClient.loop ter-render');
$kernel->terminate($request, $response);

echo "\n=== Pass 3: Konsistensi Seri 1 ===\n\n";

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check($a10 !== null, 'Artikel #10 ada');
check(
    str_contains($a10?->body ?? '', 'deep-sleep-esp32-sensor-dht22-hemat-baterai'),
    'Artikel #10 backlink deep sleep → artikel #11'
);

echo "\n=== Post-deploy (manual) ===\n";
echo "○ Update artikel #10: tambah link deep sleep → /artikel/deep-sleep-esp32-sensor-dht22\n";
echo "○ Upload cover image via Filament\n";

if ($checkProduction) {
    echo "\n=== Pass 4: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/deep-sleep-esp32-sensor-dht22-hemat-baterai';
    $prodHtml = (string) shell_exec('curl -sS --max-time 30 -o NUL -w "%{http_code}" ' . escapeshellarg($prodUrl));
    $code = trim($prodHtml);
    check($code === '200', "Production HTTP {$code}");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
