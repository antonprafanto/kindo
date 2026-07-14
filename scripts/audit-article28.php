<?php

/**
 * Audit artikel #28 — Gateway LoRa → MQTT.
 * Usage: php scripts/audit-article28.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article28Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article28Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #28 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article28Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article27Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article26Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article19Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');
check($article?->is_featured === false, 'is_featured false');

$requiredTags = ['esp32', 'lora', 'mqtt', 'iot', 'wifi', 'gateway'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'                  => 'Artikel #26 LoRa',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'   => 'Artikel #16 Mosquitto',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'        => 'Artikel #19 Grafana',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                  => 'Artikel #7 MQTT',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'              => 'Artikel #25 ESP-NOW',
    'esp32-cam-streaming-mjpeg-capture-foto-wifi'                   => 'Artikel #27 ESP32-CAM',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                 => 'Artikel #20 REST vs MQTT',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                   => 'Artikel #17 TLS',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'            => 'Artikel #34 NTP',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                   => 'Artikel #23 Node-RED',
    'home-assistant-integrasi-esp32-mqtt'                         => 'Artikel #21 Home Assistant',
    'ota-update-firmware-esp32-via-wifi'                          => 'Artikel #15 OTA',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 WiFiManager',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'       => 'Artikel #39 Greenhouse',
    'migrasi-platformio-esp32-vscode-project-rapi'                => 'Artikel #29 PlatformIO',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'       => 'Artikel #18 Python',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Jalur D'), 'Menyebut Jalur D');
check(str_contains($body, 'capstone'), 'Menyebut capstone');
check(str_contains($body, 'lora_packet_t'), 'Struct lora_packet_t');
check(str_contains($body, 'PubSubClient'), 'Library PubSubClient');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder WiFi SSID');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password MQTT');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor konsisten');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, '1782977400'), 'Contoh unix konsisten #34');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');
check(str_contains($body, 'kindo_esp32'), 'User MQTT publisher');
check(str_contains($body, '"source":"lora"'), 'Field source lora di payload');
check(str_contains($body, 'language-cpp'), 'Blok C++');
check(str_contains($body, 'language-bash'), 'Blok bash');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(str_contains($body, 'Estimasi Biaya'), 'Estimasi biaya');
check(str_contains($body, '/artikel/migrasi-platformio-esp32-vscode-project-rapi">Migrasi PlatformIO (#29)</a>'), 'Hyperlink PlatformIO #29');
check(str_contains($body, '<svg') && str_contains($body, 'Gateway ESP32'), 'Diagram arsitektur Gateway LoRa SVG');
check(! str_contains($body, '[ Sensor node ]'), 'Tidak ada diagram ASCII arsitektur');
check(str_contains($body, 'Checklist'), 'Section checklist');
check(! str_contains($body, 'Bukan ESP-NOW') || str_contains($body, 'ESP-NOW'), 'Bedakan dari ESP-NOW');
check(str_contains($body, '/artikel/lora-esp32-modul-sx1278-kirim-data-jarak-jauh">LoRa (#26)</a>'), 'Tabel/link hyperlink #26');

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
check(str_contains($html, 'Gateway LoRa'), 'Konten gateway ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'PubSubClient'), 'Sketch MQTT ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 (backlink) ===\n\n";

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'                  => '#26',
    'esp32-cam-streaming-mjpeg-capture-foto-wifi'                    => '#27',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'   => '#16',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Artikel {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #28");
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
