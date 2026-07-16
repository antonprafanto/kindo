<?php

/**
 * Audit artikel #15 — OTA Update ESP32 via WiFi.
 * Usage: php scripts/audit-article15.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article15Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'ota-update-firmware-esp32-via-wifi';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article15Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #15 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article15Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article14Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article13Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article12Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article11Seeder', '--force' => true]);
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
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['esp32', 'ota', 'wifi', 'wifimanager', 'nvs', 'iot'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'menghubungkan-esp32-wifi-kirim-data-server'                     => 'Artikel #4 WiFi',
    'membuat-web-server-esp32-monitoring-sensor-dht22'             => 'Artikel #6 web server',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'   => 'Artikel #12 NVS',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                  => 'Artikel #14 OLED',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                      => 'Artikel #13 BME280',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => 'Artikel #11 deep sleep',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'cara-install-arduino-ide-setup-esp32-board-manager'             => 'Artikel #2 Arduino IDE',
    'home-assistant-integrasi-esp32-mqtt'                            => 'Artikel #21 Home Assistant',
    'esphome-flash-esp32-tanpa-coding-arduino'                       => 'Artikel #22 ESPHome',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                      => 'Artikel #17 MQTT TLS',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'          => 'Artikel #39 greenhouse',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, '<svg'), 'SVG diagram arsitektur ada');
check(str_contains($body, 'WiFiManager (#12) + ArduinoOTA'), 'SVG: teks WiFiManager + ArduinoOTA');
check(str_contains($body, 'USB flash #1'), 'SVG: label USB flash pertama');
check(str_contains($body, 'WiFi OTA UDP'), 'SVG: label WiFi OTA UDP');
check(str_contains($body, 'Bootloader swap app0'), 'SVG: partition swap app0/app1');
check(str_contains($body, 'MQTT metadata (#16)'), 'SVG: MQTT metadata #16');

check(str_contains($body, 'deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>'), 'Hyperlink Jalur A #11');
check(str_contains($body, 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a>'), 'Hyperlink Jalur A #12');
check(str_contains($body, 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt">BME280 (#13)</a>'), 'Hyperlink Jalur A #13');
check(str_contains($body, 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED (#14)</a>'), 'Hyperlink Jalur A #14');
check(str_contains($body, 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">artikel #12</a>'), 'Hyperlink #12 di Yang Kamu Butuhkan');
check(str_contains($body, 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (#12)</a></li>'), 'Hyperlink NVS #12 di Penjelasan');
check(str_contains($body, 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">provisioning (#12)</a>'), 'Hyperlink provisioning #12');
check(str_contains($body, 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">NVS (pola #12)</a>'), 'Hyperlink pola NVS #12 Keamanan');
check(str_contains($body, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a>'), 'Hyperlink greenhouse #39');

check(str_contains($body, 'GANTI_PASSWORD_OTA'), 'Placeholder password OTA');
check(! str_contains($body, 'kindo_ota_2026'), 'Tidak ada password literal kindo_ota_2026');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(str_contains($sanitized, '<svg'), 'SVG lolos sanitizer');
check(str_contains($sanitized, 'Bootloader swap app0'), 'SVG swap partition lolos sanitizer');

check(str_contains($body, 'Jalur A'), 'Menyebut Jalur A');
check(str_contains($body, 'Over-The-Air') || str_contains($body, 'OTA'), 'Menjelaskan OTA');
check(str_contains($body, 'ArduinoOTA'), 'Library ArduinoOTA');
check(str_contains($body, 'ArduinoOTA.handle()'), 'Kode: ArduinoOTA.handle()');
check(str_contains($body, 'ArduinoOTA.begin()'), 'Kode: ArduinoOTA.begin()');
check(str_contains($body, 'setPassword'), 'Kode: setPassword OTA');
check(str_contains($body, 'setHostname'), 'Kode: setHostname');
check(str_contains($body, 'FIRMWARE_VERSION'), 'Konstanta FIRMWARE_VERSION');
check(str_contains($body, 'Partition Scheme'), 'Partition Scheme Arduino IDE');
check(str_contains($body, 'app0'), 'Menjelaskan partition app0/app1');
check(str_contains($body, 'WiFiManager'), 'WiFiManager');
check(str_contains($body, 'tzapu'), 'Library WiFiManager (tzapu)');
check(str_contains($body, 'setConfigPortalTimeout'), 'Kode: setConfigPortalTimeout');
check(str_contains($body, 'wm.resetSettings'), 'Troubleshooting wm.resetSettings');
check(str_contains($body, 'espota'), 'Troubleshooting espota / IP manual');
check(str_contains($body, 'OTA_BEGIN_ERROR'), 'Kode: OTA_BEGIN_ERROR handler');
check(str_contains($body, 'OTA_CONNECT_ERROR'), 'Kode: OTA_CONNECT_ERROR handler');
check(str_contains($body, 'app1'), 'Menjelaskan partition app1');
check(str_contains($body, 'v3.x'), 'Board esp32 v3.x disebut');
check(str_contains($body, 'Compile error WiFiManager'), 'Troubleshooting WiFiManager compile');
check(str_contains($body, '<table>'), 'Ada tabel perbandingan');
check(str_contains($body, 'mqtt-tls-qos-lwt-retained-mosquitto-esp32">MQTT TLS (#17)</a>'), 'Teaser MQTT TLS #17');
check(str_contains($body, 'KindoESP32-Setup'), 'AP portal KindoESP32-Setup');
check(str_contains($body, 'OTA_AUTH_ERROR'), 'Troubleshooting OTA_AUTH_ERROR');
check(str_contains($body, 'Sketch too big'), 'Troubleshooting sketch size');
check(str_contains($body, '2.4 GHz'), 'Troubleshooting WiFi 2.4 GHz');
check(str_contains($body, 'Keamanan &amp; Produksi'), 'Section Keamanan & Produksi');
check(str_contains($body, 'Pro tip'), 'Pro tip network port');
check(str_contains($body, 'greenhouse'), 'Teaser capstone #39');
check(str_contains($body, 'Seri 2'), 'Menyebut Seri 2');
check(str_contains($body, 'language-arduino'), 'Blok kode Arduino');
check(! preg_match('/const char\*\s+ssid\s*=\s*"[^"]+"/', $body), 'Tidak hardcode ssid di sketch');
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
check(str_contains($html, 'ArduinoOTA'), 'Konten OTA ter-render');
check(str_contains($html, '<svg'), 'SVG diagram ter-render di halaman');
check(str_contains($html, 'Bootloader swap app0'), 'SVG partition swap ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
$kernel->terminate($request, $response);

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a14 = Article::where('slug', 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c')->first();
check($a14 !== null, 'Artikel #14 ada');
check(str_contains($a14?->body ?? '', 'ota-update-firmware-esp32-via-wifi'), 'Artikel #14 backlink → #15');

$a12 = Article::where('slug', 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode')->first();
check($a12 !== null, 'Artikel #12 ada');
check(str_contains($a12?->body ?? '', 'ota-update-firmware-esp32-via-wifi'), 'Artikel #12 backlink → #15');

$a13 = Article::where('slug', 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt')->first();
check($a13 !== null, 'Artikel #13 ada');
check(str_contains($a13?->body ?? '', 'ota-update-firmware-esp32-via-wifi'), 'Artikel #13 backlink → #15');

$a11 = Article::where('slug', 'deep-sleep-esp32-sensor-dht22-hemat-baterai')->first();
check($a11 !== null, 'Artikel #11 ada');
check(str_contains($a11?->body ?? '', 'ota-update-firmware-esp32-via-wifi'), 'Artikel #11 backlink → #15');

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
check($a16 !== null, 'Artikel #16 ada');
check(str_contains($a16?->body ?? '', 'ota-update-firmware-esp32-via-wifi'), 'Artikel #16 backlink → #15');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check($a10 !== null, 'Artikel #10 ada');
check(str_contains($a10?->body ?? '', 'ota-update-firmware-esp32-via-wifi'), 'Artikel #10 indeks → #15');

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
