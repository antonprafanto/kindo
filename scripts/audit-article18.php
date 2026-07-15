<?php

/**
 * Audit artikel #18 — Python subscriber MQTT → MySQL.
 * Usage: php scripts/audit-article18.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article18Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article18Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #18 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article18Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article34Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article17Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article13Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article14Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article23Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article24Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'networking', 'Kategori networking');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['python', 'mysql', 'mqtt', 'mosquitto', 'iot', 'esp32', 'networking', 'linux'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => 'Artikel #16 broker',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => 'Artikel #34 NTP',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                    => 'Artikel #17 TLS',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => 'Artikel #24 PIR',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => 'Artikel #13 BME280',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'           => 'Artikel #19 Grafana',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => 'Artikel #20 REST vs MQTT',
    'home-assistant-integrasi-esp32-mqtt'                           => 'Artikel #21 HA',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'          => 'Artikel #39 Greenhouse',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Jalur B'), 'Menyebut Jalur B');
check(str_contains($body, 'paho-mqtt'), 'Library paho-mqtt');
check(str_contains($body, 'mysql.connector'), 'mysql-connector-python');
check(str_contains($body, 'sensor_readings'), 'Tabel sensor_readings');
check(str_contains($body, 'kindo_subscriber'), 'User MQTT subscriber');
check(str_contains($body, 'GANTI_PASSWORD_SUBSCRIBER'), 'Placeholder password subscriber');
check(str_contains($body, 'GANTI_PASSWORD_MYSQL'), 'Placeholder password MySQL');
check(! str_contains($body, 'KindoMQTT2026'), 'Tidak ada password literal');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor konsisten');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, '"unix"'), 'Parse field unix');
check(str_contains($body, '1782977400'), 'Contoh unix konsisten #34');
check(str_contains($body, 'parse_waktu'), 'Fungsi parse_waktu');
check(str_contains($body, 'systemd'), 'Layanan systemd');
check(str_contains($body, 'tls_set'), 'Opsi TLS Python');
check(str_contains($body, '/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'), 'Link/hyperlink InfluxDB #19');
check(str_contains($body, '<svg') && str_contains($body, 'Python + paho-mqtt'), 'Diagram arsitektur MQTT→Python→MySQL SVG');
check(! str_contains($body, '[ ESP32 + DHT22 ]'), 'Tidak ada diagram ASCII arsitektur');
check(str_contains($body, '/artikel/smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt">'), 'Hyperlink greenhouse #39');
check(str_contains($body, '/artikel/home-assistant-integrasi-esp32-mqtt">'), 'Hyperlink Home Assistant #21');
check(str_contains($body, 'language-python'), 'Blok Python');
check(str_contains($body, 'language-sql'), 'Blok SQL');
check(str_contains($body, 'language-bash'), 'Blok bash');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Estimasi biaya'), 'Estimasi biaya');
check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 12, "Minimal 12 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 7, 'read_time_minutes ≥ 7 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(
    Illuminate\Http\Request::create('/artikel/' . $slug, 'GET')
);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'paho-mqtt'), 'Konten Python ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'sensor_readings'), 'Skema SQL ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$sources = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => '#7',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'             => '#34',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => '#17',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => '#13',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                 => '#14',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => '#23',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Artikel {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #18");
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
