<?php

/**
 * Deep audit artikel #27 — ESP32-CAM MJPEG streaming & capture.
 * Usage: php scripts/audit-article27-deep.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article27Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article27Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$slug = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';

echo "=== DEEP AUDIT #27 ===\n\n";

$article = Article::where('slug', $slug)->first();
check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article->title) <= 90, 'title ≤ 90 char');
check(mb_strlen($article->seo_title) <= 70, 'seo_title ≤ 70 char');
check(mb_strlen($article->seo_description) >= 80 && mb_strlen($article->seo_description) <= 160, 'seo_description 80–160 char');
check($article->read_time_minutes >= 8, 'read_time ≥ 8 menit');

$words = str_word_count(strip_tags($body));
check($words >= 1300, 'word_count ≥ 1300 (' . $words . ')');

check(substr_count($body, '<h2>') >= 14, 'H2 ≥ 14');
check(substr_count($body, '<table>') >= 3, 'Minimal 3 tabel');
check(substr_count($body, '<pre>') === substr_count($body, '</pre>'), 'Tag pre seimbang');

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
foreach (array_unique($matches[1]) as $path) {
    $targetSlug = str_replace('/artikel/', '', $path);
    if ($targetSlug === '') {
        continue;
    }
    check(Article::where('slug', $targetSlug)->exists(), "Link valid: {$path}");
}

check(str_contains($body, 'kindo_esp32'), 'User MQTT publisher konsisten');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');
check(str_contains($body, '1782977400'), 'Unix konsisten #34');
check(strtotime('2026-07-02 14:30:00 +0700') === 1782977400, 'Pasangan unix+ISO WIB valid');

$backlinkSources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'                  => '#26',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
];

foreach ($backlinkSources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check(str_contains($src?->body ?? '', $slug), "Backlink {$label} → #27");
}

check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(! preg_match('/KindoMQTT|admin123|password123/i', $body), 'Tidak ada password lemah');
check(str_contains($body, 'esp_camera'), 'Library esp_camera');
check(str_contains($body, 'MJPEG'), 'Membahas MJPEG');
check(str_contains($body, 'ESP32-CAM'), 'Menyebut ESP32-CAM');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder WiFi SSID');
check(str_contains($body, '/stream'), 'Endpoint /stream');
check(str_contains($body, '/capture'), 'Endpoint /capture');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
