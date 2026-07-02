<?php

/**
 * Deep audit artikel #17 — cek tambahan di luar audit-article17.php
 * Usage: php scripts/audit-article17-deep.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article17Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn   = 0;

function check(bool $ok, string $label, bool $warning = false): void
{
    global $passed, $failed, $warn;
    if ($warning && ! $ok) {
        echo "⚠ {$label}\n";
        $warn++;
        return;
    }
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article17Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$slug = 'mqtt-tls-qos-lwt-retained-mosquitto-esp32';
$article = Article::where('slug', $slug)->first();

echo "=== DEEP AUDIT #17 — Pass A: Metadata ===\n\n";

check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article->title) <= 90, 'title ≤ 90 char (' . mb_strlen($article->title) . ')');
check(mb_strlen($article->seo_title) <= 70, 'seo_title ≤ 70 char');
check(mb_strlen($article->seo_description) >= 80 && mb_strlen($article->seo_description) <= 160, 'seo_description 80–160 char');
check($article->read_time_minutes >= 7, 'read_time ≥ 7 menit (' . $article->read_time_minutes . ')');

$words = str_word_count(strip_tags($body));
check($words >= 1400, 'word_count ≥ 1400 (' . $words . ')');

echo "\n=== Pass B: HTML & struktur ===\n\n";

check(substr_count($body, '<h2>') >= 12, 'H2 ≥ 12 (' . substr_count($body, '<h2>') . ')');
check(substr_count($body, '<table>') >= 2, 'Minimal 2 tabel');
check(substr_count($body, '<pre>') === substr_count($body, '</pre>'), 'Tag pre seimbang');
check(substr_count($body, '<code') === substr_count($body, '</code>'), 'Tag code seimbang');
check(substr_count($body, '<blockquote>') === substr_count($body, '</blockquote>'), 'Tag blockquote seimbang');
check(! str_contains($body, '<h1>'), 'Tidak ada h1 ganda di body');
check(! preg_match('/href="\/artikel\/"/', $body), 'Tidak ada link /artikel/ kosong');
check(preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) === 1, 'slug format URL-safe');

echo "\n=== Pass C: Validasi link internal ===\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
foreach (array_unique($matches[1]) as $path) {
    $targetSlug = str_replace('/artikel/', '', $path);
    if ($targetSlug === '') {
        continue;
    }
    check(Article::where('slug', $targetSlug)->exists(), "Link valid: {$path}");
}

echo "\n=== Pass D: TLS & sertifikat ===\n\n";

check(str_contains($body, '/CN=192.168.1.50'), 'OpenSSL CN contoh IP (cocok sketch)');
check(str_contains($body, 'CN harus cocok'), 'Peringatan CN harus cocok mqttHost');
check(str_contains($body, 'per_listener_settings true'), 'Dual listener per_listener_settings');
check(str_contains($body, 'listener 1883'), 'Sebut listener 1883 LAN (#16)');
check(str_contains($body, 'listener 8883'), 'Listener TLS 8883');
check(str_contains($body, 'PROGMEM'), 'CA root di PROGMEM');
check(str_contains($body, 'subjectAltName'), 'Opsi SAN SubjectAltName');
check(str_contains($body, 'PASTE_ISI_CA_CRT'), 'Placeholder paste CA');

echo "\n=== Pass E: QoS / LWT / Retained ===\n\n";

check(str_contains($body, 'QoS 2'), 'Penjelasan QoS 2');
check(str_contains($body, '-q 1'), 'CLI contoh QoS 1');
check(str_contains($body, 'willRetain'), 'Kode willRetain LWT');
check(str_contains($body, 'willQoS'), 'Kode willQoS');
check(str_contains($body, '-n -r'), 'Cara hapus retained message');
check(str_contains($body, 'publish(topicStatus, online, true)'), 'Retained status online');

echo "\n=== Pass E2: Konsistensi MQTT Seri (#16/#21) ===\n\n";

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();

check(str_contains($body, '192.168.1.50'), 'IP broker contoh sama seri');
check(str_contains($body, 'kindo_esp32'), 'Username MQTT kindo_esp32');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor JSON konsisten');
check(str_contains($body, '"suhu"'), 'Payload JSON field suhu');
check(str_contains($body, '"kelembaban"'), 'Payload JSON field kelembaban');
check(str_contains($body, 'kodingindonesia/esp32/lampu/kontrol'), 'Topic relay konsisten #8');
check(str_contains($a16?->body ?? '', 'kodingindonesia/esp32/dht22/data'), '#16 pakai topic sensor sama');
check(str_contains($a16?->body ?? '', 'per_listener_settings true'), '#16 sudah per_listener_settings');

echo "\n=== Pass E3: Logika firmware TLS (review statis) ===\n\n";

check(str_contains($body, 'setBufferSize(512)'), 'MQTT buffer 512 untuk TLS');
check(str_contains($body, 'espClient.setCACert(root_ca)'), 'setCACert sebelum connect');
check(! preg_match('/espClient\.setInsecure\s*\(/', $body), 'Tidak ada setInsecure() di sketch');
check(str_contains($body, 'mqttClient.connect('), 'connect() dengan LWT params');
check(substr_count($body, 'mqttClient.loop()') >= 1, 'mqttClient.loop() di loop()');
check(str_contains($body, 'StaticJsonDocument'), 'ArduinoJson untuk publish sensor');
check(str_contains($body, 'R"EOF('), 'Raw string literal untuk CA PEM');
check(str_contains($body, 'ESP.getEfuseMac'), 'Client ID unik dari chip');
check(str_contains($body, 'percobaan &lt; 5'), 'Max retry connect (tidak infinite loop)');

echo "\n=== Pass E4: Keamanan konten ===\n\n";

check(! str_contains($body, 'KindoMQTT2026'), 'Tidak ada password literal KindoMQTT2026');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password');
check(str_contains($body, 'esphome-flash-esp32-tanpa-coding-arduino'), 'Link integrasi ESPHome #22');
check(! str_contains($body, 'test.mosquitto.org'), 'Tidak pakai broker publik di contoh TLS');
check(str_contains($body, 'Jangan commit'), 'Peringatan jangan commit key');

echo "\n=== Pass F: Backlink dua arah ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article17Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article8Seeder', '--force' => true]);

$sources = [
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                  => '#7',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'              => '#10',
    'ota-update-firmware-esp32-via-wifi'                            => '#15',
    'home-assistant-integrasi-esp32-mqtt'                           => '#21',
    'esphome-flash-esp32-tanpa-coding-arduino'                      => '#22',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                     => '#23',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                    => '#24',
    'kontrol-lampu-esp32-mqtt-relay'                                => '#8',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null && str_contains($src->body ?? '', $slug), "{$label} → #17 backlink");
}

echo "\n=== Pass G: Infrastruktur deploy ===\n\n";

check(file_exists(__DIR__ . '/../database/seeders/Article17Seeder.php'), 'Article17Seeder.php ada');
check(str_contains(file_get_contents(__DIR__ . '/../routes/web.php'), 'publish-article-17'), 'Route deploy ada');
check(str_contains(file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php'), 'publishArticle17'), 'DeployController method ada');
check(str_contains(file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml'), 'publish-article-17'), 'CI hook deploy.yml ada');

echo "\n=== Pass H: Indeks #10 ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first()?->body ?? '';
check(str_contains($a10body, $slug), '#10 indeks mencantumkan #17');
check(str_contains($a10body, 'sebelas artikel pertama'), '#10 teks sebelas artikel');

echo "\n=== Pass I: HTTP render ===\n\n";

$response = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = (string) $response->getContent();

check($response->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'WiFiClientSecure'), 'TLS code ter-render');
check(str_contains($html, 'tls_set()'), 'Python tls_set teaser ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD');
check(str_contains($html, 'og:description'), 'og:description');
check(str_contains($html, 'canonical'), 'canonical URL');
check(str_contains($html, 'setCACert'), 'setCACert ter-render di HTML');
check(str_contains($html, 'per_listener_settings'), 'Config Mosquitto ter-render');
check(str_contains($html, 'willRetain'), 'LWT willRetain ter-render');

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
