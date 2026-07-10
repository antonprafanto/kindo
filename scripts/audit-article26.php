<?php

/**
 * Audit artikel #26 — LoRa ESP32 + SX1278.
 * Usage: php scripts/audit-article26.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article26Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article26Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #26 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article26Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article25Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article20Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article11Seeder', '--force' => true]);
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

$requiredTags = ['esp32', 'lora', 'sx1278', 'iot', 'sensor', 'spi'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'menghubungkan-esp32-wifi-kirim-data-server'                     => 'Artikel #4 WiFi',
    'membaca-sensor-dht22-suhu-kelembaban-esp32'                     => 'Artikel #5 DHT22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => 'Artikel #7 MQTT',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'               => 'Artikel #25 ESP-NOW',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => 'Artikel #20 REST vs MQTT',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => 'Artikel #11 deep sleep',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => 'Artikel #16 broker',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => 'Artikel #19 Grafana',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'               => 'Artikel #34 NTP',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Jalur D'), 'Menyebut Jalur D');
check(str_contains($body, 'LoRa'), 'Menyebut LoRa');
check(str_contains($body, 'SX1278'), 'Menyebut SX1278');
check(str_contains($body, 'LoRa.h'), 'Library LoRa.h');
check(str_contains($body, 'Kominfo'), 'Catatan regulasi Kominfo');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password MQTT');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor konsisten');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, '1782977400'), 'Contoh unix konsisten #34');
check(str_contains($body, '2026-07-02T14:30:00'), 'ISO timestamp konsisten #34');
check(str_contains($body, 'kindo_esp32'), 'User MQTT publisher');
check(str_contains($body, '433E6'), 'Frekuensi 433 MHz di sketch');
check(str_contains($body, 'lora_packet_t'), 'Struct data bersama');
check(str_contains($body, 'language-cpp'), 'Blok C++');
check(str_contains($body, 'language-bash'), 'Blok bash');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(str_contains($body, 'Estimasi biaya'), 'Estimasi biaya');
check(str_contains($body, '#28'), 'Teaser gateway #28');
check(str_contains($body, '/artikel/esp-now-kirim-data-antar-esp32-tanpa-router-wifi">ESP-NOW (#25)</a>'), 'Tabel perbandingan hyperlink #25');
check(str_contains($body, '/artikel/memahami-mqtt-esp32-kirim-data-sensor-broker">WiFi + MQTT (#7)</a>'), 'Tabel perbandingan hyperlink #7');
check(! str_contains($body, 'shared hosting'), 'Tidak ada typo shared hosting');

$seoLen = mb_strlen($article?->seo_description ?? '');
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$h2Count = substr_count($body, '<h2>');
check($h2Count >= 14, "Minimal 14 section H2 (ada {$h2Count})");
check($article?->read_time_minutes >= 8, 'read_time_minutes ≥ 8 menit');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$response = app()->handle(
    Illuminate\Http\Request::create('/artikel/' . $slug, 'GET')
);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'SX1278'), 'Konten SX1278 ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'LoRa.begin'), 'Sketch LoRa ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 (backlink) ===\n\n";

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'               => '#25',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Artikel {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #26");
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
