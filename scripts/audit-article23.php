<?php

/**
 * Audit artikel #23 — Node-RED dashboard & otomasi MQTT.
 * Usage: php scripts/audit-article23.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article23Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'node-red-dashboard-otomasi-iot-mqtt-esp32';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article23Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #23 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article23Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article22Seeder', '--force' => true]);
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

$requiredTags = ['esp32', 'nodered', 'mqtt', 'iot', 'smarthome', 'homeassistant', 'relay'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32'  => 'Artikel #16 broker',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                     => 'Artikel #5 DHT22',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek'                   => 'Artikel #9 gabungan',
    'home-assistant-integrasi-esp32-mqtt'                          => 'Artikel #21 Home Assistant',
    'esphome-flash-esp32-tanpa-coding-arduino'                       => 'Artikel #22 ESPHome',
    'kontrol-lampu-esp32-mqtt-relay'                                 => 'Artikel #8 relay',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Node-RED'), 'Menyebut Node-RED');
check(str_contains($body, 'Jalur C'), 'Menyebut Jalur C');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor DHT22 konsisten');
check(str_contains($body, 'kodingindonesia/esp32/lampu/kontrol'), 'Topic relay konsisten');
check(str_contains($body, 'Alur data secara singkat'), 'Diagram alur vertikal');
check(str_contains($body, 'Home Assistant vs ESPHome vs Node-RED'), 'Tabel perbandingan stack');
check(str_contains($body, 'node-red-dashboard'), 'Install node-red-dashboard');
check(str_contains($body, 'language-javascript'), 'Blok kode JavaScript function');
check(str_contains($body, 'language-json'), 'Blok import flow JSON');
check(str_contains($body, 'language-yaml'), 'Blok docker-compose YAML');
check(str_contains($body, 'language-bash'), 'Blok bash docker');
check(str_contains($body, 'ui_gauge'), 'Dashboard ui_gauge');
check(str_contains($body, 'ui_tab'), 'Langkah buat ui_tab');
check(str_contains($body, 'ui_group'), 'Langkah buat ui_group');
check(str_contains($body, 'Kontrol Lampu'), 'ui_group Kontrol Lampu untuk relay');
check(str_contains($body, 'ui_button'), 'Dashboard ui_button relay');
check(str_contains($body, 'ui_button</strong> → input <strong>change'), 'Wiring ui_button → change → mqtt out');
check(str_contains($body, 'msg.payload.kelembaban'), 'Function ekstrak kelembaban');
check(str_contains($body, 'mqtt out'), 'Node mqtt out relay');
check(str_contains($body, 'msg.topic'), 'mqtt out pakai msg.topic');
check(str_contains($body, 'kosongkan'), 'Petunjuk kosongkan Topic mqtt out automasi');
check(str_contains($body, 'rate limit'), 'Catatan debounce/rate limit automasi suhu');
check(str_contains($body, 'mosquitto_sub'), 'Blok verifikasi mosquitto_sub');
check(str_contains($body, 'mosquitto_pub'), 'Blok uji relay mosquitto_pub');
check(str_contains($body, 'GANTI_PASSWORD_MQTT_ANDA'), 'JSON import pakai placeholder password');
check(! str_contains($body, 'KindoMQTT2026!'), 'Tidak ada password literal di artikel');
check(str_contains($body, 'IP LAN host'), 'Catatan Docker network host IP');
check(str_contains($body, 'nama service'), 'Catatan Docker Compose hostname broker');
check(str_contains($body, 'test.mosquitto.org'), 'Peringatan broker publik');
check(str_contains($body, 'adminAuth'), 'Keamanan adminAuth Node-RED');
check(str_contains($body, '/artikel/sensor-gerak-pir-esp32-lampu-mqtt-debounce'), 'Link/hyperlink PIR #24');
check(str_contains($body, '/artikel/mqtt-tls-qos-lwt-retained-mosquitto-esp32'), 'Link/hyperlink MQTT TLS #17');
check(str_contains($body, '/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'), 'Link/hyperlink Python #18');
check(str_contains($body, 'Keamanan &amp; Produksi'), 'Section Keamanan & Produksi');
check(str_contains($body, 'Pro tip'), 'Pro tip label node');
check(str_contains($body, 'greenhouse'), 'Teaser capstone #39');
check(str_contains($body, 'Estimasi biaya'), 'Estimasi biaya');
check(str_contains($body, '2.4 GHz'), 'Troubleshooting WiFi 2.4 GHz');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 12, "Minimal 12 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 5, 'read_time_minutes ≥ 5 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(
    Illuminate\Http\Request::create('/artikel/' . $slug, 'GET')
);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'Node-RED'), 'Konten Node-RED ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'mqtt_broker_kindo'), 'JSON flow ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a22 = Article::where('slug', 'esphome-flash-esp32-tanpa-coding-arduino')->first();
check($a22 !== null, 'Artikel #22 ada');
check(str_contains($a22?->body ?? '', 'node-red-dashboard-otomasi-iot-mqtt-esp32'), 'Artikel #22 backlink → #23');

$a21 = Article::where('slug', 'home-assistant-integrasi-esp32-mqtt')->first();
check($a21 !== null, 'Artikel #21 ada');
check(str_contains($a21?->body ?? '', 'node-red-dashboard-otomasi-iot-mqtt-esp32'), 'Artikel #21 backlink → #23');

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
check($a10 !== null, 'Artikel #10 ada');
check(str_contains($a10?->body ?? '', 'node-red-dashboard-otomasi-iot-mqtt-esp32'), 'Artikel #10 indeks → #23');

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
check($a16 !== null, 'Artikel #16 ada');
check(str_contains($a16?->body ?? '', 'node-red-dashboard-otomasi-iot-mqtt-esp32'), 'Artikel #16 backlink → #23');

$a9 = Article::where('slug', 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek')->first();
check($a9 !== null, 'Artikel #9 ada');
check(str_contains($a9?->body ?? '', 'node-red-dashboard-otomasi-iot-mqtt-esp32'), 'Artikel #9 backlink → #23');

$a8 = Article::where('slug', 'kontrol-lampu-esp32-mqtt-relay')->first();
check($a8 !== null, 'Artikel #8 ada');
check(str_contains($a8?->body ?? '', 'node-red-dashboard-otomasi-iot-mqtt-esp32'), 'Artikel #8 backlink → #23');

check(str_contains($a10?->body ?? '', 'tujuh belas artikel'), 'Artikel #10 teks tujuh belas artikel');

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
