<?php

/**
 * Audit artikel #2 — Install Arduino IDE & ESP32 Board Manager.
 * Usage: php scripts/audit-article2.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article2Seeder;

$passed = 0;
$failed = 0;
$slug = 'cara-install-arduino-ide-setup-esp32-board-manager';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article2Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #2 — Pass 1: Seeder ===\n\n";

$body = seederBody();

$requiredLinks = [
    'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot' => 'Artikel #1 ESP32',
    'blink-led-esp32-tutorial-pertama-embedded-system' => 'Artikel #3 Blink',
    'migrasi-platformio-esp32-vscode-project-rapi'     => 'Artikel #29 PlatformIO',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link: {$label}");
}

check(substr_count($body, 'figure role="img"') >= 1, 'Ada 1 figure SVG');
check(str_contains($body, 'viewBox="0 0 620 220"'), 'SVG alur viewBox 620x220');
check(str_contains($body, 'id="a2B"'), 'Marker unik a2B');
check(str_contains($body, 'markerUnits="userSpaceOnUse"'), 'markerUnits userSpaceOnUse');
check(str_contains($body, 'background:#F5F5F0;border:2.5px solid #1a1a1a'), 'Figure style PRD');
check(str_contains($body, 'Upload') && str_contains($body, 'Blink'), 'SVG: Upload Blink');
check(str_contains($body, 'Boards Mgr'), 'SVG: Boards Manager');
check(str_contains($body, 'Ikuti lima langkah'), 'SVG caption user-facing');
check(! str_contains($body, 'jeda antar kotak'), 'Tidak ada catatan PRD bocor (jeda)');
check(! str_contains($body, 'satu layar kecil'), 'Tidak ada catatan PRD bocor (layar)');
check(str_contains($body, 'package_esp32_index.json'), 'Board manager URL di pre/code');
check(str_contains($body, '<h2>Langkah 1: Download Arduino IDE</h2>'), 'Langkah 1 ada');
check(str_contains($body, '<h2>Langkah 5: Test Koneksi ESP32</h2>'), 'Langkah 5 ada');
check(str_contains($body, 'Halo dari ESP32!'), 'Hello World sketch ada');
check(str_contains($body, 'arduino.cc/en/software'), 'URL arduino.cc sebagai strong text');
check(! str_contains($body, 'href="http'), 'Tidak ada outbound href eksternal');
check(str_contains($body, 'Mengenal ESP32 (#1)</a>'), 'Hyperlink prasyarat (#1)');
check(str_contains($body, 'Blink LED dengan ESP32 (#3)</a>'), 'Hyperlink Blink (#3)');
check(str_contains($body, 'PlatformIO (#29)</a>') || str_contains($body, 'Migrasi ke PlatformIO (#29)</a>'), 'Hyperlink PlatformIO (#29)');
check(str_contains($body, 'Menghubungkan ESP32 ke WiFi (#4)</a>'), 'Hyperlink WiFi (#4) opsional');
check(! preg_match('/<li>\s*<p>/', $body), 'Tidak ada li berisi p');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(str_contains($sanitized, '<svg'), 'SVG lolos sanitizer');

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
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article2Seeder', '--force' => true]);
        $article = Article::where('slug', $slug)->first();
        check($article !== null, 'Artikel ada setelah seed');
        check($article?->status === 'published', 'Status published');
        $catOk = in_array($article?->category?->slug, ['iot-smart-device', 'esp32-arduino'], true);
        check($catOk, 'Kategori iot-smart-device atau esp32-arduino');
        $tags = $article?->tags->pluck('slug')->all() ?? [];
        check(in_array('esp32', $tags, true), 'Tag esp32');
        check(in_array('arduino', $tags, true), 'Tag arduino');
    }
} catch (Throwable $e) {
    echo "\n! Skip Pass 2 DB: {$e->getMessage()}\n";
}

if ($checkProduction) {
    echo "\n=== Pass 3: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $html = (string) shell_exec('curl -sS --max-time 30 ' . escapeshellarg($prodUrl));
    check(str_contains($html, 'package_esp32_index.json'), 'Prod board URL');
    check(str_contains($html, 'Blink LED') && str_contains($html, '(#3)'), 'Prod link #3');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
