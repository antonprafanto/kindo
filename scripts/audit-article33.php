<?php

/**
 * Audit artikel #33 — Servo & PWM ESP32.
 * Usage: php scripts/audit-article33.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article33Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article33Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #33 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article33Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article32Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle8ServoSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');
check($article?->is_featured === false, 'is_featured false');

$requiredTags = ['esp32', 'servo', 'pwm', 'mqtt', 'iot', 'motor'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'blink-led-esp32-tutorial-pertama-embedded-system'                => 'Artikel #3 Blink',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                    => 'Artikel #7 MQTT',
    'kontrol-lampu-esp32-mqtt-relay'                                  => 'Artikel #8 Relay',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek'                    => 'Artikel #9 Gabungan',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'                  => 'Artikel #10 Capstone',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'     => 'Artikel #16 Mosquitto',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                       => 'Artikel #17 TLS',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'              => 'Artikel #19 Grafana',
    'home-assistant-integrasi-esp32-mqtt'                             => 'Artikel #21 Home Assistant',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                       => 'Artikel #23 Node-RED',
    'migrasi-platformio-esp32-vscode-project-rapi'                      => 'Artikel #29 PlatformIO',
    'freertos-esp32-multi-task-sensor-wifi-mqtt'                        => 'Artikel #31 FreeRTOS',
    'bluetooth-esp32-ble-kirim-data-sensor-smartphone'                 => 'Artikel #32 BLE',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'                  => 'Artikel #34 NTP',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                      => 'Artikel #5 DHT22',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'             => 'Artikel #18 Python',
    'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt'                  => 'Artikel #35 ADC',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'           => 'Artikel #39 Greenhouse',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(str_contains($body, 'ESP32Servo'), 'Library ESP32Servo');
check(str_contains($body, 'allocateTimer'), 'API allocateTimer');
check(str_contains($body, 'servo.write'), 'API servo.write');
check(str_contains($body, 'SERVO_PIN 27') || str_contains($body, 'GPIO 27'), 'GPIO27 servo');
check(str_contains($body, 'kodingindonesia/esp32/servo/sudut'), 'Topic servo konsisten');
check(str_contains($body, 'kodingindonesia/esp32/lampu/kontrol'), 'Topic relay referensi #8');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor referensi');
check(str_contains($body, '1782977400'), 'Contoh unix konsisten #34');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, 'kindo_esp32'), 'User MQTT kindo_esp32');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder WiFi SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password MQTT');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'language-cpp'), 'Blok C++');
check(str_contains($body, 'language-ini'), 'Blok platformio.ini');
check(str_contains($body, 'language-bash'), 'Blok bash mosquitto_pub');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(str_contains($body, 'Estimasi Biaya'), 'Estimasi biaya');
check(str_contains($body, '#35') || str_contains($body, 'soil moisture'), 'Teaser ADC #35');
check(str_contains($body, 'Checklist'), 'Section checklist');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 14, "Minimal 14 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 8, 'read_time_minutes ≥ 8 menit');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(
    Illuminate\Http\Request::create('/artikel/' . $slug, 'GET')
);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'Servo') && str_contains($html, 'PWM'), 'Konten Servo ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'servo/sudut'), 'Topic servo ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 (backlink) ===\n\n";

$sources = [
    'kontrol-lampu-esp32-mqtt-relay'                   => '#8',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22' => '#10',
    'bluetooth-esp32-ble-kirim-data-sensor-smartphone' => '#32',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Artikel {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #33");
}

echo "\n=== Post-deploy (manual) ===\n";
echo "○ Upload cover image via Filament (daftar artikel → Upload Cover)\n";

if ($checkProduction) {
    echo "\n=== Pass 4: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $code = trim((string) shell_exec('curl -sS --max-time 30 -o NUL -w "%{http_code}" ' . escapeshellarg($prodUrl)));
    check($code === '200', "Production HTTP {$code}");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
