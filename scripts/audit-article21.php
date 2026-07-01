<?php

/**
 * Audit artikel #21 — Home Assistant + ESP32 MQTT.
 * Usage: php scripts/audit-article21.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article21Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'home-assistant-integrasi-esp32-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article21Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #21 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article21Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article15Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article9Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article8Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article6Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['esp32', 'mqtt', 'iot', 'homeassistant', 'smarthome', 'relay'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'membuat-web-server-esp32-monitoring-sensor-dht22'             => 'Artikel #6 web server',
    'kontrol-lampu-esp32-mqtt-relay'                                 => 'Artikel #8 relay',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek'                   => 'Artikel #9 gabungan',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'   => 'Artikel #12 NVS',
    'ota-update-firmware-esp32-via-wifi'                              => 'Artikel #15 OTA',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                      => 'Artikel #13 BME280',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Jalur C'), 'Menyebut Jalur C');
check(str_contains($body, 'Home Assistant'), 'Menyebut Home Assistant');
check(str_contains($body, 'configuration.yaml'), 'Konfigurasi configuration.yaml');
check(str_contains($body, 'value_template'), 'MQTT sensor value_template');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor DHT22 konsisten');
check(str_contains($body, 'kodingindonesia/esp32/lampu/kontrol'), 'Topic relay konsisten');
check(str_contains($body, 'optimistic: true'), 'MQTT switch optimistic: true (cocok sketch #9)');
check(str_contains($body, 'optimistic: false'), 'Menjelaskan kapan optimistic: false');
check(str_contains($body, 'mqttClient.publish(topicKontrol'), 'Tips publish state relay balik');
check(str_contains($body, 'mqttClient.loop()'), 'Troubleshooting mqttClient.loop()');
check(str_contains($body, 'mosquitto_pub'), 'Uji coba mosquitto_pub relay');
check(str_contains($body, 'Artikel #17'), 'Teaser MQTT TLS #17');
check(str_contains($body, 'numeric_state'), 'Automasi numeric_state');
check(str_contains($body, 'device_class'), 'YAML device_class sensor');
check(str_contains($body, '{"suhu"'), 'Contoh payload JSON suhu');
check(str_contains($body, 'test.mosquitto.org'), 'Peringatan jangan pakai broker publik');
check(str_contains($body, 'payload_on'), 'MQTT switch payload_on');
check(str_contains($body, 'docker-compose'), 'Install Docker Compose');
check(str_contains($body, '8123'), 'Port Home Assistant 8123');
check(str_contains($body, 'Devices &amp; Services'), 'UI Devices & Services');
check(str_contains($body, 'unique_id'), 'unique_id entitas MQTT');
check(str_contains($body, 'language-yaml'), 'Blok kode YAML');
check(str_contains($body, 'language-bash'), 'Blok kode bash mosquitto_sub');
check(str_contains($body, '<table>'), 'Ada tabel perbandingan');
check(str_contains($body, 'Keamanan &amp; Produksi'), 'Section Keamanan & Produksi');
check(str_contains($body, 'Pro tip'), 'Pro tip unique_id');
check(str_contains($body, 'Artikel #22'), 'Teaser ESPHome #22');
check(str_contains($body, 'Artikel #24'), 'Teaser PIR #24');
check(str_contains($body, 'greenhouse'), 'Teaser capstone #39');
check(str_contains($body, 'Seri 2'), 'Menyebut Seri 2');
check(str_contains($body, '2.4 GHz'), 'Troubleshooting WiFi 2.4 GHz');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = preg_match_all('/<h2>/', $body);
check($h2Count >= 10, "Minimal 10 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 5, 'read_time_minutes ≥ 5 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/artikel/' . $slug, 'GET');
$response = $kernel->handle($request);
$html = (string) $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'Home Assistant'), 'Konten HA ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'configuration.yaml'), 'YAML config ter-render');
$kernel->terminate($request, $response);

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
check($a16 !== null, 'Artikel #16 ada');
check(str_contains($a16?->body ?? '', 'home-assistant-integrasi-esp32-mqtt'), 'Artikel #16 backlink → #21');

$a15 = Article::where('slug', 'ota-update-firmware-esp32-via-wifi')->first();
check($a15 !== null, 'Artikel #15 ada');
check(str_contains($a15?->body ?? '', 'home-assistant-integrasi-esp32-mqtt'), 'Artikel #15 backlink → #21');

$a8 = Article::where('slug', 'kontrol-lampu-esp32-mqtt-relay')->first();
check($a8 !== null, 'Artikel #8 ada');
check(str_contains($a8?->body ?? '', 'home-assistant-integrasi-esp32-mqtt'), 'Artikel #8 backlink → #21');

$a9 = Article::where('slug', 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek')->first();
check($a9 !== null, 'Artikel #9 ada');
check(str_contains($a9?->body ?? '', 'home-assistant-integrasi-esp32-mqtt'), 'Artikel #9 backlink → #21');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check($a10 !== null, 'Artikel #10 ada');
check(str_contains($a10?->body ?? '', 'home-assistant-integrasi-esp32-mqtt'), 'Artikel #10 indeks → #21');

$a7 = Article::where('slug', 'memahami-mqtt-esp32-kirim-data-sensor-broker')->first();
check($a7 !== null, 'Artikel #7 ada');
check(str_contains($a7?->body ?? '', 'home-assistant-integrasi-esp32-mqtt'), 'Artikel #7 backlink → #21');

$a6 = Article::where('slug', 'membuat-web-server-esp32-monitoring-sensor-dht22')->first();
check($a6 !== null, 'Artikel #6 ada');
check(str_contains($a6?->body ?? '', 'home-assistant-integrasi-esp32-mqtt'), 'Artikel #6 backlink → #21 (langkah selanjutnya)');

check(
    str_contains($a6?->body ?? '', 'home-assistant-integrasi-esp32-mqtt') &&
    preg_match('/diintegrasikan dengan.*home-assistant-integrasi-esp32-mqtt/', $a6?->body ?? ''),
    'Artikel #6 body pendahuluan → #21'
);

check(str_contains($a8?->body ?? '', 'broker-mosquitto-pribadi'), 'Artikel #8 backlink → #16 broker');

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
