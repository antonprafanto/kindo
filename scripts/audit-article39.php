<?php

/**
 * Audit artikel #39 — Capstone Smart Greenhouse.
 * Usage: php scripts/audit-article39.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article39Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article39Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #39 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article39Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle38GreenhouseSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device');
check($article?->is_featured === true, 'is_featured true (capstone)');

$requiredTags = ['esp32', 'mqtt', 'iot', 'greenhouse', 'sensor', 'smarthome'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => 'Artikel #10 Capstone Seri 1',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                  => 'Artikel #11 Deep sleep',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => 'Artikel #13 BME280',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 Mosquitto',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => 'Artikel #19 Grafana',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => 'Artikel #24 PIR',
    'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt'               => 'Artikel #35 Soil moisture',
    'kontrol-lampu-esp32-mqtt-relay'                               => 'Artikel #8 Relay',
    'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi'                 => 'Artikel #33 Servo',
    'sd-card-spi-esp32-logging-data-sensor-offline'                => 'Artikel #37 SD Card',
    'https-sertifikat-esp32-wificlientsecure-api-rest'             => 'Artikel #38 HTTPS',
    'home-assistant-integrasi-esp32-mqtt'                          => 'Artikel #21 Home Assistant',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => 'Artikel #23 Node-RED',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'          => 'Artikel #18 Python',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => 'Artikel #34 NTP',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                    => 'Artikel #17 MQTT TLS',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'capstone'), 'Menyebut capstone');
check(str_contains($body, '29/29') || str_contains($body, '29 artikel'), 'Menyebut 29 artikel Seri 2');
check(str_contains($body, 'BME280'), 'Include BME280');
check(str_contains($body, 'kelembaban_tanah'), 'Include soil moisture JSON');
check(str_contains($body, 'kodingindonesia/esp32/bme280/data'), 'Topic BME280');
check(str_contains($body, 'kodingindonesia/esp32/tanah/data'), 'Topic tanah');
check(str_contains($body, 'kodingindonesia/esp32/pir/gerak'), 'Topic PIR gerak');
check(str_contains($body, 'kodingindonesia/esp32/cahaya/data'), 'Topic cahaya/LDR');
check(str_contains($body, 'kodingindonesia/esp32/pompa/kontrol'), 'Topic pompa kontrol');
check(str_contains($body, 'lampu/kontrol'), 'Catatan topic lampu/kontrol vs pompa (#35)');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder WiFi SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder MQTT password');
check(str_contains($body, '1782977400'), 'Contoh unix konsisten #34');
check(! str_contains($body, 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32-mqtt'), 'Tidak ada slug #18 salah (-mqtt suffix)');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'language-cpp'), 'Blok C++');
check(str_contains($body, 'language-ini'), 'Blok platformio.ini');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(str_contains($body, 'Estimasi Biaya'), 'Estimasi biaya');
check(str_contains($body, 'Indeks Lengkap Seri 2'), 'Indeks lengkap Seri 2');
check(str_contains($body, 'Checklist'), 'Section checklist');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 18, "Minimal 18 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 10, 'read_time_minutes ≥ 10 menit');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'Greenhouse') && str_contains($html, 'BME280'), 'Konten greenhouse ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'pompa/kontrol') || str_contains($html, 'kelembaban_tanah'), 'Kode MQTT ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 (backlink) ===\n\n";

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check($a10 !== null, 'Artikel #10 ada');
check(str_contains($a10?->body ?? '', $slug), 'Artikel #10 backlink → #39');
check(str_contains($a10?->body ?? '', 'dua puluh sembilan'), '#10 indeks 29 artikel');
check(str_contains($a10?->body ?? '', '29/29') || str_contains($a10?->body ?? '', 'selesai'), '#10 menyebut Seri 2 selesai');

$a38 = Article::where('slug', 'https-sertifikat-esp32-wificlientsecure-api-rest')->first();
check($a38 !== null, 'Artikel #38 ada');
check(str_contains($a38?->body ?? '', $slug), 'Artikel #38 backlink → #39');

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
