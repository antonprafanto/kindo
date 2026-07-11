<?php



/**

 * Audit artikel #37 — SD Card & SPI logging offline ESP32.

 * Usage: php scripts/audit-article37.php [--production]

 */



$checkProduction = in_array('--production', $argv, true);



require __DIR__ . '/../vendor/autoload.php';



$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();



use App\Models\Article;

use Database\Seeders\Article37Seeder;

use Illuminate\Support\Facades\Artisan;



$passed = 0;

$failed = 0;

$slug   = 'sd-card-spi-esp32-logging-data-sensor-offline';



function check(bool $ok, string $label): void

{

    global $passed, $failed;

    echo ($ok ? '✓' : '✗') . " {$label}\n";

    $ok ? $passed++ : $failed++;

}



function seederBody(): string

{

    $ref = new ReflectionClass(Article37Seeder::class);

    $method = $ref->getMethod('body');

    $method->setAccessible(true);



    return $method->invoke($ref->newInstanceWithoutConstructor());

}



echo "=== Audit Artikel #37 — Pass 1: Seeder & DB ===\n\n";



Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article37Seeder', '--force' => true]);

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle36SdCardSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle27SdCardSeeder', '--force' => true]);

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);



$article = Article::where('slug', $slug)->first();

$body = seederBody();



check($article !== null, 'Artikel ada di database setelah seed');

check($article?->status === 'published', 'Status published');

check($article?->published_at !== null, 'published_at terisi');

check($article?->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');

check($article?->is_featured === false, 'is_featured false');



$requiredTags = ['esp32', 'spi', 'sd-card', 'sensor', 'iot', 'mqtt'];

$articleTags = $article?->tags->pluck('slug')->all() ?? [];

foreach ($requiredTags as $tag) {

    check(in_array($tag, $articleTags, true), "Tag: {$tag}");

}



$requiredLinks = [

    'blink-led-esp32-tutorial-pertama-embedded-system'                => 'Artikel #3 Blink',

    'menghubungkan-esp32-wifi-kirim-data-server'                      => 'Artikel #4 WiFi',

    'membaca-sensor-dht22-suhu-kelembaban-esp32'                      => 'Artikel #5 DHT22',

    'memahami-mqtt-esp32-kirim-data-sensor-broker'                    => 'Artikel #7 MQTT',

    'dashboard-esp32-web-server-mqtt-monitoring-dht22'                => 'Artikel #10 Capstone',

    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                     => 'Artikel #11 Deep sleep',

    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'    => 'Artikel #12 NVS',

    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                       => 'Artikel #13 I2C',

    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'     => 'Artikel #16 Mosquitto',

    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'             => 'Artikel #18 Python',

    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'            => 'Artikel #19 Grafana',

    'freertos-esp32-multi-task-sensor-wifi-mqtt'                        => 'Artikel #31 FreeRTOS',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'                => 'Artikel #25 ESP-NOW',
    'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard'             => 'Artikel #28 Gateway',
    'home-assistant-integrasi-esp32-mqtt'                             => 'Artikel #21 Home Assistant',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                       => 'Artikel #23 Node-RED',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'                    => 'Artikel #26 LoRa',

    'migrasi-platformio-esp32-vscode-project-rapi'                    => 'Artikel #29 PlatformIO',

    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'                => 'Artikel #34 NTP',

    'adc-esp32-sensor-analog-soil-moisture-ldr-mqtt'                  => 'Artikel #35 ADC',

    'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade'                    => 'Artikel #36 ESP8266',

    'ota-update-firmware-esp32-via-wifi'                                => 'Artikel #15 OTA',

];



foreach ($requiredLinks as $linkSlug => $label) {

    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");

    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");

}



check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');

check(str_contains($body, 'SPI.h'), 'Include SPI.h');

check(str_contains($body, 'SD.h'), 'Include SD.h');

check(str_contains($body, 'GPIO 5') || str_contains($body, 'SD_CS     5'), 'GPIO 5 CS');

check(str_contains($body, 'GPIO 18') || str_contains($body, 'SD_SCK   18'), 'GPIO 18 SCK');

check(str_contains($body, 'GPIO 23') || str_contains($body, 'SD_MOSI  23'), 'GPIO 23 MOSI');

check(str_contains($body, 'GPIO 19') || str_contains($body, 'SD_MISO  19'), 'GPIO 19 MISO');

check(str_contains($body, '/sensor.csv') || str_contains($body, 'LOG_FILE'), 'File sensor.csv');

check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic MQTT sync konsisten');

check(str_contains($body, 'SPI vs I2C') || str_contains($body, 'SPI vs I2C'), 'Section SPI vs I2C');

check(str_contains($body, 'bukan I2C') || str_contains($body, 'bukan I2C'), 'Tekankan bukan I2C');

check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, 'kindo_esp32'), 'User MQTT kindo_esp32');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder WiFi SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password MQTT');
check(str_contains($body, '1782977400'), 'Contoh unix konsisten #34');

check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');

check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');

check(str_contains($body, 'language-cpp'), 'Blok C++');

check(str_contains($body, 'language-ini'), 'Blok platformio.ini');

check(str_contains($body, 'Pro tip'), 'Pro tip');

check(str_contains($body, 'Keamanan'), 'Section keamanan');

check(str_contains($body, 'Estimasi Biaya'), 'Estimasi biaya');

check(str_contains($body, '#38') || str_contains($body, 'HTTPS'), 'Teaser HTTPS #38');

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

check(str_contains($html, 'SD Card') && str_contains($html, 'SPI'), 'Konten SD/SPI ter-render');

check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');

check(str_contains($html, 'sensor.csv'), 'File log ter-render');



echo "\n=== Pass 3: Konsistensi Seri 2 (backlink) ===\n\n";



$sources = [

    'dashboard-esp32-web-server-mqtt-monitoring-dht22' => '#10',

    'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade'     => '#36',

];



foreach ($sources as $sourceSlug => $label) {

    $src = Article::where('slug', $sourceSlug)->first();

    check($src !== null, "Artikel {$label} ada");

    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #37");

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

