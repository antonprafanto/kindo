<?php

/**
 * Deep audit artikel #34 — cek tambahan di luar audit-article34.php
 * Usage: php scripts/audit-article34-deep.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article34Seeder;
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

$ref = new ReflectionClass(Article34Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$slug = 'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt';
$article = Article::where('slug', $slug)->first();

echo "=== DEEP AUDIT #34 — Pass A: Metadata ===\n\n";

check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article->title) <= 90, 'title ≤ 90 char (' . mb_strlen($article->title) . ')');
check(mb_strlen($article->seo_title) <= 70, 'seo_title ≤ 70 char');
check(mb_strlen($article->seo_description) >= 80 && mb_strlen($article->seo_description) <= 160, 'seo_description 80–160 char');
check($article->read_time_minutes >= 7, 'read_time ≥ 7 menit (' . $article->read_time_minutes . ')');
check(preg_match('/^[a-z0-9]+(-[a-z0-9]+)*$/', $slug) === 1, 'slug format URL-safe');

$words = str_word_count(strip_tags($body));
check($words >= 1300, 'word_count ≥ 1300 (' . $words . ')');

echo "\n=== Pass B: HTML & struktur ===\n\n";

check(substr_count($body, '<h2>') >= 12, 'H2 ≥ 12 (' . substr_count($body, '<h2>') . ')');
check(substr_count($body, '<table>') >= 2, 'Minimal 2 tabel');
check(substr_count($body, '<pre>') === substr_count($body, '</pre>'), 'Tag pre seimbang');
check(substr_count($body, '<code') === substr_count($body, '</code>'), 'Tag code seimbang');
check(substr_count($body, '<blockquote>') === substr_count($body, '</blockquote>'), 'Tag blockquote seimbang');
check(! str_contains($body, '<h1>'), 'Tidak ada h1 ganda di body');
check(! preg_match('/href="\/artikel\/"/', $body), 'Tidak ada link /artikel/ kosong');

echo "\n=== Pass C: Validasi link internal ===\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $matches);
foreach (array_unique($matches[1]) as $path) {
    $targetSlug = str_replace('/artikel/', '', $path);
    if ($targetSlug === '') {
        continue;
    }
    check(Article::where('slug', $targetSlug)->exists(), "Link valid: {$path}");
}

echo "\n=== Pass D: NTP & timestamp ===\n\n";

check(str_contains($body, '#include "time.h"'), 'Include time.h');
check(str_contains($body, 'strftime'), 'Fungsi strftime');
check(str_contains($body, 'struct tm'), 'struct tm timeinfo');
check(str_contains($body, '7 * 3600'), 'Offset WIB di kode');
check(str_contains($body, '8 * 3600'), 'Offset WITA di tabel/kode');
check(str_contains($body, '9 * 3600'), 'Offset WIT di tabel/kode');
check(str_contains($body, 'time(nullptr)'), 'Unix epoch time(nullptr)');

$isoExample = '2026-07-02T14:30:00';
$unixExample = 1782977400;
check(
    str_contains($body, $isoExample) && str_contains($body, (string) $unixExample)
    && strtotime('2026-07-02 14:30:00 +0700') === $unixExample,
    'Contoh payload: ISO + unix konsisten (WIB)'
);

echo "\n=== Pass E: Konsistensi MQTT Seri ===\n\n";

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();

check(str_contains($body, '"suhu"'), 'Payload field suhu');
check(str_contains($body, '"kelembaban"'), 'Payload field kelembaban');
check(str_contains($a16?->body ?? '', 'kodingindonesia/esp32/dht22/data'), '#16 topic sensor sama');

echo "\n=== Pass F: Logika firmware (review statis) ===\n\n";

check(str_contains($body, 'WL_CONNECTED'), 'Cek WiFi sebelum NTP');
check(str_contains($body, 'maxRetry'), 'Max retry NTP');
check(str_contains($body, 'ESP.getEfuseMac'), 'Client ID unik');
check(str_contains($body, 'StaticJsonDocument'), 'ArduinoJson');
check(str_contains($body, 'isnan'), 'Cek DHT22 NaN');
check(preg_match('/percobaan\s*(?:&lt;|<)\s*5/', $body) === 1, 'koneksiMQTT max 5 retry');
check(
    preg_match('/bool publishSensorDenganWaktu[\s\S]{0,600}ambilTimestampISO/', $body) === 1,
    'publishSensor pakai ambilTimestampISO (bukan re-sync NTP)'
);
check(str_contains($body, 'ambilTimestampISO'), 'Helper ambilTimestampISO ada di sketch');
check(str_contains($body, 'mqttClient.state()'), 'Debug rc MQTT');

echo "\n=== Pass G: Keamanan konten ===\n\n";

check(! str_contains($body, 'KindoMQTT2026'), 'Tidak ada password literal');
check(! str_contains($body, 'test.mosquitto.org'), 'Broker privat #16 (bukan publik)');
check(str_contains($body, 'Jangan hardcode'), 'Peringatan credential');

echo "\n=== Pass H: Backlink dua arah ===\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article34Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article17Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article16Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article11Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article24Seeder', '--force' => true]);
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article10Seeder', '--force' => true]);

$sources = [
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                    => '#17',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                  => '#11',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
];

foreach ($sources as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    check($src !== null && str_contains($src->body ?? '', $slug), "{$label} → #34 backlink");
}

echo "\n=== Pass I: Indeks #10 ===\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first()?->body ?? '';
check(str_contains($a10body, $slug), '#10 indeks mencantumkan #34');
check(str_contains($a10body, 'dua belas artikel pertama'), '#10 teks dua belas artikel');

echo "\n=== Pass J: HTTP render ===\n\n";

$response = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = (string) $response->getContent();

check($response->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'sinkronisasiNTP'), 'Kode NTP ter-render');
check(str_contains($html, 'application/ld+json'), 'JSON-LD');
check(str_contains($html, 'og:description'), 'og:description');
check(str_contains($html, 'canonical'), 'canonical URL');

echo "\n=== Pass K: Infrastruktur deploy ===\n\n";

check(file_exists(__DIR__ . '/../database/seeders/Article34Seeder.php'), 'Article34Seeder.php ada');
check(str_contains(file_get_contents(__DIR__ . '/../routes/web.php'), 'publish-article-34'), 'Route deploy ada');
check(str_contains(file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php'), 'publishArticle34'), 'DeployController method ada');
check(str_contains(file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml'), 'publish-article-34'), 'CI hook deploy.yml ada');

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
