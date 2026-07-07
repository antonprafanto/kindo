<?php

/**
 * Extra exhaustive audit #19 — regresi & edge case di luar 3 skrip utama.
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article19Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn   = 0;
$slug   = 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt';
$href   = '/artikel/' . $slug;

function check(bool $ok, string $label, bool $warning = false): void
{
    global $passed, $failed, $warn;
    if ($warning && ! $ok) {
        echo "⚠ {$label}\n";
        $warn++;

        return;
    }
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article19Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== EXTRA EXHAUSTIVE AUDIT #19 ===\n\n";

echo "--- A: Regresi audit artikel terkait ---\n\n";

$relatedAudits = [
    'audit-article16.php' => '#16',
    'audit-article17.php' => '#17',
    'audit-article18.php' => '#18',
    'audit-article23.php' => '#23',
    'audit-article34.php' => '#34',
];

foreach ($relatedAudits as $script => $label) {
    $path = __DIR__ . '/' . $script;
    if (! file_exists($path)) {
        check(false, "{$label}: skrip {$script} tidak ada");
        continue;
    }
    exec('php ' . escapeshellarg($path) . ' 2>&1', $output, $code);
    check($code === 0, "{$label}: {$script} masih lulus setelah update backlink (exit {$code})");
    if ($code !== 0) {
        echo '    ' . implode("\n    ", array_slice($output, -4)) . "\n";
    }
}

echo "\n--- B: Semua link internal #19 valid di DB ---\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article19Seeder', '--force' => true]);
$article = Article::where('slug', $slug)->first();
$dbBody = $article?->body ?? '';

preg_match_all('/href="(\/artikel\/[^"]+)"/', $dbBody, $matches);
$uniqueLinks = array_unique($matches[1]);
check(count($uniqueLinks) >= 8, 'Minimal 8 link internal unik (' . count($uniqueLinks) . ')');

foreach ($uniqueLinks as $path) {
    $targetSlug = str_replace('/artikel/', '', $path);
    if ($targetSlug === '') {
        continue;
    }
    check(Article::where('slug', $targetSlug)->exists(), "Target DB ada: {$path}");
}

echo "\n--- C: Backlink count per sumber ---\n\n";

$sources = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => ['#7', 1],
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => ['#10', 1],
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => ['#16', 1],
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => ['#17', 1],
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => ['#18', 3],
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'             => ['#34', 4],
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => ['#13', 1],
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                 => ['#14', 1],
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => ['#23', 2],
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => ['#24', 1],
    'home-assistant-integrasi-esp32-mqtt'                        => ['#21', 1],
];

foreach ($sources as $sourceSlug => [$label, $minCount]) {
    $src = Article::where('slug', $sourceSlug)->first();
    $count = substr_count($src?->body ?? '', $href);
    check($count >= $minCount, "{$label} punya ≥{$minCount} hyperlink ke #19 (ada {$count})");
}

echo "\n--- D: Konvensi Seri 2 (topic, user, IP) ---\n\n";

$checks = [
    'kodingindonesia/esp32/dht22/data' => 'Topic sensor',
    'kindo_subscriber'                 => 'User subscriber MQTT',
    '192.168.1.50'                     => 'IP broker contoh',
    'GANTI_INFLUX_TOKEN'               => 'Placeholder token Influx',
    'GANTI_PASSWORD_SUBSCRIBER'        => 'Placeholder subscriber',
    '1782977400'                       => 'Unix konsisten #34',
    'iot_sensors'                      => 'Bucket iot_sensors',
];

foreach ($checks as $needle => $label) {
    check(str_contains($body, $needle), $label);
}

echo "\n--- E: Struktur konten wajib ---\n\n";

$requiredH2 = [
    'Pendahuluan',
    'Arsitektur',
    'Yang Kamu Butuhkan',
    'Docker Compose',
    'Telegraf',
    'Verifikasi Data',
    'Grafana',
    'Dashboard',
    'mosquitto_pub',
    'Retensi',
    'Keamanan',
    'Langkah Selanjutnya',
];

foreach ($requiredH2 as $fragment) {
    check(str_contains($body, $fragment), "Section mengandung: {$fragment}");
}

echo "\n--- F: Seeder tidak wipe cover_image ---\n\n";

$seederSource = file_get_contents(__DIR__ . '/../database/seeders/Article19Seeder.php');
check(! preg_match("/'cover_image'\s*=>/", $seederSource), 'Seeder tidak set cover_image');
check(str_contains($seederSource, 'cover_image tidak disentuh'), 'Komentar cover manual ada');

echo "\n--- G: Deploy hook urutan & kelengkapan ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
$pos19 = strpos($deploy, 'Article19Seeder');
$pos18 = strpos($deploy, "publishArticle19");
check($pos19 !== false && $pos18 !== false, 'publishArticle19 + Article19Seeder ada');

$reseedClasses = [
    'Article18Seeder', 'Article34Seeder', 'Article17Seeder', 'Article16Seeder', 'Article10Seeder',
    'Article7Seeder', 'Article13Seeder', 'Article14Seeder', 'Article23Seeder',
    'Article24Seeder', 'Article21Seeder',
];
foreach ($reseedClasses as $class) {
    if (preg_match('/function publishArticle19\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m)) {
        check(str_contains($m[1], $class), "publishArticle19 re-seed {$class}");
    }
}

echo "\n--- H: HTTP render + meta ---\n\n";

$response = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $response->getContent();
check($response->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, '<title>'), 'Tag title ada');
check(str_contains($html, 'canonical'), 'Canonical URL ada');
check(str_contains($html, 'outputs.influxdb_v2'), 'Telegraf config ter-render');
check(! str_contains($html, '&amp;amp;'), 'Tidak ada double-encode &amp;amp;');

echo "\n--- I: Pola password berbahaya ---\n\n";

$danger = ['KindoMQTT', 'password123', 'admin123', 'root:root', 'mysql123'];
foreach ($danger as $d) {
    check(! str_contains($body, $d), "Tidak ada '{$d}'");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
