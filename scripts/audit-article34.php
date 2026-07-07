<?php

/**
 * Audit artikel #34 — NTP & timestamp MQTT.
 * Usage: php scripts/audit-article34.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article34Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article34Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #34 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article34Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article17Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article24Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article11Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['esp32', 'ntp', 'timestamp', 'mqtt', 'wifi', 'iot', 'sensor'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'menghubungkan-esp32-wifi-kirim-data-server'                   => 'Artikel #4 WiFi',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => 'Artikel #11 deep sleep',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                    => 'Artikel #17 TLS',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                     => 'Artikel #24 PIR',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                     => 'Artikel DHT22',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');
check(str_contains($body, 'configTime'), 'ESP32 configTime');
check(str_contains($body, 'getLocalTime'), 'ESP32 getLocalTime');
check(str_contains($body, 'id.pool.ntp.org') || str_contains($body, 'pool.ntp.org'), 'Server NTP');
check(str_contains($body, 'gmtOffset_sec'), 'Offset zona waktu');
check(str_contains($body, 'WIB'), 'Zona WIB');
check(str_contains($body, 'WITA'), 'Zona WITA');
check(str_contains($body, 'WIT'), 'Zona WIT');
check(str_contains($body, 'daylightOffset_sec'), 'Tanpa daylight saving');
check(str_contains($body, 'millis()'), 'Bandingkan dengan millis()');
check(str_contains($body, '"timestamp"'), 'Field JSON timestamp');
check(str_contains($body, '"unix"'), 'Field JSON unix');
check(str_contains($body, '%Y-%m-%dT%H:%M:%S'), 'Format ISO 8601');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor konsisten');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, 'kindo_esp32'), 'User MQTT');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password literal');
check(str_contains($body, 'setBufferSize(512)'), 'MQTT buffer 512');
check(str_contains($body, 'sinkronisasiNTP'), 'Fungsi sinkronisasiNTP');
check(str_contains($body, 'UDP'), 'Port/protokol UDP NTP');
check(str_contains($body, '/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'), 'Link/hyperlink Python #18');
check(str_contains($body, '/artikel/influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'), 'Link/hyperlink InfluxDB #19');
check(str_contains($body, 'language-bash'), 'Blok bash mosquitto_sub');
check(str_contains($body, 'language-arduino'), 'Blok Arduino');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Estimasi biaya'), 'Estimasi biaya');
check(str_contains($body, '2.4 GHz'), 'Troubleshooting WiFi');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop()');
check(str_contains($body, 'mqttClient.state()'), 'Debug rc MQTT');
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
check(str_contains($html, 'configTime'), 'Konten NTP ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'WIB'), 'Tabel zona waktu ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$sources = [
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                    => '#17',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                  => '#11',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
];

$a7 = Article::where('slug', 'memahami-mqtt-esp32-kirim-data-sensor-broker')->first();
check(str_contains($a7?->body ?? '', $slug), 'Artikel #7 backlink → #34');

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Artikel {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #34");
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
