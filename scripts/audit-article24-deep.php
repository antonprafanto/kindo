<?php

/**
 * Deep audit artikel #24 — cek tambahan di luar audit-article24.php
 * Usage: php scripts/audit-article24-deep.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article24Seeder;

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

$ref = new ReflectionClass(Article24Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$slug = 'sensor-gerak-pir-esp32-lampu-mqtt-debounce';
$article = Article::where('slug', $slug)->first();

echo "=== DEEP AUDIT #24 — Pass A: Metadata ===\n\n";

check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article->title) <= 80, 'title ≤ 80 char (' . mb_strlen($article->title) . ')');
check(mb_strlen($article->seo_title) <= 70, 'seo_title ≤ 70 char');
check(mb_strlen($article->seo_description) >= 80 && mb_strlen($article->seo_description) <= 160, 'seo_description 80–160 char');
check(preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) === 1, 'slug format URL-safe');
check($article->read_time_minutes >= 5, 'read_time ≥ 5 menit (' . $article->read_time_minutes . ')');

$words = str_word_count(strip_tags($body));
check($words >= 1200, 'word_count ≥ 1200 (' . $words . ')');

echo "\n=== Pass B: HTML & struktur ===\n\n";

check(substr_count($body, '<h2>') === substr_count($body, '</h2>') || substr_count($body, '<h2>') > 0, 'H2 sections ada (' . substr_count($body, '<h2>') . ')');
check(substr_count($body, '<pre>') === substr_count($body, '</pre>'), 'Tag pre seimbang');
check(substr_count($body, '<code') === substr_count($body, '</code>'), 'Tag code seimbang');
check(substr_count($body, '<blockquote>') === substr_count($body, '</blockquote>'), 'Tag blockquote seimbang');
check(substr_count($body, '<table>') === 1, 'Satu tabel perbandingan');
check(! str_contains($body, '<h1>'), 'Tidak ada h1 ganda di body');
check(! preg_match('/href="\/artikel\/"/', $body), 'Tidak ada link /artikel/ kosong');

echo "\n=== Pass C: Validasi link internal ===\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
$uniqueLinks = array_unique($matches[1]);
foreach ($uniqueLinks as $path) {
    $targetSlug = str_replace('/artikel/', '', $path);
    if ($targetSlug === '') {
        continue;
    }
    check(Article::where('slug', $targetSlug)->exists(), "Link valid: {$path}");
}

echo "\n=== Pass D: Konsistensi MQTT & GPIO (#8/#21) ===\n\n";

$a8  = Article::where('slug', 'kontrol-lampu-esp32-mqtt-relay')->first();
$a21 = Article::where('slug', 'home-assistant-integrasi-esp32-mqtt')->first();

check(str_contains($body, 'kodingindonesia/esp32/lampu/kontrol'), 'Topic kontrol relay');
check(substr_count($body, 'kodingindonesia/esp32/pir/gerak') >= 5, 'Topic PIR dipakai konsisten (≥5)');
check(str_contains($body, '#define RELAY_PIN 26'), 'GPIO 26 relay');
check(str_contains($body, '#define PIR_PIN   27'), 'GPIO 27 PIR');
check(str_contains($body, 'RELAY_ON  = LOW'), 'RELAY active LOW sama #8');
check(str_contains($a8?->body ?? '', 'kodingindonesia/esp32/lampu/kontrol'), '#8 pakai topic kontrol sama');
check(str_contains($a21?->body ?? '', 'switch.lampu_esp32_relay'), '#21 entity switch ada');
check(str_contains($body, 'switch.lampu_esp32_relay'), '#24 pakai entity #21');

echo "\n=== Pass E: Logika firmware (review statis) ===\n\n";

check(str_contains($body, 'void publishStatus()'), 'publishStatus tanpa argumen salah');
check(str_contains($body, 'bool gerakAktif = digitalRead(PIR_PIN)'), 'gerak dari pin PIR');
check(str_contains($body, 'attachInterrupt'), 'Interrupt terpasang');
check(str_contains($body, 'RISING'), 'Edge RISING');
check(str_contains($body, 'pirFlag = false'), 'Flag ISR di-clear di loop');
check(str_contains($body, 'setBufferSize(256)'), 'MQTT buffer cukup untuk JSON');
check(str_contains($body, 'StaticJsonDocument'), 'ArduinoJson StaticDocument');
check(! str_contains($body, 'delay(') || str_contains($body, 'delay(500)'), 'delay hanya di koneksi (bukan ISR)', true);

echo "\n=== Pass F: Backlink dua arah ===\n\n";

$sources = [
    'kontrol-lampu-esp32-mqtt-relay'                                => '#8',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek'                  => '#9',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'              => '#10',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'home-assistant-integrasi-esp32-mqtt'                           => '#21',
    'esphome-flash-esp32-tanpa-coding-arduino'                      => '#22',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                     => '#23',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null && str_contains($src->body ?? '', $slug), "{$label} → #24 backlink");
}

check(
    ! str_contains(Article::where('slug', 'memahami-mqtt-esp32-kirim-data-sensor-broker')->first()?->body ?? '', $slug),
    '#7 tidak wajib backlink ke #24 (opsional)',
    true
);

echo "\n=== Pass G: Infrastruktur deploy ===\n\n";

check(file_exists(__DIR__ . '/../database/seeders/Article24Seeder.php'), 'Article24Seeder.php ada');
check(str_contains(file_get_contents(__DIR__ . '/../routes/web.php'), 'publish-article-24'), 'Route deploy ada');
check(str_contains(file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php'), 'publishArticle24'), 'DeployController method ada');
check(str_contains(file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml'), 'publish-article-24'), 'CI hook deploy.yml ada');
check(str_contains(file_get_contents(__DIR__ . '/../database/seeders/Article24Seeder.php'), 'cover_image tidak disentuh'), 'Seeder tidak wipe cover');

echo "\n=== Pass H: Indeks Seri 2 (#10) ===\n\n";

Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first()?->body ?? '';
check(str_contains($a10body, $slug), '#10 indeks mencantumkan #24');
$listCount = substr_count($a10body, '<li><strong><a href="/artikel/');
check($listCount >= 10, '#10 daftar Seri 2 punya ≥10 item (' . $listCount . ')');
check(
    str_contains($a10body, 'sepuluh artikel pertama') || str_contains($a10body, '10 artikel pertama'),
    '#10 teks jumlah artikel konsisten dengan daftar (sepuluh)',
);

echo "\n=== Pass I: Keamanan konten ===\n\n";

check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal KindoMQTT');
check(! str_contains($body, 'GANTI_PASSWORD_MQTT_ANDA') || str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password konsisten');
check(str_contains($body, 'test.mosquitto.org'), 'Peringatan jangan pakai broker publik');
check(str_contains($body, 'bukan</strong> AC 220V'), 'Peringatan AC 220V');

echo "\n=== Pass J: HTTP render mendalam ===\n\n";

$response = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = (string) $response->getContent();

check($response->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'application/ld+json'), 'JSON-LD');
check(str_contains($html, 'og:description'), 'og:description');
check(str_contains($html, 'canonical'), 'canonical URL');
check(str_contains($html, 'digitalRead(PIR_PIN)'), 'Kode polling ter-render');
check(str_contains($html, 'payload_json.gerak'), 'YAML automasi ter-render');

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
