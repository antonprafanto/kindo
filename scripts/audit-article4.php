<?php

/**
 * Audit artikel #4 — ESP32 WiFi & HTTP.
 * Usage: php scripts/audit-article4.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article4Seeder;

$passed = 0;
$failed = 0;
$slug = 'menghubungkan-esp32-wifi-kirim-data-server';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article4Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #4 — Pass 1: Seeder ===\n\n";

$body = seederBody();

$requiredLinks = [
    'blink-led-esp32-tutorial-pertama-embedded-system'              => 'Artikel #3 Blink',
    'cara-install-arduino-ide-setup-esp32-board-manager'            => 'Artikel #2 Arduino IDE',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                    => 'Artikel #5 DHT22',
    'membuat-web-server-esp32-monitoring-sensor-dht22'              => 'Artikel #6 Web Server',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                  => 'Artikel #7 MQTT',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'  => 'Artikel #12 NVS',
    'esp32-firebase-realtime-database-sensor-cloud'                 => 'Artikel #30 Firebase',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link: {$label}");
}

check(substr_count($body, 'figure role="img"') >= 2, 'Ada 2 figure SVG');
check(str_contains($body, 'viewBox="0 0 620 280"'), 'SVG topologi viewBox');
check(str_contains($body, 'viewBox="0 0 620 200"'), 'SVG alur viewBox');
check(str_contains($body, 'id="w4B"'), 'Marker topologi w4B');
check(str_contains($body, 'id="a4B"'), 'Marker alur a4B');
check(str_contains($body, 'markerUnits="userSpaceOnUse"'), 'markerUnits userSpaceOnUse');
check(str_contains($body, 'GANTI_SSID_WIFI'), 'Placeholder SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(! str_contains($body, 'NamaWiFiKamu'), 'NamaWiFiKamu dihapus');
check(! str_contains($body, 'PasswordWiFiKamu'), 'PasswordWiFiKamu dihapus');
check(! str_contains($body, 'WiFiKamu'), 'WiFiKamu dihapus');
check(! str_contains($body, 'PasswordKamu'), 'PasswordKamu dihapus');
check(str_contains($body, 'Serial.println();'), 'Serial.println() terpisah (bukan \\n)');
check(! str_contains($body, 'println("\\n'), 'Tidak ada println dengan \\n');
check(str_contains($body, 'Serial.println("Terhubung ke WiFi!")'), 'Pesan terhubung WiFi');
check(str_contains($body, 'HTTPClient'), 'Library HTTPClient');
check(str_contains($body, 'HTTP GET') || str_contains($body, 'http.GET()'), 'HTTP GET disebut');
check(str_contains($body, 'HTTP POST') || str_contains($body, 'http.POST'), 'HTTP POST disebut');
check(str_contains($body, 'Blink LED (#3)</a>'), 'Hyperlink prasyarat (#3)');
check(str_contains($body, 'install Arduino IDE (#2)</a>'), 'Hyperlink prasyarat (#2)');
check(str_contains($body, 'NVS + WiFiManager (#12)</a>'), 'Hyperlink NVS (#12)');
check(str_contains($body, 'Firebase (#30)</a>') || str_contains($body, 'ESP32 + Firebase (#30)</a>'), 'Hyperlink Firebase (#30)');
check(str_contains($body, 'MQTT (#7)</a>'), 'Hyperlink MQTT (#7)');
check(! preg_match('/<li>\s*<p>/', $body), 'Tidak ada li berisi p');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(substr_count($sanitized, '<svg') >= 2, 'Kedua SVG lolos sanitizer');

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
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article4Seeder', '--force' => true]);
        $article = Article::where('slug', $slug)->first();
        check($article !== null, 'Artikel ada setelah seed');
        check($article?->status === 'published', 'Status published');
        check($article?->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device');
    }
} catch (Throwable $e) {
    echo "\n! Skip Pass 2 DB: {$e->getMessage()}\n";
}

if ($checkProduction) {
    echo "\n=== Pass 3: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $html = (string) shell_exec('curl -sS --max-time 30 ' . escapeshellarg($prodUrl));
    check(str_contains($html, 'GANTI_SSID_WIFI'), 'Prod WiFi placeholder');
    check(! str_contains($html, 'NamaWiFiKamu'), 'Prod tanpa NamaWiFiKamu');
    check(str_contains($html, 'NVS') || str_contains($html, 'WiFiManager'), 'Prod NVS/WiFiManager');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
