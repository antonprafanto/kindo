<?php

/**
 * Manual supplemental audit #34 — cek di luar audit-article34*.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article34Seeder;

$passed = 0;
$failed = 0;
$warn = 0;

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

$ref = new ReflectionClass(Article34Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== MANUAL SUPPLEMENTAL AUDIT #34 ===\n\n";

echo "--- Konsistensi firmware vs #17 ---\n\n";

check(
    preg_match('/koneksiMQTT[\s\S]*?percobaan\s*(?:&lt;|<)\s*5/', $body) === 1,
    'koneksiMQTT() punya max 5 retry (seperti #17)'
);

check(
    preg_match('/bool publishSensorDenganWaktu[\s\S]{0,600}ambilTimestampISO/', $body) === 1,
    'publishSensor memakai ambilTimestampISO (bukan duplikasi/re-sync NTP)'
);

check(str_contains($body, 'ambilTimestampISO'), 'Helper ambilTimestampISO ada');
check(
    preg_match('/publishSensorDenganWaktu[\s\S]*?ambilTimestampISO/', $body) === 1,
    'publishSensor memakai ambilTimestampISO (bukan duplikasi logic)'
);

check(str_contains($body, 'Serial.println(mqttClient.state())') || str_contains($body, 'mqttClient.state()'), 'Debug rc MQTT saat gagal connect');

echo "\n--- Link & referensi seri ---\n\n";

check(str_contains($body, 'home-assistant-integrasi-esp32-mqtt'), 'Link artikel #21 Home Assistant');

$a7 = Article::where('slug', 'memahami-mqtt-esp32-kirim-data-sensor-broker')->first();
check(
    str_contains($a7?->body ?? '', 'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'),
    '#7 backlink ke #34 (opsional forward link)',
    true
);

echo "\n--- ISO & timezone ---\n\n";

check(str_contains($body, '+07:00') || str_contains($body, 'timezone') || str_contains($body, 'zona'), 'Dokumentasi timezone/offset untuk parsing #18');

$isoExample = '2026-07-02T14:30:00';
$unixExample = 1782977400;
check(str_contains($body, $isoExample), 'Contoh ISO timestamp ada');
check(str_contains($body, (string) $unixExample), 'Contoh unix epoch ada');
check(
    strtotime('2026-07-02 14:30:00 +0700') === $unixExample,
    'Pasangan contoh ISO + unix konsisten (WIB)'
);

echo "\n--- Deploy hook completeness ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'Article7Seeder'), 'Deploy hook re-seed #7 (jika ada backlink)');
check(str_contains($deploy, 'runDuplicateBme280Cleanup'), 'Deploy hook cleanup BME280 duplikat');

echo "\n--- Konten density ---\n\n";

$words = str_word_count(strip_tags($body));
check($words >= 1300, "word_count ≥ 1300 ({$words})");
check($words >= 1350, "word_count ≥ 1350 (buffer atas minimum)", true);

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
