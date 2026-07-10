<?php

/**
 * Manual supplemental audit #27.
 * Usage: php scripts/audit-article27-manual.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #27 ===\n\n";

foreach ([
    'Article27Seeder', 'Article26Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article6Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

foreach ([
    'Article26Seeder.php', 'Article10Seeder.php', 'Article20Seeder.php',
    'Article6Seeder.php',
] as $file) {
    $content = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! str_contains($content, 'Artikel #27'), "{$file}: tidak ada teks orphan 'Artikel #27'");
}

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'                  => '#26',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
];

foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    check(str_contains($b, $href), "{$lbl} hyperlink → #27");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle27'), 'DeployController publishArticle27');
check(str_contains($deploy, 'Article26Seeder'), 'Hook re-seed Article26Seeder');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 27 via deploy hook \(required\)/', $yml) === 1, 'CI hook #27 required');
check(strpos($yml, 'publish-article-26') < strpos($yml, 'publish-article-27'), 'CI: hook #26 sebelum #27');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
$a10body = $a10?->body ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 19, '#10 indeks punya 19 item live (' . $indexItems . ')');
check(str_contains($a10body, 'sembilan belas artikel'), '#10 indeks sembilan belas artikel');
check(substr_count($a10body, $href) >= 1, '#10 punya link #27');
check(str_contains($a10body, 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard'), '#10 punya link #28 di indeks');
check(str_contains($a10body, '#29') || str_contains($a10body, 'PlatformIO'), '#10 teaser #29 PlatformIO');

$a26 = Article::where('slug', 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh')->first();
check(str_contains($a26?->body ?? '', $href), '#26 hyperlink → #27');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
