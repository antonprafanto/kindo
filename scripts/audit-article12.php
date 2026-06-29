<?php

/**
 * Audit artikel #12 — NVS Preferences + WiFiManager ESP32.
 * Usage: php scripts/audit-article12.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article12Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article12Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #12 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article12Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article11Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');
check($article?->is_featured === false, 'is_featured false');
check($article?->cover_image === null || $article->cover_image !== '', 'cover_image tidak di-wipe seeder');

$requiredTags = ['esp32', 'wifi', 'iot', 'mqtt', 'sensor', 'wifimanager', 'nvs'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'menghubungkan-esp32-wifi-kirim-data-server'              => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'              => 'Artikel #5 DHT22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'              => 'Artikel #7 MQTT',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'             => 'Artikel #11 deep sleep',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'WiFiManager'), 'Menyebut WiFiManager');
check(str_contains($body, 'Preferences'), 'Menyebut Preferences / NVS');
check(str_contains($body, 'autoConnect'), 'Kode: wm.autoConnect');
check(str_contains($body, 'prefs.putString'), 'Kode: prefs.putString');
check(str_contains($body, 'resetSettings'), 'Kode: resetSettings');
check(str_contains($body, 'KindoESP32-Setup'), 'Nama AP portal');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'MQTT topic sensor konsisten');
check(str_contains($body, 'test.mosquitto.org'), 'Broker latihan test.mosquitto.org');
check(str_contains($body, 'Pro tip'), 'Pro tip topic unik');
check(str_contains($body, 'Broker bukan website'), 'Peringatan broker bukan website');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop() sebelum publish');
check(str_contains($body, 'setBufferSize(512)'), 'PubSubClient setBufferSize(512)');
check(str_contains($body, 'dht.begin()'), 'DHT begin');
check(
    preg_match('/dht\.begin\(\)[\s\S]{0,120}delay\(2000\)/', $body),
    'delay(2000) setelah dht.begin() di kode'
);
check(str_contains($body, '#define DHT_PIN  4'), 'DHT GPIO 4');
check(str_contains($body, 'Install Library'), 'Section install library');
check(str_contains($body, 'tzapu'), 'Library WiFiManager (tzapu)');
check(! preg_match('/const char\*\s+ssid\s*=\s*"[^"]+"/', $body), 'Tidak hardcode ssid di sketch utama');
check(! preg_match('/const char\*\s+password\s*=\s*"[^"]+"/', $body), 'Tidak hardcode password di sketch utama');
check(str_contains($body, 'Seri 2'), 'Menyebut Seri 2');
check(str_contains($body, 'BME280'), 'Teaser artikel #13 BME280');
check(str_contains($body, 'OTA'), 'Teaser artikel #15 OTA');
check(str_contains($body, 'language-arduino'), 'Blok kode Arduino');
check(str_contains($body, 'language-bash'), 'Blok mosquitto_sub bash');
check(str_contains($body, '<table>'), 'Ada tabel');
check(str_contains($body, 'rel="noopener"') || ! str_contains($body, 'target="_blank"'), 'Link eksternal aman');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = preg_match_all('/<h2>/', $body);
check($h2Count >= 9, "Minimal 9 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 5, 'read_time_minutes ≥ 5 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/artikel/' . $slug, 'GET');
$response = $kernel->handle($request);
$html = (string) $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'WiFiManager'), 'Judul/konten WiFiManager ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'autoConnect'), 'Kode autoConnect ter-render');
$kernel->terminate($request, $response);

echo "\n=== Pass 3: Konsistensi Seri 2 ===\n\n";

$a11 = Article::where('slug', 'deep-sleep-esp32-sensor-dht22-hemat-baterai')->first();
check($a11 !== null, 'Artikel #11 ada');
check(
    str_contains($a11?->body ?? '', 'nvs-preferences-wifimanager-esp32-konfigurasi-tanpa-hardcode'),
    'Artikel #11 backlink → artikel #12'
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
