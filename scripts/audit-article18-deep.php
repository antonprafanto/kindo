<?php

/**
 * Deep audit artikel #18 — Python subscriber MQTT → MySQL.
 * Usage: php scripts/audit-article18-deep.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article18Seeder;

$passed = 0;
$failed = 0;
$warn   = 0;

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

$ref = new ReflectionClass(Article18Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$slug = 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32';
$article = Article::where('slug', $slug)->first();

echo "=== DEEP AUDIT #18 — Pass A: Metadata ===\n\n";

check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article->title) <= 90, 'title ≤ 90 char (' . mb_strlen($article->title) . ')');
check(mb_strlen($article->seo_title) <= 70, 'seo_title ≤ 70 char');
check(mb_strlen($article->seo_description) >= 80 && mb_strlen($article->seo_description) <= 160, 'seo_description 80–160 char');
check($article->read_time_minutes >= 7, 'read_time ≥ 7 menit (' . $article->read_time_minutes . ')');
check(preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) === 1, 'slug format URL-safe');

$words = str_word_count(strip_tags($body));
check($words >= 1200, 'word_count ≥ 1200 (' . $words . ')');

echo "\n=== Pass B: HTML & struktur ===\n\n";

check(substr_count($body, '<h2>') >= 12, 'H2 ≥ 12 (' . substr_count($body, '<h2>') . ')');
check(substr_count($body, '<table>') >= 1 || substr_count($body, '<pre>') >= 5, 'Minimal tabel atau blok kode cukup');
check(substr_count($body, '<pre>') === substr_count($body, '</pre>'), 'Tag pre seimbang');
check(substr_count($body, '<code') === substr_count($body, '</code>'), 'Tag code seimbang');
check(substr_count($body, '<blockquote>') === substr_count($body, '</blockquote>'), 'Tag blockquote seimbang');
check(! str_contains($body, '<h1>'), 'Tidak ada h1 ganda di body');
check(! preg_match('/href="\/artikel\/"/', $body), 'Tidak ada link /artikel/ kosong');

echo "\n=== Pass C: Validasi link internal ===\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
foreach (array_unique($matches[1]) as $path) {
    $targetSlug = str_replace('/artikel/', '', $path);
    if ($targetSlug === '') {
        continue;
    }
    check(Article::where('slug', $targetSlug)->exists(), "Link valid: {$path}");
}

echo "\n=== Pass D: Python & MySQL ===\n\n";

check(str_contains($body, 'import paho.mqtt.client as mqtt'), 'Import paho-mqtt');
check(str_contains($body, 'import mysql.connector'), 'Import mysql.connector');
check(str_contains($body, 'on_message'), 'Callback on_message');
check(str_contains($body, 'INSERT INTO sensor_readings'), 'Query INSERT');
check(str_contains($body, 'datetime.fromtimestamp'), 'Konversi unix → datetime');
check(str_contains($body, 'CallbackAPIVersion.VERSION2'), 'paho CallbackAPIVersion v2');
check(str_contains($body, 'kindo-mqtt-mysql.service'), 'Unit file systemd');
check(str_contains($body, 'MQTT_PORT=8883'), 'Opsi port TLS 8883');
check(str_contains($body, 'client.tls_set'), 'tls_set di Python');
check(! str_contains($body, 'password123'), 'Tidak ada password contoh lemah');

echo "\n=== Pass E: Konsistensi Seri 2 ===\n\n";

check(str_contains($body, 'kindo_esp32'), 'User publisher ESP32 konsisten #16');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder MQTT publisher');
check(str_contains($body, '1782977400'), 'Unix timestamp konsisten #34');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');

$backlinkSources = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => '#7',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'             => '#34',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => '#17',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => '#13',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                 => '#14',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => '#23',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
];

foreach ($backlinkSources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Sumber {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Backlink {$label} → #18");
}

echo "\n=== Pass F: Keamanan & placeholder ===\n\n";

check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(str_contains($body, '.env'), 'File env untuk kredensial');
check(! preg_match('/KindoMQTT|KindoMySQL|admin123/i', $body), 'Tidak ada password literal brand');

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
