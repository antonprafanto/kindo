<?php

/**
 * Manual supplemental audit #25.
 * Usage: php scripts/audit-article25-manual.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #25 ===\n\n";

foreach ([
    'Article25Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article11Seeder', 'Article7Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

foreach ([
    'Article20Seeder.php', 'Article10Seeder.php', 'Article11Seeder.php', 'Article7Seeder.php',
] as $file) {
    $content = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! str_contains($content, 'Artikel #25'), "{$file}: tidak ada teks orphan 'Artikel #25'");
}

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
];

foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    check(str_contains($b, $href), "{$lbl} hyperlink → #25");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle25'), 'DeployController publishArticle25');
check(str_contains($deploy, 'Article7Seeder'), 'Hook re-seed Article7Seeder');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 25 via deploy hook \(required\)/', $yml) === 1, 'CI hook #25 required');
check(strpos($yml, 'publish-article-20') < strpos($yml, 'publish-article-25'), 'CI: hook #20 sebelum #25');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check(str_contains($a10?->body ?? '', 'dua puluh artikel'), '#10 indeks dua puluh artikel');
check(substr_count($a10?->body ?? '', $href) >= 1, '#10 punya link #25');
check(str_contains($a10?->body ?? '', 'esp32-cam-streaming-mjpeg-capture-foto-wifi'), '#10 punya link #27 di indeks');
check(! str_contains($a10?->body ?? '', 'ESP-NOW antar ESP32 tanpa router WiFi (#25)'), '#10 tidak ada teaser orphan #25');

$a20 = Article::where('slug', 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32')->first();
check(str_contains($a20?->body ?? '', $href), '#20 hyperlink → #25');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
