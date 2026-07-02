<?php

/**
 * Audit artikel #17 — MQTT TLS, QoS, LWT & retained.
 * Usage: php scripts/audit-article17.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article17Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'mqtt-tls-qos-lwt-retained-mosquitto-esp32';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article17Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #17 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article17Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article24Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article23Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article22Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article21Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article15Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article12Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article8Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article7Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'networking', 'Kategori networking');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['mqtt', 'mosquitto', 'tls', 'esp32', 'iot', 'networking', 'linux'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode' => 'Artikel #12 NVS',
    'kontrol-lampu-esp32-mqtt-relay'                                 => 'Artikel #8 relay',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                     => 'Artikel #24 PIR',
    'home-assistant-integrasi-esp32-mqtt'                          => 'Artikel #21 HA',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                      => 'Artikel #23 Node-RED',
    'esphome-flash-esp32-tanpa-coding-arduino'                       => 'Artikel #22 ESPHome',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Jalur B'), 'Menyebut Jalur B');
check(str_contains($body, '8883'), 'Port TLS 8883');
check(str_contains($body, 'WiFiClientSecure'), 'ESP32 WiFiClientSecure');
check(str_contains($body, 'setCACert'), 'ESP32 setCACert');
check(! str_contains($body, 'espClient.setInsecure'), 'Tidak ada espClient.setInsecure di kode');
check(str_contains($body, 'setInsecure'), 'Peringatan jangan setInsecure produksi');
check(str_contains($body, 'openssl'), 'Perintah OpenSSL');
check(str_contains($body, 'ca.crt'), 'File CA certificate');
check(str_contains($body, 'listener 8883'), 'Mosquitto listener 8883');
check(str_contains($body, 'per_listener_settings'), 'Config per_listener_settings');
check(str_contains($body, 'require_certificate false'), 'require_certificate false');
check(str_contains($body, '--cafile'), 'mosquitto CLI --cafile');
check(str_contains($body, 'QoS 0'), 'Penjelasan QoS 0');
check(str_contains($body, 'QoS 1'), 'Penjelasan QoS 1');
check(str_contains($body, 'QoS 2'), 'Penjelasan QoS 2');
check(str_contains($body, '/CN=192.168.1.50'), 'OpenSSL CN contoh IP');
check(str_contains($body, 'CN harus cocok'), 'Checklist CN sertifikat');
check(str_contains($body, 'listener 1883'), 'Sebut dual listener 1883');
check(str_contains($body, 'subjectAltName'), 'Opsi SAN SubjectAltName');
check(str_contains($body, 'ESP.getEfuseMac'), 'Client ID unik dari chip');
check(str_contains($body, 'percobaan &lt; 5'), 'Max retry connect MQTT');
check(str_contains($body, 'esphome-flash-esp32-tanpa-coding-arduino'), 'Link integrasi ESPHome #22');
check(str_contains($body, 'conf.d'), 'Troubleshooting conf.d Mosquitto');
check(str_contains($body, 'LWT'), 'Last Will Testament');
check(str_contains($body, 'willTopic'), 'Kode LWT willTopic');
check(str_contains($body, 'lwtPayload'), 'Payload LWT offline');
check(str_contains($body, '{"online":false}'), 'JSON LWT offline');
check(str_contains($body, 'retained'), 'Retained messages');
check(str_contains($body, 'publish(topicStatus, online, true)'), 'Publish retained status');
check(str_contains($body, 'kodingindonesia/esp32/status'), 'Topic status LWT');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor konsisten');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password literal');
check(str_contains($body, 'language-bash'), 'Blok bash');
check(str_contains($body, 'language-arduino'), 'Blok Arduino');
check(str_contains($body, 'language-ini'), 'Blok config Mosquitto ini');
check(str_contains($body, 'Let\'s Encrypt'), 'Opsi Let\'s Encrypt');
check(str_contains($body, 'Artikel #18'), 'Teaser Python #18');
check(str_contains($body, 'Artikel #34'), 'Teaser NTP #34');
check(str_contains($body, 'Artikel #19'), 'Teaser InfluxDB #19');
check(str_contains($body, '#38'), 'Teaser HTTPS #38');
check(str_contains($body, 'Keamanan &amp; Produksi'), 'Section Keamanan');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Estimasi biaya'), 'Estimasi biaya');
check(str_contains($body, '2.4 GHz'), 'Troubleshooting WiFi');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop()');
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
check(str_contains($html, 'WiFiClientSecure'), 'Konten TLS ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'listener 8883'), 'Config Mosquitto ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
check($a16 !== null, 'Artikel #16 ada');
check(str_contains($a16?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #16 backlink → #17');

$a24 = Article::where('slug', 'sensor-gerak-pir-esp32-lampu-mqtt-debounce')->first();
check($a24 !== null, 'Artikel #24 ada');
check(str_contains($a24?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #24 backlink → #17');

$a7 = Article::where('slug', 'memahami-mqtt-esp32-kirim-data-sensor-broker')->first();
check($a7 !== null, 'Artikel #7 ada');
check(str_contains($a7?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #7 backlink → #17');

$a21 = Article::where('slug', 'home-assistant-integrasi-esp32-mqtt')->first();
check($a21 !== null, 'Artikel #21 ada');
check(str_contains($a21?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #21 backlink → #17');

$a23 = Article::where('slug', 'node-red-dashboard-otomasi-iot-mqtt-esp32')->first();
check($a23 !== null, 'Artikel #23 ada');
check(str_contains($a23?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #23 backlink → #17');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check($a10 !== null, 'Artikel #10 ada');
check(str_contains($a10?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #10 backlink → #17');

$a22 = Article::where('slug', 'esphome-flash-esp32-tanpa-coding-arduino')->first();
check($a22 !== null, 'Artikel #22 ada');
check(str_contains($a22?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #22 backlink → #17');

$a15 = Article::where('slug', 'ota-update-firmware-esp32-via-wifi')->first();
check($a15 !== null, 'Artikel #15 ada');
check(str_contains($a15?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #15 backlink → #17');

$a8 = Article::where('slug', 'kontrol-lampu-esp32-mqtt-relay')->first();
check($a8 !== null, 'Artikel #8 ada');
check(str_contains($a8?->body ?? '', 'mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Artikel #8 backlink → #17');

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
