<?php

/**
 * Audit artikel #5 — Membaca Sensor DHT22.
 * Usage: php scripts/audit-article5.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article5Seeder;

$passed = 0;
$failed = 0;
$slug = 'membaca-sensor-dht22-suhu-kelembaban-esp32';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article5Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #5 — Pass 1: Seeder ===\n\n";

$body = seederBody();

$requiredLinks = [
    'blink-led-esp32-tutorial-pertama-embedded-system' => 'Artikel #3 GPIO',
    'menghubungkan-esp32-wifi-kirim-data-server' => 'Artikel #4 WiFi',
    'membuat-web-server-esp32-monitoring-sensor-dht22' => 'Artikel #6 Web Server',
    'memahami-mqtt-esp32-kirim-data-sensor-broker' => 'Artikel #7 MQTT',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai' => 'Artikel #11 Deep Sleep',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt' => 'Artikel #13 BME280',
    'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt' => 'Artikel #35 ADC',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link: {$label}");
}

check(substr_count($body, 'figure role="img"') >= 2, 'Ada 2 figure SVG');
check(str_contains($body, 'viewBox="0 0 620 320"'), 'SVG wiring viewBox');
check(str_contains($body, 'viewBox="0 0 620 300"'), 'SVG alur viewBox');
check(str_contains($body, 'points="190,110 300,110 300,145 410,145"'), 'SVG wiring: 3.3V ortogonal');
check(str_contains($body, 'points="190,155 320,155 320,180 410,180"'), 'SVG wiring: GND ortogonal');
check(str_contains($body, 'points="190,200 340,200 340,215 410,215"'), 'SVG wiring: DATA ortogonal');
check(str_contains($body, 'GPIO 4'), 'GPIO 4 disebut');
check(str_contains($body, 'pull-up 10k'), 'Pull-up 10k disebut');
check(str_contains($body, 'GANTI_SSID_WIFI'), 'Placeholder SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(! str_contains($body, 'WiFiKamu'), 'WiFiKamu dihapus');
check(! str_contains($body, 'PasswordKamu'), 'PasswordKamu dihapus');
check(! str_contains($body, 'NamaWiFiKamu'), 'NamaWiFiKamu dihapus');
check(! str_contains($body, 'PasswordWiFiKamu'), 'PasswordWiFiKamu dihapus');
check(str_contains($body, 'Blink LED (#3)</a>'), 'Hyperlink Blink (#3)');
check(str_contains($body, 'WiFi ESP32 (#4)</a>') || str_contains($body, 'koneksi WiFi (#4)</a>') || str_contains($body, 'artikel WiFi (#4)</a>'), 'Hyperlink WiFi (#4)');
check(str_contains($body, 'Web Server (#6)</a>') || str_contains($body, 'Web Server ESP32 + DHT22 (#6)</a>'), 'Hyperlink Web Server (#6)');
check(str_contains($body, 'MQTT (#7)</a>') || str_contains($body, 'via MQTT (#7)</a>'), 'Hyperlink MQTT (#7)');
check(str_contains($body, 'Deep Sleep DHT22 (#11)</a>'), 'Hyperlink Deep Sleep (#11)');
check(str_contains($body, 'NVS + WiFiManager (#12)</a>'), 'Hyperlink NVS (#12)');
check(str_contains($body, 'BME280 via I2C (#13)</a>'), 'Hyperlink BME280 (#13)');
check(str_contains($body, 'LDR (#35)</a>'), 'Hyperlink ADC (#35)');
check(str_contains($body, 'DHT22 Sensor Siap!'), 'Sketch Serial ada');
check(str_contains($body, 'HTTPClient'), 'Sketch WiFi HTTP ada');
check(str_contains($body, 'markerUnits="userSpaceOnUse"'), 'markerUnits userSpaceOnUse');
check(str_contains($body, 'rel="noopener"') || ! str_contains($body, 'target="_blank"'), 'Eksternal aman / tidak ada target blank tanpa noopener');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(substr_count($sanitized, '<svg') >= 2, 'Kedua SVG lolos sanitizer');
check(str_contains($sanitized, 'ESP32 DevKit'), 'Teks SVG wiring lolos sanitizer');
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
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article5Seeder', '--force' => true]);
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
    check(str_contains($html, '620 320') || str_contains($html, 'viewBox'), 'Prod SVG wiring');
    check(str_contains($html, 'GANTI_SSID_WIFI'), 'Prod WiFi placeholder');
    check(! str_contains($html, 'WiFiKamu'), 'Prod tanpa WiFiKamu');
    check(str_contains($html, 'Web Server ESP32 + DHT22 (#6)') || str_contains($html, 'Web Server (#6)'), 'Prod link Web (#6)');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
