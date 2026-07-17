<?php

/**
 * Audit artikel #13 — I2C ESP32 + Sensor BME280.
 * Usage: php scripts/audit-article13.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article13Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article13Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #13 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article13Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article12Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article11Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle5Seri2Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['esp32', 'bme280', 'i2c', 'sensor', 'iot', 'mqtt', 'wifi', 'dht22'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'blink-led-esp32-tutorial-pertama-embedded-system'               => 'Artikel #3 GPIO',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                     => 'Artikel #5 DHT22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'   => 'Artikel #12 NVS',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => 'Artikel #11 deep sleep',
    'cara-install-arduino-ide-setup-esp32-board-manager'             => 'Artikel #2 Arduino IDE',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                  => 'Artikel #14 OLED',
    'ota-update-firmware-esp32-via-wifi'                              => 'Artikel #15 OTA',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'          => 'Artikel #18 Python',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'           => 'Artikel #19 Grafana',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'          => 'Artikel #39 greenhouse',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, '<svg'), 'SVG diagram ada');
check(substr_count($body, '<svg') >= 2, 'Minimal 2 SVG (arsitektur + wiring)');
check(str_contains($body, 'I2C · SDA 21 / SCL 22'), 'SVG arsitektur: label I2C bus');
check(str_contains($body, 'Mosquitto (#16)'), 'SVG arsitektur: Mosquitto (#16)');
check(str_contains($body, 'OLED (#14) next'), 'SVG arsitektur: teaser OLED #14');
check(str_contains($body, '3.3V → VCC'), 'SVG wiring: legend 3.3V → VCC');
check(str_contains($body, 'GPIO 21 → SDA'), 'SVG wiring: legend GPIO 21 → SDA');
check(str_contains($body, 'GPIO 22 → SCL'), 'SVG wiring: legend GPIO 22 → SCL');
check(! str_contains($body, 'ESP32 DevKit          BME280'), 'ASCII wiring sudah dihapus');
check(! str_contains($body, '─────── VCC'), 'ASCII wiring dashes sudah dihapus');

check(str_contains($body, 'deep-sleep-esp32-sensor-dht22-hemat-baterai">deep sleep (#11)</a>'), 'Hyperlink Jalur A #11');
check(str_contains($body, 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode">konfigurasi lapangan (#12)</a>'), 'Hyperlink Jalur A #12');
check(str_contains($body, 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32">broker (#16)</a>'), 'Hyperlink broker #16 di Uji Coba');
check(str_contains($body, 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c">OLED SSD1306 (#14)</a>'), 'Hyperlink OLED #14');
check(str_contains($body, 'ota-update-firmware-esp32-via-wifi">OTA update firmware (#15)</a>'), 'Hyperlink OTA #15');
check(str_contains($body, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">greenhouse (#39)</a>'), 'Hyperlink greenhouse #39');

check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password MQTT');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password literal');

$sanitized = app(\App\Services\ArticleHtmlSanitizer::class)->sanitize($body);
check(str_contains($sanitized, '<svg'), 'SVG lolos sanitizer');
check(substr_count($sanitized, '<svg') >= 2, 'Kedua SVG lolos sanitizer');
check(str_contains($sanitized, '3.3V → VCC'), 'Wiring legend lolos sanitizer');

check(str_contains($body, 'Jalur A'), 'Menyebut Jalur A hardware');
check(str_contains($body, 'DHT22 vs BME280'), 'Tabel perbandingan DHT22 vs BME280');
check(str_contains($body, 'SDA'), 'Menjelaskan SDA I2C');
check(str_contains($body, 'SCL'), 'Menjelaskan SCL I2C');
check(str_contains($body, 'GPIO 21'), 'Wiring SDA GPIO 21');
check(str_contains($body, 'GPIO 22'), 'Wiring SCL GPIO 22');
check(str_contains($body, '0x76'), 'Alamat I2C 0x76');
check(str_contains($body, '0x77'), 'Alamat I2C 0x77');
check(str_contains($body, 'Wire.begin'), 'Kode: Wire.begin');
check(str_contains($body, '#include &lt;Wire.h&gt;'), 'Include Wire.h');
check(str_contains($body, 'Adafruit_BME280'), 'Library Adafruit BME280');
check(str_contains($body, 'Adafruit Unified Sensor'), 'Dependency Adafruit Unified Sensor');
check(str_contains($body, 'tzapu'), 'Library WiFiManager (tzapu)');
check(str_contains($body, "Nick O'Leary"), 'Library PubSubClient disebut');
check(str_contains($body, 'readTemperature'), 'Kode: readTemperature');
check(str_contains($body, 'readHumidity'), 'Kode: readHumidity');
check(str_contains($body, 'readPressure'), 'Kode: readPressure');
check(str_contains($body, 'prefs.getString'), 'Kode: prefs.getString');
check(str_contains($body, 'prefs.putString'), 'Kode: prefs.putString');
check(str_contains($body, 'setConfigPortalTimeout'), 'Kode: setConfigPortalTimeout');
check(str_contains($body, 'autoConnect'), 'Kode: wm.autoConnect');
check(str_contains($body, 'mqtt_host'), 'NVS key mqtt_host');
check(str_contains($body, 'Publish OK'), 'Serial log Publish OK');
check(str_contains($body, 'MQTT Explorer'), 'Menyebut MQTT Explorer');
check(str_contains($body, 'bus I2C sama'), 'Catatan OLED share bus I2C (#14)');
check(str_contains($body, 'Install Library'), 'Section install library');
check(str_contains($body, 'cara-install-arduino-ide-setup-esp32-board-manager'), 'Link artikel #2 Arduino IDE');
check(str_contains($body, 'v3.x'), 'Board esp32 v3.x disebut');
check(str_contains($body, 'Keamanan &amp; Produksi'), 'Section Keamanan & Produksi');
check(str_contains($body, '25.000'), 'Estimasi harga BME280');
check(str_contains($body, 'mqttClient.state()'), 'Kode: mqttClient.state() untuk debug MQTT');
check(str_contains($body, 'rc=-2'), 'Troubleshooting MQTT rc=-2');
check(str_contains($body, 'wm.resetSettings'), 'Troubleshooting reset WiFiManager');
check(str_contains($body, 'dht22/data'), 'Referensi topic DHT22 untuk perbandingan');
check(str_contains($body, 'tekanan'), 'Field tekanan di payload');
check(str_contains($body, 'kodingindonesia/esp32/bme280/data'), 'MQTT topic BME280 konsisten');
check(str_contains($body, '{"suhu"'), 'Payload JSON suhu/kelembaban/tekanan');
check(str_contains($body, 'WiFiManagerParameter'), 'WiFiManagerParameter MQTT');
check(str_contains($body, 'mqttClient.connect'), 'MQTT connect dengan auth');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop() sebelum publish');
check(str_contains($body, 'setBufferSize(512)'), 'setBufferSize(512)');
check(str_contains($body, 'KindoESP32-Setup'), 'AP portal KindoESP32-Setup');
check(str_contains($body, 'Scan I2C'), 'Sketch scanner I2C opsional');
check(str_contains($body, 'OLED SSD1306'), 'Teaser artikel #14 OLED');
check(str_contains($body, 'OTA'), 'Teaser artikel #15 OTA');
check(str_contains($body, 'Pro tip'), 'Pro tip topic unik');
check(str_contains($body, 'Seri 2'), 'Menyebut Seri 2');
check(str_contains($body, 'language-arduino'), 'Blok kode Arduino');
check(str_contains($body, 'language-bash'), 'Blok mosquitto_sub bash');
check(str_contains($body, '<table>'), 'Ada tabel perbandingan');
check(! preg_match('/const char\*\s+ssid\s*=\s*"[^"]+"/', $body), 'Tidak hardcode ssid di sketch');
check(str_contains($body, 'rel="noopener"') || ! str_contains($body, 'target="_blank"'), 'Link eksternal aman');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = preg_match_all('/<h2>/', $body);
check($h2Count >= 10, "Minimal 10 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 6, 'read_time_minutes ≥ 6 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/artikel/' . $slug, 'GET');
$response = $kernel->handle($request);
$html = (string) $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'BME280'), 'Judul/konten BME280 ter-render');
check(str_contains($html, '<svg'), 'SVG diagram ter-render');
check(str_contains($html, 'I2C · SDA 21 / SCL 22'), 'SVG I2C bus ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'Wire.begin'), 'Kode I2C ter-render');
$kernel->terminate($request, $response);

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a12 = Article::where('slug', 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode')->first();
check($a12 !== null, 'Artikel #12 ada');
check(
    str_contains($a12?->body ?? '', 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'),
    'Artikel #12 backlink → artikel #13'
);

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
check($a16 !== null, 'Artikel #16 ada');
check(
    str_contains($a16?->body ?? '', 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'),
    'Artikel #16 backlink → artikel #13'
);

$a11 = Article::where('slug', 'deep-sleep-esp32-sensor-dht22-hemat-baterai')->first();
check($a11 !== null, 'Artikel #11 ada');
check(
    str_contains($a11?->body ?? '', 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'),
    'Artikel #11 backlink → artikel #13'
);

$a5 = Article::where('slug', 'membaca-sensor-dht22-suhu-kelembaban-esp32')->first();
check($a5 !== null, 'Artikel #5 DHT22 ada');
check(
    str_contains($a5?->body ?? '', 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'),
    'Artikel #5 backlink → artikel #13'
);

echo "\n=== Post-deploy (manual) ===\n";
echo "○ Upload cover image via Filament\n";

if ($checkProduction) {
    echo "\n=== Pass 4: Production ===\n\n";
    $prodUrl = 'https://kodingindonesia.com/artikel/' . $slug;
    $code = trim((string) shell_exec('curl -sS --max-time 30 -o NUL -w "%{http_code}" ' . escapeshellarg($prodUrl)));
    check($code === '200', "Production HTTP {$code}");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
