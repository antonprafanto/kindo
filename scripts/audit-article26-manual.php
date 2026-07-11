<?php

/**
 * Manual supplemental audit #26.
 * Usage: php scripts/audit-article26-manual.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #26 ===\n\n";

foreach ([
    'Article26Seeder', 'Article25Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article11Seeder', 'Article7Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

foreach ([
    'Article25Seeder.php', 'Article10Seeder.php', 'Article20Seeder.php',
    'Article11Seeder.php', 'Article7Seeder.php',
] as $file) {
    $content = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! str_contains($content, 'Artikel #26'), "{$file}: tidak ada teks orphan 'Artikel #26'");
}

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'               => '#25',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
];

foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    check(str_contains($b, $href), "{$lbl} hyperlink → #26");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle26'), 'DeployController publishArticle26');
check(str_contains($deploy, 'Article25Seeder'), 'Hook re-seed Article25Seeder');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 26 via deploy hook \(required\)/', $yml) === 1, 'CI hook #26 required');
check(strpos($yml, 'publish-article-25') < strpos($yml, 'publish-article-26'), 'CI: hook #25 sebelum #26');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check(str_contains($a10?->body ?? '', 'dua puluh dua artikel'), '#10 indeks dua puluh dua artikel');
check(substr_count($a10?->body ?? '', $href) >= 1, '#10 punya link #26');
check(! str_contains($a10?->body ?? '', 'LoRa SX1278 — komunikasi jarak jauh antar ESP32 (#26)'), '#10 tidak ada teaser orphan #26');

$a25 = Article::where('slug', 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi')->first();
check(str_contains($a25?->body ?? '', $href), '#25 hyperlink → #26');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
