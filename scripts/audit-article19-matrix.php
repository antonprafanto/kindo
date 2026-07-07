<?php

/**
 * Cross-matrix audit #19 — bandingkan cek #18 vs #19 + matrix backlink.
 * Usage: php scripts/audit-article19-matrix.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article18Seeder;
use Database\Seeders\Article19Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug19 = 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt';
$slug18 = 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32';
$href19 = '/artikel/' . $slug19;
$href18 = '/artikel/' . $slug18;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function bodyOf(string $seederClass): string
{
    $ref = new ReflectionClass($seederClass);
    $m = $ref->getMethod('body');
    $m->setAccessible(true);

    return $m->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== CROSS-MATRIX AUDIT #19 ===\n\n";

foreach ([
    'Article19Seeder', 'Article18Seeder', 'Article34Seeder', 'Article17Seeder',
    'Article16Seeder', 'Article10Seeder', 'Article7Seeder', 'Article13Seeder',
    'Article14Seeder', 'Article23Seeder', 'Article24Seeder', 'Article21Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$b19 = bodyOf(Article19Seeder::class);
$b18 = bodyOf(Article18Seeder::class);

echo "--- A: Prasyarat #19 ↔ #18 saling merujuk ---\n\n";

check(str_contains($b19, $href18), '#19 body link ke #18');
check(str_contains($b18, $href19), '#18 body hyperlink ke #19');
check(str_contains($b19, 'sensor_readings'), '#19 referensi skema MySQL #18');
check(str_contains($b18, 'kindo_subscriber'), '#18 user subscriber sama');
check(str_contains($b19, 'kindo_subscriber'), '#19 user subscriber sama');

echo "\n--- B: Matrix backlink 11×2 ---\n\n";

$matrix = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => '#7',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => '#17',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => '#18',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'             => '#34',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => '#13',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                 => '#14',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => '#23',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
    'home-assistant-integrasi-esp32-mqtt'                        => '#21',
];

foreach ($matrix as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->value('body') ?? '';
    check(str_contains($src, $href19), "{$label} → #19");
}

echo "\n--- C: #19 outbound ke prasyarat wajib ---\n\n";

$requiredOutbound = [
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => '#34',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => '#7',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => '#17',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => '#18',
];

foreach ($requiredOutbound as $target => $label) {
    check(str_contains($b19, '/artikel/' . $target), "#19 outbound wajib: {$label}");
}

echo "\n--- D: Paritas kualitas vs #18 ---\n\n";

check(substr_count($b19, '<h2>') >= substr_count($b18, '<h2>'), '#19 H2 ≥ #18 (' . substr_count($b19, '<h2>') . ' vs ' . substr_count($b18, '<h2>') . ')');
check(str_word_count(strip_tags($b19)) >= str_word_count(strip_tags($b18)), '#19 word count ≥ #18');
check(substr_count($b19, '<table>') >= 2, '#19 minimal 2 tabel');
check(substr_count($b19, 'Pro tip') >= 2, '#19 minimal 2 pro tip');
check(str_contains($b19, 'Uji Coba'), '#19 checklist uji coba');
check(str_contains($b19, 'Troubleshooting'), '#19 troubleshooting');
check(str_contains($b19, 'Langkah Selanjutnya'), '#19 langkah selanjutnya');

echo "\n--- E: Route + middleware + CI ---\n\n";

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(preg_match('/publish-article-19.*throttle:120,1/s', $routes) === 1, 'Route #19 throttle 120/menit');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
$pos18 = strpos($yml, 'Publish article 18 via deploy hook (required)');
$pos19 = strpos($yml, 'Publish article 19 via deploy hook (required)');
check($pos18 !== false && $pos19 !== false && $pos18 < $pos19, 'CI: step #18 required sebelum #19 required');
check(! preg_match('/- name: Publish article 19 via deploy hook \(required\)\s+continue-on-error:\s*true/', $yml), 'CI #19 publish step TIDAK continue-on-error');

echo "\n--- F: Sitemap lokal ---\n\n";

$sitemap = file_get_contents(__DIR__ . '/../public/sitemap.xml');
check(str_contains($sitemap, $slug19), 'sitemap.xml mengandung slug #19');

echo "\n--- G: Production snapshot ---\n\n";

$code19 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug19)));
$code18 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug18)));
check($code19 === '200', "Prod #19 live HTTP {$code19}");
check($code18 === '200', "Prod #18 live HTTP {$code18}");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
