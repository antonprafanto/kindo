<?php

/**
 * Audit artikel #1 — Mengenal ESP32.
 * Usage: php scripts/audit-article1.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article1Seeder;

$passed = 0;
$failed = 0;
$slug = 'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article1Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #1 — Pass 1: Seeder ===\n\n";

$body = seederBody();

$requiredLinks = [
    'cara-install-arduino-ide-setup-esp32-board-manager' => 'Artikel #2 Arduino IDE',
    'blink-led-esp32-tutorial-pertama-embedded-system'     => 'Artikel #3 Blink',
    'menghubungkan-esp32-wifi-kirim-data-server'         => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'         => 'Artikel #5 DHT22',
    'bluetooth-esp32-ble-kirim-data-sensor-smartphone'   => 'Artikel #32 BLE',
    'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade'       => 'Artikel #36 ESP8266',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link: {$label}");
}

check(substr_count($body, 'figure role="img"') >= 1, 'Ada 1 figure SVG');
check(str_contains($body, 'viewBox="0 0 620 380"'), 'SVG overview viewBox 620x380');
check(str_contains($body, 'markerUnits="userSpaceOnUse"'), 'markerUnits userSpaceOnUse');
check(str_contains($body, 'id="a1G"'), 'Marker unik a1G');
check(str_contains($body, 'background:#F5F5F0;border:2.5px solid #1a1a1a'), 'Figure style PRD');
check(str_contains($body, 'Dual-core'), 'Dual-core disebut');
check(str_contains($body, '802.11 b/g/n'), 'WiFi spec disebut');
check(str_contains($body, 'x1="310" y1="145" x2="310" y2="108"'), 'SVG: panah keluar ke WiFi');
check(str_contains($body, 'x1="400" y1="190" x2="442" y2="190"'), 'SVG: panah keluar ke Bluetooth');
check(str_contains($body, 'Arduino IDE (#2)</a>'), 'Figcaption link Arduino IDE (#2)');
check(str_contains($body, 'Install Arduino IDE &amp; ESP32 Board Manager (#2)</a>') || str_contains($body, 'install Arduino IDE &amp; setup ESP32 Board Manager (#2)</a>'), 'Hyperlink Install (#2)');
check(str_contains($body, 'Blink LED (#3)</a>'), 'Hyperlink Blink (#3)');
check(str_contains($body, 'WiFi (#4)</a>'), 'Hyperlink WiFi (#4)');
check(str_contains($body, 'DHT22 (#5)</a>') || str_contains($body, 'sensor DHT22 (#5)</a>'), 'Hyperlink DHT22 (#5)');
check(str_contains($body, 'Bluetooth BLE ESP32 (#32)</a>'), 'Hyperlink BLE (#32)');
check(str_contains($body, 'ESP8266 vs ESP32 (#36)</a>') || str_contains($body, 'perbandingan (#36)</a>'), 'Hyperlink ESP8266 (#36)');
check(str_contains($body, '<h2>Apa itu ESP32?</h2>'), 'Section Apa itu ESP32');
check(str_contains($body, '<h2>Spesifikasi Teknis ESP32</h2>'), 'Section Spesifikasi');
check(str_contains($body, '<h2>Mengapa Memilih ESP32?</h2>'), 'Section Mengapa Memilih');
check(str_contains($body, '<h2>Perbandingan ESP32 vs ESP8266 vs Arduino</h2>'), 'Section Perbandingan');
check(str_contains($body, '<h2>Memulai dengan ESP32</h2>'), 'Section Memulai');
check(str_contains($body, '<h2>Langkah Selanjutnya</h2>'), 'Section Langkah Selanjutnya');
check(! preg_match('/<li>\s*<p>/', $body), 'Tidak ada li berisi p');

$seoDesc = 'Pelajari ESP32: mikrokontroler WiFi & Bluetooth untuk IoT. Spesifikasi lengkap, dual-core, perbandingan ESP8266, dan langkah memulai proyek embedded.';
$seoLen = mb_strlen($seoDesc);
check($seoLen >= 120 && $seoLen <= 155, "seo_description panjang OK ({$seoLen} char)");

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(str_contains($sanitized, '<svg'), 'SVG lolos sanitizer');
check(str_contains($sanitized, 'ESP32'), 'Teks SVG lolos sanitizer');

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
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article1Seeder', '--force' => true]);
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
    check(str_contains($html, 'viewBox') || str_contains($html, '620 380'), 'Prod SVG overview');
    check(str_contains($html, 'Arduino IDE (#2)') || str_contains($html, 'Board Manager (#2)'), 'Prod link #2');
    check(str_contains($html, 'Bluetooth BLE ESP32 (#32)') || str_contains($html, 'BLE (#32)'), 'Prod link #32');
    check(str_contains($html, 'x2="310" y2="108"') || str_contains($html, 'Xtensa LX6'), 'Prod panah keluar / dual-core');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
