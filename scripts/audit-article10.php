<?php

/**
 * Audit berulang artikel #10 — konten, link, SEO, konsistensi seri ESP32.
 * Usage: php scripts/audit-article10.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article10Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article10Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);
    $seeder = $ref->newInstanceWithoutConstructor();

    return $method->invoke($seeder);
}

echo "=== Audit Artikel #10 — Pass 1: Seeder source ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);

$article = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device');

$expectedSlugs = [
    'mengenal-esp32-mikrokontroler-wifi-bluetooth-iot',
    'cara-install-arduino-ide-setup-esp32-board-manager',
    'blink-led-esp32-tutorial-pertama-embedded-system',
    'menghubungkan-esp32-wifi-kirim-data-server',
    'membaca-sensor-dht22-suhu-kelembaban-esp32',
    'membuat-web-server-esp32-monitoring-sensor-dht22',
    'memahami-mqtt-esp32-kirim-data-sensor-broker',
    'kontrol-lampu-esp32-mqtt-relay',
    'gabungkan-dht22-relay-mqtt-esp32-satu-proyek',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22',
];

preg_match_all('#href="/artikel/([a-z0-9\-]+)"#', $body, $linkMatches);
$internalSlugs = array_unique($linkMatches[1] ?? []);

foreach ($internalSlugs as $slug) {
    $exists = Article::where('slug', $slug)->exists();
    check($exists, "Link internal valid: /artikel/{$slug}");
}

check(
    in_array('membuat-web-server-esp32-monitoring-sensor-dht22', $internalSlugs, true),
    'Link ke artikel #6 (web server)'
);
check(
    in_array('memahami-mqtt-esp32-kirim-data-sensor-broker', $internalSlugs, true),
    'Link ke artikel #7 (MQTT)'
);
check(
    in_array('gabungkan-dht22-relay-mqtt-esp32-satu-proyek', $internalSlugs, true),
    'Link ke artikel #9 (gabungan)'
);
check(
    in_array('menghubungkan-esp32-wifi-kirim-data-server', $internalSlugs, true),
    'Link ke artikel #4 (WiFi)'
);
check(
    in_array('membaca-sensor-dht22-suhu-kelembaban-esp32', $internalSlugs, true),
    'Link ke artikel #5 (DHT22)'
);

$missingSeriLinks = array_diff([
    'kontrol-lampu-esp32-mqtt-relay',
], $internalSlugs);
check(count($missingSeriLinks) === 0, 'Link ke artikel #8 (relay) di indeks seri');

foreach ($expectedSlugs as $slug) {
    if ($slug === 'dashboard-esp32-web-server-mqtt-monitoring-dht22') {
        check(
            str_contains($body, 'artikel ini (capstone)') || str_contains($body, $slug),
            'Indeks seri menyebut artikel #10 (capstone)'
        );
        continue;
    }
    check(str_contains($body, $slug), "Indeks seri menyebut slug: {$slug}");
}

check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic MQTT konsisten (/data)');
check(str_contains($body, 'test.mosquitto.org'), 'Broker test.mosquitto.org');
check(str_contains($body, '#define DHT_PIN  4') || str_contains($body, '#define DHT_PIN 4'), 'GPIO 4 konsisten');
check(str_contains($body, 'dht.begin()'), 'Kode memanggil dht.begin()');
check(str_contains($body, 'delay(2000)') && preg_match('/dht\.begin\(\)[\s\S]{0,80}delay\(2000\)/', $body), 'delay(2000) setelah dht.begin() di kode');
check(str_contains($body, 'mqttClient.loop()'), 'mqttClient.loop() di dokumentasi/alur');
check(str_contains($body, 'server.handleClient()'), 'server.handleClient() di dokumentasi');
check(str_contains($body, 'setBufferSize'), 'mqttClient.setBufferSize() diset');
check(str_contains($body, 'language-arduino'), 'Blok kode Arduino dengan highlight class');
check(str_contains($body, 'language-bash'), 'Blok kode bash (mosquitto_sub)');
check(str_contains($body, '<table>'), 'Ada tabel arsitektur');
check(str_contains($body, 'rel="noopener"'), 'Link eksternal pakai rel=noopener');
check(! str_contains($body, 'shared hosting'), 'Tidak ada istilah salah "shared hosting" di konteks ESP32');

$seoDesc = $article?->seo_description ?? '';
$seoLen = mb_strlen($seoDesc);
check($seoLen >= 80 && $seoLen <= 160, "seo_description panjang OK ({$seoLen} char)");
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

$tagSlugs = $article?->tags->pluck('slug')->sort()->values()->all() ?? [];
check(in_array('esp32', $tagSlugs, true), 'Tag esp32');
check(in_array('mqtt', $tagSlugs, true), 'Tag mqtt');
check(in_array('dht22', $tagSlugs, true), 'Tag dht22');
check(in_array('sensor', $tagSlugs, true), 'Tag sensor');

$h2Count = preg_match_all('/<h2>/', $body);
check($h2Count >= 8, "Minimal 8 section H2 (ada {$h2Count})");

check($article?->read_time_minutes >= 5, 'read_time_minutes masuk akal (≥5 menit)');

$seri2IndexCount = substr_count($body, '<li><strong><a href="/artikel/');
check($seri2IndexCount === 23, 'Indeks Seri 2 punya 23 artikel live (' . $seri2IndexCount . ')');
check(str_contains($body, 'dua puluh tiga artikel'), 'Teks indeks: dua puluh tiga artikel');
check(str_contains($body, 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'), 'Indeks Seri 2 link artikel #25');
check(str_contains($body, 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'), 'Indeks Seri 2 link artikel #26');
check(str_contains($body, 'esp32-cam-streaming-mjpeg-capture-foto-wifi'), 'Indeks Seri 2 link artikel #27');
check(str_contains($body, 'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard'), 'Indeks Seri 2 link artikel #28');
check(str_contains($body, 'migrasi-platformio-esp32-vscode-project-rapi'), 'Indeks Seri 2 link artikel #29');
check(str_contains($body, 'esp32-firebase-realtime-database-sensor-cloud'), 'Indeks Seri 2 link artikel #30');
check(str_contains($body, 'freertos-esp32-multi-task-sensor-wifi-mqtt'), 'Indeks Seri 2 link artikel #31');
check(str_contains($body, 'bluetooth-esp32-ble-kirim-data-sensor-smartphone'), 'Indeks Seri 2 link artikel #32');
check(str_contains($body, '#33') || str_contains($body, 'Servo'), 'Teaser Seri 2: Servo #33');

echo "\n=== Pass 2: HTTP render lokal ===\n\n";

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$request = Illuminate\Http\Request::create('/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22', 'GET');
$response = $kernel->handle($request);
$html = $response->getContent();

check($response->getStatusCode() === 200, 'GET artikel → 200');
check(str_contains($html, 'Dashboard ESP32'), 'Judul tampil di halaman');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'og:title'), 'OG meta ada');
check(str_contains($html, 'ESP32 IoT Dashboard'), 'Kode sketch ter-render');
check(str_contains($html, 'article-comments') || str_contains($html, 'livewire'), 'Komponen komentar ada');
$kernel->terminate($request, $response);

echo "\n=== Pass 3: Konsistensi dengan artikel #9 ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article9Seeder', '--force' => true]);

$a9 = Article::where('slug', 'gabungkan-dht22-relay-mqtt-esp32-satu-proyek')->first();
check($a9 !== null, 'Artikel #9 ada');
check(
    str_contains($a9?->body ?? '', 'dashboard-esp32-web-server-mqtt-monitoring-dht22'),
    'Artikel #9 backlink ke artikel #10'
);

if ($checkProduction) {
    echo "\n=== Pass 4: Production content (post-deploy) ===\n\n";

    $prodUrl = 'https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22';
    $prodHtml = (string) shell_exec('curl -sS --max-time 30 ' . escapeshellarg($prodUrl));

    check($prodHtml !== '', 'Production halaman bisa di-fetch');
    check(str_contains($prodHtml, 'Indeks Seri ESP32'), 'Production: indeks seri 10 artikel');
    check(str_contains($prodHtml, 'kontrol-lampu-esp32-mqtt-relay'), 'Production: link artikel #8');
    check(str_contains($prodHtml, 'jaringan WiFi rumah'), 'Production: troubleshooting WiFi rumah (bukan shared hosting)');
    check(! str_contains($prodHtml, 'shared hosting WiFi'), 'Production: tidak ada typo shared hosting');
    check(
        preg_match('/dht\.begin\(\)[\s\S]{0,120}delay\(2000\)/', $prodHtml),
        'Production: delay(2000) setelah dht.begin() di kode'
    );
    check(str_contains($prodHtml, 'setBufferSize(512)'), 'Production: setBufferSize(512)');
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
