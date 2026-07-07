<?php

/**
 * Manual supplemental audit #20.
 * Usage: php scripts/audit-article20-manual.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #20 ===\n\n";

foreach ([
    'Article20Seeder', 'Article19Seeder', 'Article18Seeder', 'Article10Seeder',
    'Article7Seeder', 'Article6Seeder', 'Article17Seeder', 'Article16Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

foreach ([
    'Article19Seeder.php', 'Article18Seeder.php', 'Article10Seeder.php',
    'Article7Seeder.php', 'Article6Seeder.php',
] as $file) {
    $content = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! str_contains($content, 'Artikel #20'), "{$file}: tidak ada teks orphan 'Artikel #20'");
}

$sources = [
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => '#18',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
];

foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    check(str_contains($b, $href), "{$lbl} hyperlink → #20");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle20'), 'DeployController publishArticle20');
check(str_contains($deploy, 'Article6Seeder'), 'Hook re-seed Article6Seeder');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 20 via deploy hook \(required\)/', $yml) === 1, 'CI hook #20 required');
check(strpos($yml, 'publish-article-19') < strpos($yml, 'publish-article-20'), 'CI: hook #19 sebelum #20');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check(str_contains($a10?->body ?? '', 'enam belas artikel'), '#10 indeks enam belas artikel');
check(substr_count($a10?->body ?? '', $href) >= 1, '#10 punya link #20');
check(! str_contains($a10?->body ?? '', 'REST API vs MQTT untuk proyek IoT (#20)'), '#10 tidak ada teaser orphan #20');

$a19 = Article::where('slug', 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt')->first();
check(str_contains($a19?->body ?? '', $href), '#19 hyperlink → #20');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
