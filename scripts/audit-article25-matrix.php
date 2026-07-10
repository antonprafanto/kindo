<?php

/**
 * Cross-matrix audit #25 — bandingkan #20 vs #25 + matrix backlink.
 * Usage: php scripts/audit-article25-matrix.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article20Seeder;
use Database\Seeders\Article25Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug25 = 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi';
$slug20 = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
$href25 = '/artikel/' . $slug25;
$href20 = '/artikel/' . $slug20;

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

echo "=== CROSS-MATRIX AUDIT #25 ===\n\n";

foreach ([
    'Article25Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article11Seeder', 'Article7Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$b25 = bodyOf(Article25Seeder::class);
$b20 = bodyOf(Article20Seeder::class);

echo "--- A: Prasyarat #25 ↔ #4/#7 saling merujuk ---\n\n";

check(str_contains($b25, '/artikel/menghubungkan-esp32-wifi-kirim-data-server'), '#25 body link ke #4 WiFi');
check(str_contains($b25, '/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker'), '#25 body link ke #7 MQTT');
check(str_contains(Article::where('slug', 'memahami-mqtt-esp32-kirim-data-sensor-broker')->value('body') ?? '', $href25), '#7 backlink ke #25');

echo "\n--- B: Matrix backlink 4×2 ---\n\n";

$matrix = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
];

foreach ($matrix as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->value('body') ?? '';
    check(str_contains($src, $href25), "{$label} → #25");
}

echo "\n--- C: #25 outbound ke prasyarat wajib ---\n\n";

$requiredOutbound = [
    'menghubungkan-esp32-wifi-kirim-data-server'                     => '#4',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => '#34',
];

foreach ($requiredOutbound as $target => $label) {
    check(str_contains($b25, '/artikel/' . $target), "#25 outbound wajib: {$label}");
}

echo "\n--- D: #20 ↔ #25 cross-link ---\n\n";

check(str_contains($b20, $href25), '#20 → #25 hyperlink');
check(str_contains($b25, $href20), '#25 → #20 hyperlink');
check(! str_contains($b20, 'ESP-NOW (#25):'), '#20 tidak orphan ESP-NOW (#25)');

echo "\n--- E: Paritas kualitas vs #20 ---\n\n";

check(substr_count($b25, '<h2>') >= 14, '#25 H2 ≥14 (' . substr_count($b25, '<h2>') . ')');
check(str_word_count(strip_tags($b25)) >= 1300, '#25 word count ≥1300');
check(substr_count($b25, '<table>') >= 1, '#25 minimal 1 tabel');
check(str_contains($b25, 'Pro tip'), '#25 pro tip');
check(str_contains($b25, 'Uji Coba'), '#25 uji coba');
check(str_contains($b25, 'Troubleshooting'), '#25 troubleshooting');
check(str_contains($b25, 'Langkah Selanjutnya'), '#25 langkah selanjutnya');

echo "\n--- F: Route + CI ---\n\n";

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(preg_match('/publish-article-25.*throttle:120,1/s', $routes) === 1, 'Route #25 throttle 120/menit');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
$pos20 = strpos($yml, 'Publish article 20 via deploy hook (required)');
$pos25 = strpos($yml, 'Publish article 25 via deploy hook (required)');
check($pos20 !== false && $pos25 !== false && $pos20 < $pos25, 'CI: step #20 required sebelum #25 required');
check(! preg_match('/- name: Publish article 25 via deploy hook \(required\)\s+continue-on-error:\s*true/', $yml), 'CI #25 publish step TIDAK continue-on-error');

echo "\n--- G: Sitemap lokal ---\n\n";

$sitemap = file_get_contents(__DIR__ . '/../public/sitemap.xml');
check(str_contains($sitemap, $slug25), 'sitemap.xml mengandung slug #25');

echo "\n--- H: Production snapshot ---\n\n";

$code25 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug25)));
$code26 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh')));
$code20 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug20)));
check($code25 === '200', "Prod #25 live HTTP {$code25}");
check($code26 === '404', "Prod #26 pre-deploy HTTP {$code26}");
check($code20 === '200', "Prod #20 live HTTP {$code20}");

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
