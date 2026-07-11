<?php

/** Spot-check #38 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article38Seeder;

$passed = 0; $failed = 0;
$slug = 'https-sertifikat-esp32-wificlientsecure-api-rest';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article38Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());

echo "=== SPOT-CHECK #38 ===\n\n";

$links = [
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32',
    'esp32-firebase-realtime-database-sensor-cloud',
    'sd-card-spi-esp32-logging-data-sensor-offline',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22',
    'menghubungkan-esp32-wifi-kirim-data-server',
    'membaca-sensor-dht22-suhu-kelembaban-esp32',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt',
    'home-assistant-integrasi-esp32-mqtt',
    'node-red-dashboard-otomasi-iot-mqtt-esp32',
];

foreach ($links as $linkSlug) {
    $target = Article::where('slug', $linkSlug)->first();
    check($target !== null && $target->status === 'published', "Link published: /artikel/{$linkSlug}");
}

$h2 = substr_count($body, '<h2>');
$words = str_word_count(strip_tags($body));
check($h2 >= 14, "H2 count ≥14 ({$h2})");
check($words >= 900, "Word count ≥900 ({$words})");
check(str_contains($body, 'WiFiClientSecure'), 'WiFiClientSecure di konten');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, '#39') || str_contains($body, 'greenhouse'), 'Teaser #39');

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    check(str_contains(file_get_contents($docs . '/TODO.md'), $slug) || str_contains(file_get_contents($docs . '/TODO.md'), '#38'), 'TODO.md slug #38');
    check(str_contains(file_get_contents($docs . '/TODO.md'), '#38'), 'TODO.md status #38');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
