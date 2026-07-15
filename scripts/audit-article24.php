<?php

/**
 * Audit artikel #24 — PIR + lampu MQTT dengan debounce.
 * Usage: php scripts/audit-article24.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article24Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'sensor-gerak-pir-esp32-lampu-mqtt-debounce';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article24Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #24 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article24Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article23Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article22Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article21Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article9Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article8Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['esp32', 'pir', 'mqtt', 'iot', 'smarthome', 'homeassistant', 'relay', 'nodered'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'kontrol-lampu-esp32-mqtt-relay'                                 => 'Artikel #8 relay',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'home-assistant-integrasi-esp32-mqtt'                          => 'Artikel #21 Home Assistant',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                      => 'Artikel #23 Node-RED',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek'                   => 'Artikel #9 gabungan',
    'esphome-flash-esp32-tanpa-coding-arduino'                       => 'Artikel #22 ESPHome',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                      => 'Artikel #17 TLS',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => 'Artikel #18 Python',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => 'Artikel #19 Grafana',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'             => 'Artikel #34 NTP',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'        => 'Artikel #39 Greenhouse',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, '<svg'), 'SVG diagram arsitektur ada');
check(str_contains($body, 'Mosquitto #16'), 'SVG: teks Mosquitto #16');
check(str_contains($body, 'HC-SR501 PIR'), 'SVG: teks HC-SR501 PIR');
check(str_contains($body, 'Home Assistant (#21)'), 'SVG: Home Assistant (#21)');
check(str_contains($body, 'Node-RED (#23)'), 'SVG: Node-RED (#23)');
check(! str_contains($body, '[ HC-SR501 PIR ]'), 'ASCII diagram sudah dihapus ([ HC-SR501 PIR ])');
check(! str_contains($body, '+-- Home Assistant'), 'ASCII diagram sudah dihapus (+-- Home Assistant)');

check(str_contains($body, 'broker pribadi <a href'), 'Hyperlink #16 di Troubleshooting');
check(str_contains($body, 'greenhouse (#39)</a>'), 'Hyperlink greenhouse #39 di Langkah Selanjutnya');

check(str_contains($body, 'Jalur C'), 'Menyebut Jalur C');
check(str_contains($body, 'HC-SR501'), 'Modul PIR HC-SR501');
check(str_contains($body, 'kodingindonesia/esp32/pir/gerak'), 'Topic PIR konsisten');
check(str_contains($body, 'kodingindonesia/esp32/lampu/kontrol'), 'Topic relay konsisten');
check(str_contains($body, 'DEBOUNCE_MS'), 'Konstanta debounce');
check(str_contains($body, 'HOLD_MS'), 'Konstanta hold time / hysteresis');
check(str_contains($body, 'hysteresis'), 'Sebut hysteresis eksplisit');
check(str_contains($body, 'otomasiAktif &amp;&amp; lampuMenyala'), 'handlePirEvent perpanjang hold saat lampu nyala');
check(str_contains($body, 'mqttClient.loop()'), 'Sebut mqttClient.loop()');
check(! str_contains($body, 'publishStatus(nyala)'), 'Tidak ada publishStatus(nyala) salah');
check(str_contains($body, 'GPIO 34–39'), 'GPIO input-only 34–39 (bukan GPIO 27)');
check(! str_contains($body, 'GPIO 27 input-only'), 'Tidak ada klaim salah GPIO 27 input-only');
check(str_contains($body, 'digitalRead(PIR_PIN) == HIGH'), 'loop() polling pin HIGH perpanjang hold');
check(str_contains($body, 'retrigger (H)'), 'Sebut mode retrigger HC-SR501');
check(str_contains($body, '-m "ON"'), 'mosquitto_pub contoh ON');
check(str_contains($body, 'esphome-flash-esp32-tanpa-coding-arduino'), 'Link ESPHome #22 di pendahuluan');
check(str_contains($body, 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'), 'Link NVS #12');
check(str_contains($body, 'payload_json.gerak'), 'HA automasi pakai template gerak');
check(str_contains($body, 'warm-up PIR'), 'Checklist warm-up HC-SR501');
check(str_contains($body, 'IRAM_ATTR'), 'Interrupt ISR IRAM_ATTR');
check(str_contains($body, 'attachInterrupt'), 'attachInterrupt PIR');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password MQTT');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password literal');
check(str_contains($body, 'language-arduino'), 'Blok kode Arduino');
check(str_contains($body, 'language-yaml'), 'Blok YAML Home Assistant');
check(str_contains($body, 'language-javascript'), 'Blok function Node-RED');
check(str_contains($body, 'msg.payload.gerak'), 'Snippet filter gerak Node-RED');
check(str_contains($body, 'switch.turn_on'), 'Automasi HA switch.turn_on');
check(str_contains($body, 'mosquitto_sub'), 'Blok verifikasi mosquitto_sub');
check(str_contains($body, 'mosquitto_pub'), 'Blok uji mosquitto_pub');
check(str_contains($body, '-m "AUTO"'), 'mosquitto_pub contoh AUTO');
check(str_contains($body, 'broker <strong>pribadi</strong>'), 'Checklist broker pribadi #16');
check(str_contains($body, 'device_class: motion'), 'HA binary_sensor motion');
check(str_contains($body, 'unique_id'), 'HA unique_id');
check(str_contains($body, 'otomasiAktif'), 'Mode override AUTO');
check(str_contains($body, 'test.mosquitto.org'), 'Peringatan broker publik');
check(str_contains($body, 'Keamanan &amp; Produksi'), 'Section Keamanan & Produksi');
check(str_contains($body, 'Pro tip'), 'Pro tip hold time');
check(str_contains($body, '/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Link/hyperlink MQTT TLS #17');
check(str_contains($body, '/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'), 'Link/hyperlink Python #18');
check(str_contains($body, 'greenhouse'), 'Teaser capstone #39');
check(str_contains($body, 'Estimasi biaya'), 'Estimasi biaya');
check(str_contains($body, '2.4 GHz'), 'Troubleshooting WiFi 2.4 GHz');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 10, "Minimal 10 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 5, 'read_time_minutes ≥ 5 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(
    Illuminate\Http\Request::create('/artikel/' . $slug, 'GET')
);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'HC-SR501'), 'Konten PIR ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'DEBOUNCE_MS'), 'Kode Arduino ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a23 = Article::where('slug', 'node-red-dashboard-otomasi-iot-mqtt-esp32')->first();
check($a23 !== null, 'Artikel #23 ada');
check(str_contains($a23?->body ?? '', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce'), 'Artikel #23 backlink → #24');

$a21 = Article::where('slug', 'home-assistant-integrasi-esp32-mqtt')->first();
check($a21 !== null, 'Artikel #21 ada');
check(str_contains($a21?->body ?? '', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce'), 'Artikel #21 backlink → #24');

$a8 = Article::where('slug', 'kontrol-lampu-esp32-mqtt-relay')->first();
check($a8 !== null, 'Artikel #8 ada');
check(str_contains($a8?->body ?? '', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce'), 'Artikel #8 backlink → #24');

$a9 = Article::where('slug', 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek')->first();
check($a9 !== null, 'Artikel #9 ada');
check(str_contains($a9?->body ?? '', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce'), 'Artikel #9 backlink → #24');

$a22 = Article::where('slug', 'esphome-flash-esp32-tanpa-coding-arduino')->first();
check($a22 !== null, 'Artikel #22 ada');
check(str_contains($a22?->body ?? '', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce'), 'Artikel #22 backlink → #24');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check($a10 !== null, 'Artikel #10 (dashboard hybrid) ada');
check(str_contains($a10?->body ?? '', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce'), 'Artikel #10 backlink → #24');

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
check($a16 !== null, 'Artikel #16 ada');
check(str_contains($a16?->body ?? '', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce'), 'Artikel #16 backlink → #24');

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
