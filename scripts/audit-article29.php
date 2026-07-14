<?php

/**
 * Audit artikel #29 — Migrasi PlatformIO.
 * Usage: php scripts/audit-article29.php [--production]
 */

$checkProduction = in_array('--production', $argv, true);

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article29Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$slug   = 'migrasi-platformio-esp32-vscode-project-rapi';

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

function seederBody(): string
{
    $ref = new ReflectionClass(Article29Seeder::class);
    $method = $ref->getMethod('body');
    $method->setAccessible(true);

    return $method->invoke($ref->newInstanceWithoutConstructor());
}

echo "=== Audit Artikel #29 — Pass 1: Seeder & DB ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article29Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article28Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PatchArticle2PlatformioSeeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

$article = Article::where('slug', $slug)->first();
$body = seederBody();

check($article !== null, 'Artikel ada di database setelah seed');
check($article?->status === 'published', 'Status published');
check($article?->published_at !== null, 'published_at terisi');
check($article?->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');
check($article?->is_featured === false, 'is_featured false');

$requiredTags = ['esp32', 'platformio', 'vscode', 'iot', 'arduino', 'mqtt'];
$articleTags = $article?->tags->pluck('slug')->all() ?? [];
foreach ($requiredTags as $tag) {
    check(in_array($tag, $articleTags, true), "Tag: {$tag}");
}

$requiredLinks = [
    'cara-install-arduino-ide-setup-esp32-board-manager'              => 'Artikel #2 Arduino IDE',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                  => 'Artikel #7 MQTT',
    'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard'           => 'Artikel #28 Gateway LoRa',
    'ota-update-firmware-esp32-via-wifi'                            => 'Artikel #15 OTA',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                   => 'Artikel #17 TLS',
    'esphome-flash-esp32-tanpa-coding-arduino'                    => 'Artikel #22 ESPHome',
    'esp32-cam-streaming-mjpeg-capture-foto-wifi'                   => 'Artikel #27 ESP32-CAM',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => 'Artikel #16 Mosquitto',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'        => 'Artikel #19 Grafana',
    'blink-led-esp32-tutorial-pertama-embedded-system'            => 'Artikel #3 Blink',
    'freertos-esp32-multi-task-sensor-wifi-mqtt'                  => 'Artikel #31 FreeRTOS',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'       => 'Artikel #39 Greenhouse',
    'esp32-firebase-realtime-database-sensor-cloud'               => 'Artikel #30 Firebase',
];

foreach ($requiredLinks as $linkSlug => $label) {
    check(str_contains($body, '/artikel/' . $linkSlug), "Link internal: {$label}");
    check(Article::where('slug', $linkSlug)->exists(), "Target exists: {$linkSlug}");
}

check(str_contains($body, 'Jalur E'), 'Menyebut Jalur E');
check(str_contains($body, 'platformio.ini'), 'File platformio.ini');
check(str_contains($body, 'lib_deps'), 'lib_deps di contoh');
check(str_contains($body, 'GANTI_NAMA_WIFI'), 'Placeholder WiFi SSID');
check(str_contains($body, 'GANTI_PASSWORD_WIFI'), 'Placeholder password WiFi');
check(str_contains($body, 'GANTI_PASSWORD_MQTT'), 'Placeholder password MQTT');
check(str_contains($body, 'language-yaml'), 'Blok YAML CI');
check(str_contains($body, '.pio/'), 'Peringatan folder .pio');
check(str_contains($body, 'pio run'), 'Perintah pio run');
check(str_contains($body, 'Checklist'), 'Section checklist');
check(str_contains($body, '<svg') && str_contains($body, 'platformio.ini'), 'Diagram struktur folder PlatformIO SVG');
check(! str_contains($body, '├──'), 'Tidak ada tree ASCII folder');
check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');
check(str_contains($body, 'kodingindonesia/esp32/dht22/data'), 'Topic sensor konsisten');
check(str_contains($body, '192.168.1.50'), 'IP broker contoh');
check(str_contains($body, 'kindo_esp32'), 'User MQTT publisher');
check(str_contains($body, 'language-cpp'), 'Blok C++');
check(str_contains($body, 'language-ini'), 'Blok ini platformio');
check(str_contains($body, 'language-bash'), 'Blok bash');
check(str_contains($body, 'Pro tip'), 'Pro tip');
check(str_contains($body, 'Keamanan'), 'Section keamanan');
check(str_contains($body, 'Estimasi Biaya'), 'Estimasi biaya');
check(str_contains($body, '#30'), 'Teaser Firebase #30');
check(str_contains($body, 'FreeRTOS (#31)'), 'Teaser FreeRTOS #31');
check(str_contains($body, 'lora_packet_t'), 'Referensi struct gateway #28');

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
check(str_contains($html, 'PlatformIO'), 'Konten PlatformIO ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD schema ada');
check(str_contains($html, 'platformio.ini'), 'platformio.ini ter-render');

echo "\n=== Pass 3: Konsistensi Seri 2 (backlink) ===\n\n";

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'   => '#10',
    'gateway-lora-mqtt-esp32-sensor-jarak-jauh-dashboard' => '#28',
    'cara-install-arduino-ide-setup-esp32-board-manager'  => '#2',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null, "Artikel {$label} ada");
    check(str_contains($src?->body ?? '', $slug), "Artikel {$label} backlink → #29");
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
