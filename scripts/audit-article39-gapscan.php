<?php

/** Gap scan manual #39 — cek hal yang tidak ditangkap audit utama. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article39Seeder;
use Database\Seeders\Article10Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function body(string $class): string
{
    $ref = new ReflectionClass($class);
    $m = $ref->getMethod('body');
    $m->setAccessible(true);

    return $m->invoke($ref->newInstanceWithoutConstructor());
}

$body39 = body(Article39Seeder::class);
$body10 = body(Article10Seeder::class);

echo "=== GAP SCAN #39 ===\n\n";

preg_match_all('#href="/artikel/([^"]+)"#', $body39, $m39);
preg_match_all('#href="/artikel/([^"]+)"#', $body10, $m10);
$links39 = array_unique($m39[1]);
$links10 = array_unique($m10[1]);
sort($links39);
sort($links10);

$seri2In10 = [];
if (preg_match('#<h2>Roadmap Belajar Selanjutnya — Seri 2.*?</h2>.*?<ol>(.*?)</ol>#s', $body10, $idx10)) {
    preg_match_all('#href="/artikel/([^"]+)"#', $idx10[1], $im10);
    $seri2In10 = $im10[1];
}

$indexLinks39 = [];
if (preg_match('#<h2>Indeks Lengkap Seri 2.*?</h2>.*?<ol>(.*?)</ol>#s', $body39, $idx)) {
    preg_match_all('#href="/artikel/([^"]+)"#', $idx[1], $im);
    $indexLinks39 = $im[1];
}

check(count($indexLinks39) === 28, 'Indeks #39 punya 28 link + 1 self (ada ' . count($indexLinks39) . ')');
check(count($seri2In10) === 29, 'Indeks #10 punya 29 link Seri 2 (ada ' . count($seri2In10) . ')');
$seri2In10Without39 = array_values(array_filter($seri2In10, fn ($s) => $s !== 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'));
check($seri2In10Without39 === $indexLinks39, 'Indeks #39 slug identik dengan #10 (minus self)');

$roadmapPrereq = [
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                  => '#11',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => '#13',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => '#16',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
    'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt'               => '#35',
];
foreach ($roadmapPrereq as $slug => $label) {
    check(str_contains($body39, $slug), "Roadmap prasyarat {$label} terlink");
}

$arch = ['BME280', 'kelembaban_tanah', 'Grafana', 'pompa/kontrol', 'PIR', 'deep sleep'];
foreach ($arch as $kw) {
    check(stripos($body39, $kw) !== false, "Arsitektur roadmap: {$kw}");
}

check(str_contains($body39, 'kodingindonesia/esp32/cahaya/data'), 'Topic cahaya/LDR');
check(str_contains($body39, 'kodingindonesia/esp32/pir/gerak'), 'Topic PIR');
check(str_contains($body39, 'hysteresis'), 'Otomasi hysteresis');
check(str_contains($body39, 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'), 'Link #18 Python subscriber');
check(! str_contains($body39, 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32-mqtt'), 'Tidak ada slug #18 salah');
check(str_contains($body39, 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'), 'Link #12 WiFiManager');
check(str_contains($body39, 'ota-update-firmware-esp32-via-wifi'), 'Link #15 OTA');
check(str_contains($body39, 'esp_deep_sleep'), 'Deep sleep API');
check(str_contains($body39, 'mqtt.setCallback'), 'Relay mqtt callback');
check(str_contains($body39, 'Troubleshooting'), 'Section troubleshooting');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$routes = file_get_contents(__DIR__ . '/../routes/web.php');
$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');

check(str_contains($deploy, 'publishArticle39'), 'DeployController publishArticle39');
check(str_contains($routes, 'publish-article-39'), 'Route publish-article-39');
check(str_contains($yml, 'publish-article-39'), 'CI workflow publish-article-39');
check(class_exists(\Database\Seeders\PatchArticle38GreenhouseSeeder::class), 'PatchArticle38GreenhouseSeeder exists');

$badPasswords = ['KindoMQTT', 'kindo1234', 'password123', 'admin123'];
foreach ($badPasswords as $pw) {
    check(! str_contains($body39, $pw), "Tidak ada password literal: {$pw}");
}

foreach ($links39 as $slug) {
    check(Article::where('slug', $slug)->exists(), "Target DB ada: {$slug}");
}

$a38 = Article::where('slug', 'https-sertifikat-esp32-wificlientsecure-api-rest')->first();
check(str_contains($a38?->body ?? '', 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), '#38 patch hyperlink #39');

echo "\n=== GAP SCAN RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
