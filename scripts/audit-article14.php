<?php

/**
 * Audit artikel #14 — OLED SSD1306 + BME280 I2C.
 * Usage: php scripts/audit-article14.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article14Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article14Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #14 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article14Seeder', '--force' => true]);
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

$requiredTags = ['esp32', 'oled', 'i2c', 'bme280', 'sensor', 'iot', 'mqtt', 'wifi'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'blink-led-esp32-tutorial-pertama-embedded-system'               => 'Artikel #3 GPIO',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                     => 'Artikel #5 DHT22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                      => 'Artikel #13 BME280',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'   => 'Artikel #12 NVS',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => 'Artikel #11 deep sleep',
    'cara-install-arduino-ide-setup-esp32-board-manager'             => 'Artikel #2 Arduino IDE',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Jalur A'), 'Menyebut Jalur A hardware');
check(str_contains($body, 'SSD1306'), 'Menyebut SSD1306');
check(str_contains($body, '128×64') || str_contains($body, '128x64'), 'Resolusi 128x64');
check(str_contains($body, '0x3C'), 'Alamat I2C OLED 0x3C');
check(str_contains($body, '0x3D'), 'Alamat I2C OLED 0x3D');
check(str_contains($body, '0x76'), 'Alamat BME280 0x76');
check(str_contains($body, 'GPIO 21'), 'Wiring SDA GPIO 21');
check(str_contains($body, 'GPIO 22'), 'Wiring SCL GPIO 22');
check(str_contains($body, 'satu bus'), 'Konsep satu bus I2C');
check(str_contains($body, 'Adafruit_SSD1306'), 'Library Adafruit SSD1306');
check(str_contains($body, 'Adafruit_GFX'), 'Library Adafruit GFX');
check(str_contains($body, 'Adafruit_BME280'), 'Library BME280');
check(str_contains($body, 'tzapu'), 'Library WiFiManager (tzapu)');
check(str_contains($body, "Nick O'Leary"), 'Library PubSubClient disebut');
check(str_contains($body, 'tampilkanOLED'), 'Fungsi tampilkanOLED');
check(str_contains($body, 'display.display()'), 'Kode: display.display()');
check(str_contains($body, 'display.clearDisplay()'), 'Kode: display.clearDisplay()');
check(str_contains($body, 'SSD1306_SWITCHCAPVCC'), 'Kode: SSD1306_SWITCHCAPVCC');
check(str_contains($body, 'SCREEN_ADDRESS'), 'Konstanta SCREEN_ADDRESS');
check(str_contains($body, 'prefs.getString'), 'Kode: prefs.getString');
check(str_contains($body, 'setConfigPortalTimeout'), 'Kode: setConfigPortalTimeout');
check(str_contains($body, 'mqttClient.state()'), 'Kode: mqttClient.state()');
check(str_contains($body, 'kodingindonesia/esp32/bme280/data'), 'MQTT topic BME280 konsisten');
check(str_contains($body, '{"suhu"'), 'Payload JSON');
check(str_contains($body, 'setBufferSize(512)'), 'setBufferSize(512)');
check(str_contains($body, 'KindoESP32-Setup'), 'AP portal KindoESP32-Setup');
check(str_contains($body, 'Scan I2C'), 'Referensi scanner I2C');
check(str_contains($body, 'v3.x'), 'Board esp32 v3.x disebut');
check(str_contains($body, 'Keamanan &amp; Produksi'), 'Section Keamanan & Produksi');
check(str_contains($body, '20.000'), 'Estimasi harga OLED');
check(str_contains($body, 'rc=-2'), 'Troubleshooting MQTT rc=-2');
check(str_contains($body, 'wm.resetSettings'), 'Troubleshooting reset WiFiManager');
check(str_contains($body, 'layar putih') || str_contains($body, 'Layar putih'), 'Troubleshooting layar kosong');
check(str_contains($body, 'test.mosquitto.org'), 'Catatan broker publik uji hardware');
check(str_contains($body, '2.4 GHz'), 'Troubleshooting WiFi 2.4 GHz');
check(str_contains($body, 'Compile error WiFiManager'), 'Troubleshooting WiFiManager compile');
check(str_contains($body, 'Publish gagal'), 'Serial log Publish gagal');
check(str_contains($body, 'MQTT Explorer'), 'Menyebut MQTT Explorer');
check(str_contains($body, 'OTA'), 'Teaser artikel #15 OTA');
check(str_contains($body, 'greenhouse'), 'Teaser capstone #39');
check(str_contains($body, 'Pro tip'), 'Pro tip demo suhu + topic');
check(str_contains($body, 'kodingindonesia/anton'), 'Pro tip topic unik');
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
check(str_contains($html, 'SSD1306'), 'Judul/konten SSD1306 ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'tampilkanOLED'), 'Kode OLED ter-render');
$kernel->terminate($request, $response);

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a13 = Article::where('slug', 'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt')->first();
check($a13 !== null, 'Artikel #13 ada');
check(
    str_contains($a13?->body ?? '', 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'),
    'Artikel #13 backlink → artikel #14'
);

$a12 = Article::where('slug', 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode')->first();
check($a12 !== null, 'Artikel #12 ada');
check(
    str_contains($a12?->body ?? '', 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'),
    'Artikel #12 backlink → artikel #14'
);

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
check($a16 !== null, 'Artikel #16 ada');
check(
    str_contains($a16?->body ?? '', 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'),
    'Artikel #16 backlink → artikel #14'
);

$a11 = Article::where('slug', 'deep-sleep-esp32-sensor-dht22-hemat-baterai')->first();
check($a11 !== null, 'Artikel #11 ada');
check(
    str_contains($a11?->body ?? '', 'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'),
    'Artikel #11 backlink → artikel #14'
);

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
