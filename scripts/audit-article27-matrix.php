<?php

/**
 * Cross-matrix audit #27 — bandingkan #26 vs #27 + matrix backlink.
 * Usage: php scripts/audit-article27-matrix.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article26Seeder;
use Database\Seeders\Article27Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug27 = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';
$slug26 = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';
$href27 = '/artikel/' . $slug27;
$href26 = '/artikel/' . $slug26;

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

echo "=== CROSS-MATRIX AUDIT #27 ===\n\n";

foreach ([
    'Article27Seeder', 'Article26Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article6Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$b27 = bodyOf(Article27Seeder::class);
$b26 = bodyOf(Article26Seeder::class);

echo "--- A: Prasyarat #27 ↔ #4/#6/#26 saling merujuk ---\n\n";

check(str_contains($b27, '/artikel/menghubungkan-esp32-wifi-kirim-data-server'), '#27 body link ke #4 WiFi');
check(str_contains($b27, '/artikel/membuat-web-server-esp32-monitoring-sensor-dht22'), '#27 body link ke #6 WebServer');
check(str_contains($b27, '/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh'), '#27 body link ke #26 LoRa');
check(str_contains($b26, $href27), '#26 backlink ke #27');

echo "\n--- B: Matrix backlink 4×2 ---\n\n";

$matrix = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'                  => '#26',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
];

foreach ($matrix as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->value('body') ?? '';
    check(str_contains($src, $href27), "{$label} → #27");
}

echo "\n--- C: #27 outbound ke prasyarat wajib ---\n\n";

$requiredOutbound = [
    'menghubungkan-esp32-wifi-kirim-data-server'                     => '#4',
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'                  => '#26',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'               => '#25',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'   => '#12',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => '#34',
];

foreach ($requiredOutbound as $target => $label) {
    check(str_contains($b27, '/artikel/' . $target), "#27 outbound wajib: {$label}");
}

echo "\n--- D: #26 ↔ #27 cross-link ---\n\n";

check(str_contains($b26, $href27), '#26 → #27 hyperlink');
check(str_contains($b27, $href26), '#27 → #26 hyperlink');
check(! str_contains($b26, 'ESP32-CAM (#27):'), '#26 tidak orphan ESP32-CAM (#27) tanpa link');

echo "\n--- E: Paritas kualitas vs #26 ---\n\n";

check(substr_count($b27, '<h2>') >= 14, '#27 H2 ≥14 (' . substr_count($b27, '<h2>') . ')');
check(str_word_count(strip_tags($b27)) >= 1300, '#27 word count ≥1300');
check(substr_count($b27, '<table>') >= 3, '#27 minimal 3 tabel');
check(str_contains($b27, 'Pro tip'), '#27 pro tip');
check(str_contains($b27, 'Uji Coba'), '#27 uji coba');
check(str_contains($b27, 'Troubleshooting'), '#27 troubleshooting');
check(str_contains($b27, 'Langkah Selanjutnya'), '#27 langkah selanjutnya');
check(str_contains($b27, 'esp_camera'), '#27 esp_camera');
check(str_contains($b27, 'MJPEG'), '#27 MJPEG');
check(str_contains($b27, '/stream') && str_contains($b27, '/capture'), '#27 endpoint stream+capture');

echo "\n--- F: Route + CI ---\n\n";

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(preg_match('/publish-article-27.*throttle:120,1/s', $routes) === 1, 'Route #27 throttle 120/menit');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
$pos26 = strpos($yml, 'Publish article 26 via deploy hook (required)');
$pos27 = strpos($yml, 'Publish article 27 via deploy hook (required)');
check($pos26 !== false && $pos27 !== false && $pos26 < $pos27, 'CI: step #26 required sebelum #27 required');
check(! preg_match('/- name: Publish article 27 via deploy hook \(required\)\s+continue-on-error:\s*true/', $yml), 'CI #27 publish step TIDAK continue-on-error');

echo "\n--- G: Sitemap lokal ---\n\n";

$sitemap = @file_get_contents(__DIR__ . '/../public/sitemap.xml');
if ($sitemap !== false) {
    $inSitemap = str_contains($sitemap, $slug27);
    check(true, 'sitemap.xml ada (' . ($inSitemap ? 'sudah punya #27 lokal' : 'belum punya #27 — normal pre-deploy') . ')');
} else {
    check(true, 'sitemap.xml tidak ada — skip');
}

echo "\n--- H: Production snapshot ---\n\n";

$code27 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug27)));
$code26 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug26)));
check($code27 === '404', "Prod #27 pre-deploy HTTP {$code27}");
check($code26 === '200', "Prod #26 live HTTP {$code26}");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
