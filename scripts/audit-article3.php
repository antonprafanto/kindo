<?php

/**
 * Audit artikel #3 — Blink LED ESP32.
 * Usage: php scripts/audit-article3.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article3Seeder;

$passed = 0;
$failed = 0;
$slug = 'blink-led-esp32-tutorial-pertama-embedded-system';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article3Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #3 — Pass 1: Seeder ===\n\n";

$body = seederBody();

$requiredLinks = [
    'cara-install-arduino-ide-setup-esp32-board-manager' => 'Artikel #2 Arduino IDE',
    'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot' => 'Artikel #1 ESP32',
    'menghubungkan-esp32-wifi-kirim-data-server'         => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'         => 'Artikel #5 DHT22',
    'membuat-web-server-esp32-monitoring-sensor-dht22'   => 'Artikel #6 Web Server',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link: {$label}");
}

check(substr_count($body, 'figure role="img"') >= 2, 'Ada 2 figure SVG');
check(str_contains($body, 'viewBox="0 0 620 300"'), 'SVG wiring viewBox');
check(substr_count($body, 'viewBox="0 0 620 300"') >= 2, 'SVG alur viewBox 620x300');
check(str_contains($body, 'kembali ke GND'), 'SVG wiring: return ke GND ESP32');
check(! str_contains($body, 'rect x="490" y="60"'), 'SVG wiring: tanpa kotak GND mengambang');
check(str_contains($body, 'points="403,80 403,105 90,105 90,125"'), 'SVG alur: masuk loop ke HIGH');
check(str_contains($body, 'ulang loop() selamanya'), 'SVG alur: label ulang loop');
check(str_contains($body, 'id="w3O"'), 'Marker wiring w3O');
check(str_contains($body, 'id="a3B"'), 'Marker alur a3B');
check(str_contains($body, 'markerUnits="userSpaceOnUse"'), 'markerUnits userSpaceOnUse');
check(str_contains($body, '220 Ω') || str_contains($body, '220Ω'), 'Resistor 220 ohm di SVG/teks');
check(str_contains($body, 'GPIO 2') || str_contains($body, 'GPIO2'), 'GPIO 2 disebut');
check(str_contains($body, '#define LED_PIN 2'), 'Sketch LED_PIN 2');
check(str_contains($body, 'digitalWrite(LED_PIN, HIGH)'), 'Sketch digitalWrite HIGH');
check(str_contains($body, 'install Arduino IDE &amp; board ESP32 (#2)</a>') || str_contains($body, 'install Arduino IDE (#2)</a>'), 'Hyperlink prasyarat (#2)');
check(str_contains($body, 'Mengenal ESP32 (#1)</a>'), 'Hyperlink prasyarat (#1)');
check(str_contains($body, 'Menghubungkan ESP32 ke WiFi (#4)</a>'), 'Hyperlink WiFi (#4)');
check(str_contains($body, 'DHT22 (#5)</a>'), 'Hyperlink DHT22 (#5)');
check(str_contains($body, 'Web Server ESP32 + DHT22 (#6)</a>'), 'Hyperlink Web Server (#6)');
check(str_contains($body, 'built-in LED'), 'Built-in LED disebut');
check(str_contains($body, 'delay(100)'), 'Modifikasi kecepatan ada');
check(! preg_match('/<li>\s*<p>/', $body), 'Tidak ada li berisi p');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(substr_count($sanitized, '<svg') >= 2, 'Kedua SVG lolos sanitizer');
check(str_contains($sanitized, 'ESP32 DevKit'), 'Teks wiring lolos sanitizer');

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
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article3Seeder', '--force' => true]);
        $article = Article::where('slug', $slug)->first();
        check($article !== null, 'Artikel ada setelah seed');
        check($article?->status === 'published', 'Status published');
        $tags = $article?->tags->pluck('slug')->all() ?? [];
        check(in_array('gpio', $tags, true), 'Tag gpio');
    }
} catch (Throwable $e) {
    echo "\n! Skip Pass 2 DB: {$e->getMessage()}\n";
}

if ($checkProduction) {
    echo "\n=== Pass 3: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $html = (string) shell_exec('curl -sS --max-time 30 ' . escapeshellarg($prodUrl));
    check(str_contains($html, 'GPIO') || str_contains($html, 'viewBox'), 'Prod GPIO/SVG');
    check(str_contains($html, 'LED_PIN') || str_contains($html, 'Blink'), 'Prod sketch Blink');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
