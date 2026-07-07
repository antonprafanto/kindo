<?php

/**
 * Cross-matrix audit #20 — bandingkan #19 vs #20 + matrix backlink.
 * Usage: php scripts/audit-article20-matrix.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article19Seeder;
use Database\Seeders\Article20Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug20 = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
$slug19 = 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt';
$href20 = '/artikel/' . $slug20;
$href19 = '/artikel/' . $slug19;

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

echo "=== CROSS-MATRIX AUDIT #20 ===\n\n";

foreach ([
    'Article20Seeder', 'Article19Seeder', 'Article18Seeder', 'Article10Seeder',
    'Article7Seeder', 'Article6Seeder', 'Article17Seeder', 'Article16Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$b20 = bodyOf(Article20Seeder::class);
$b19 = bodyOf(Article19Seeder::class);

echo "--- A: Prasyarat #20 ↔ #6/#7 saling merujuk ---\n\n";

check(str_contains($b20, '/artikel/membuat-web-server-esp32-monitoring-sensor-dht22'), '#20 body link ke #6 REST');
check(str_contains($b20, '/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker'), '#20 body link ke #7 MQTT');
check(str_contains(Article::where('slug', 'membuat-web-server-esp32-monitoring-sensor-dht22')->value('body') ?? '', $href20), '#6 backlink ke #20');
check(str_contains(Article::where('slug', 'memahami-mqtt-esp32-kirim-data-sensor-broker')->value('body') ?? '', $href20), '#7 backlink ke #20');

echo "\n--- B: Matrix backlink 5×2 ---\n\n";

$matrix = [
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => '#18',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
];

foreach ($matrix as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->value('body') ?? '';
    check(str_contains($src, $href20), "{$label} → #20");
}

echo "\n--- C: #20 outbound ke prasyarat wajib ---\n\n";

$requiredOutbound = [
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => '#17',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => '#18',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => '#34',
];

foreach ($requiredOutbound as $target => $label) {
    check(str_contains($b20, '/artikel/' . $target), "#20 outbound wajib: {$label}");
}

echo "\n--- D: #19 ↔ #20 cross-link ---\n\n";

check(str_contains($b19, $href20), '#19 → #20 hyperlink');
check(str_contains($b20, $href19), '#20 → #19 hyperlink');
check(! str_contains($b19, 'Artikel #20:'), '#19 tidak orphan Artikel #20');

echo "\n--- E: Paritas kualitas vs #19 ---\n\n";

check(substr_count($b20, '<h2>') >= 14, '#20 H2 ≥14 (' . substr_count($b20, '<h2>') . ')');
check(str_word_count(strip_tags($b20)) >= 1300, '#20 word count ≥1300');
check(substr_count($b20, '<table>') >= 2, '#20 minimal 2 tabel');
check(str_contains($b20, 'Pro tip'), '#20 pro tip');
check(str_contains($b20, 'Uji Coba'), '#20 uji coba');
check(str_contains($b20, 'Troubleshooting'), '#20 troubleshooting');
check(str_contains($b20, 'Langkah Selanjutnya'), '#20 langkah selanjutnya');

echo "\n--- F: Route + CI ---\n\n";

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(preg_match('/publish-article-20.*throttle:120,1/s', $routes) === 1, 'Route #20 throttle 120/menit');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
$pos19 = strpos($yml, 'Publish article 19 via deploy hook (required)');
$pos20 = strpos($yml, 'Publish article 20 via deploy hook (required)');
check($pos19 !== false && $pos20 !== false && $pos19 < $pos20, 'CI: step #19 required sebelum #20 required');
check(! preg_match('/- name: Publish article 20 via deploy hook \(required\)\s+continue-on-error:\s*true/', $yml), 'CI #20 publish step TIDAK continue-on-error');

echo "\n--- G: Sitemap lokal ---\n\n";

$sitemap = file_get_contents(__DIR__ . '/../public/sitemap.xml');
check(str_contains($sitemap, $slug20), 'sitemap.xml mengandung slug #20');

echo "\n--- H: Production snapshot ---\n\n";

$code20 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug20)));
$code19 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug19)));
check($code20 === '404', "Prod #20 pre-deploy HTTP {$code20}");
check($code19 === '200', "Prod #19 live HTTP {$code19}");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
