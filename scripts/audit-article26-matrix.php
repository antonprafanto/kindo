<?php

/**
 * Cross-matrix audit #26 — bandingkan #25 vs #26 + matrix backlink.
 * Usage: php scripts/audit-article26-matrix.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article25Seeder;
use Database\Seeders\Article26Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug26 = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';
$slug25 = 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi';
$href26 = '/artikel/' . $slug26;
$href25 = '/artikel/' . $slug25;

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

echo "=== CROSS-MATRIX AUDIT #26 ===\n\n";

foreach ([
    'Article26Seeder', 'Article25Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article11Seeder', 'Article7Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$b26 = bodyOf(Article26Seeder::class);
$b25 = bodyOf(Article25Seeder::class);

echo "--- A: Prasyarat #26 ↔ #4/#5/#25 saling merujuk ---\n\n";

check(str_contains($b26, '/artikel/menghubungkan-esp32-wifi-kirim-data-server'), '#26 body link ke #4 WiFi');
check(str_contains($b26, '/artikel/membaca-sensor-dht22-suhu-kelembaban-esp32'), '#26 body link ke #5 DHT22');
check(str_contains($b26, '/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi'), '#26 body link ke #25 ESP-NOW');
check(str_contains($b25, $href26), '#25 backlink ke #26');

echo "\n--- B: Matrix backlink 5×2 ---\n\n";

$matrix = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'               => '#25',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
];

foreach ($matrix as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->value('body') ?? '';
    check(str_contains($src, $href26), "{$label} → #26");
}

echo "\n--- C: #26 outbound ke prasyarat wajib ---\n\n";

$requiredOutbound = [
    'menghubungkan-esp32-wifi-kirim-data-server'                     => '#4',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                     => '#5',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'               => '#25',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => '#34',
];

foreach ($requiredOutbound as $target => $label) {
    check(str_contains($b26, '/artikel/' . $target), "#26 outbound wajib: {$label}");
}

echo "\n--- D: #25 ↔ #26 cross-link ---\n\n";

check(str_contains($b25, $href26), '#25 → #26 hyperlink');
check(str_contains($b26, $href25), '#26 → #25 hyperlink');
check(! str_contains($b25, 'LoRa SX1278 (#26):'), '#25 tidak orphan LoRa (#26) tanpa link');

echo "\n--- E: Paritas kualitas vs #25 ---\n\n";

check(substr_count($b26, '<h2>') >= 14, '#26 H2 ≥14 (' . substr_count($b26, '<h2>') . ')');
check(str_word_count(strip_tags($b26)) >= 1300, '#26 word count ≥1300');
check(substr_count($b26, '<table>') >= 3, '#26 minimal 3 tabel');
check(str_contains($b26, 'Pro tip'), '#26 pro tip');
check(str_contains($b26, 'Uji Coba'), '#26 uji coba');
check(str_contains($b26, 'Troubleshooting'), '#26 troubleshooting');
check(str_contains($b26, 'Langkah Selanjutnya'), '#26 langkah selanjutnya');

echo "\n--- F: Route + CI ---\n\n";

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(preg_match('/publish-article-26.*throttle:120,1/s', $routes) === 1, 'Route #26 throttle 120/menit');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
$pos25 = strpos($yml, 'Publish article 25 via deploy hook (required)');
$pos26 = strpos($yml, 'Publish article 26 via deploy hook (required)');
check($pos25 !== false && $pos26 !== false && $pos25 < $pos26, 'CI: step #25 required sebelum #26 required');
check(! preg_match('/- name: Publish article 26 via deploy hook \(required\)\s+continue-on-error:\s*true/', $yml), 'CI #26 publish step TIDAK continue-on-error');

echo "\n--- G: Sitemap lokal ---\n\n";

$sitemap = @file_get_contents(__DIR__ . '/../public/sitemap.xml');
if ($sitemap !== false) {
    $inSitemap = str_contains($sitemap, $slug26);
    check(true, 'sitemap.xml ada (' . ($inSitemap ? 'sudah punya #26 lokal' : 'belum punya #26 — normal pre-deploy') . ')');
} else {
    check(true, 'sitemap.xml tidak ada — skip');
}

echo "\n--- H: Production snapshot ---\n\n";

$code26 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug26)));
$code25 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug25)));
check($code26 === '404', "Prod #26 pre-deploy HTTP {$code26}");
check($code25 === '200', "Prod #25 live HTTP {$code25}");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
