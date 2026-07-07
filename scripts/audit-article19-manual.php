<?php

/**
 * Manual supplemental audit #19.
 * Usage: php scripts/audit-article19-manual.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== MANUAL AUDIT #19 ===\n\n";

foreach ([
    'Article19Seeder', 'Article18Seeder', 'Article34Seeder', 'Article17Seeder',
    'Article16Seeder', 'Article10Seeder', 'Article7Seeder', 'Article13Seeder',
    'Article14Seeder', 'Article23Seeder', 'Article24Seeder', 'Article21Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

foreach ([
    'Article19Seeder.php', 'Article18Seeder.php', 'Article16Seeder.php', 'Article17Seeder.php',
    'Article34Seeder.php', 'Article10Seeder.php', 'Article7Seeder.php',
    'Article13Seeder.php',     'Article14Seeder.php', 'Article23Seeder.php', 'Article24Seeder.php', 'Article21Seeder.php',
] as $file) {
    $content = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! str_contains($content, 'Artikel #19'), "{$file}: tidak ada teks orphan 'Artikel #19'");
}

$href20 = '/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
$a19body = Article::where('slug', 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt')->value('body') ?? '';
check(str_contains($a19body, $href20), '#19 hyperlink → #20');
check(! str_contains(file_get_contents(__DIR__ . '/../database/seeders/Article19Seeder.php'), 'Artikel #20'), 'Article19Seeder: tidak ada teks orphan Artikel #20');

$sources = [
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => '#18',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'             => '#34',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => '#17',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => '#7',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => '#13',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                 => '#14',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => '#23',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
    'home-assistant-integrasi-esp32-mqtt'                        => '#21',
];

foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    check(str_contains($b, $href), "{$lbl} hyperlink → #19");
}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle19'), 'DeployController publishArticle19');
check(str_contains($deploy, 'Article24Seeder'), 'Hook re-seed Article24Seeder');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(preg_match('/Publish article 19 via deploy hook \(required\)/', $yml) === 1, 'CI hook #19 required');
check(strpos($yml, 'publish-article-18') < strpos($yml, 'publish-article-19'), 'CI: hook #18 sebelum #19');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check(str_contains($a10?->body ?? '', 'lima belas artikel'), '#10 indeks lima belas artikel (post-#20)');
check(substr_count($a10?->body ?? '', $href) >= 1, '#10 punya link #19');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
