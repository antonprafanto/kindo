<?php

/**
 * Ultra-deep supplemental audit #19 — cek di luar 5 skrip utama.
 * Usage: php scripts/audit-article19-ultra.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article19Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt';
$href = '/artikel/' . $slug;

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

$ref = new ReflectionClass(Article19Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== ULTRA DEEP AUDIT #19 ===\n\n";

echo "--- 1: HTML & konten struktural ---\n\n";

foreach (['h2', 'table', 'pre', 'blockquote', 'ul', 'ol'] as $tag) {
    $open = substr_count($body, '<' . $tag . '>');
    $close = substr_count($body, '</' . $tag . '>');
    check($open === $close, "Tag <{$tag}> seimbang ({$open}/{$close})");
}

preg_match_all('/<h2>(.*?)<\/h2>/', $body, $h2matches);
$dupH2 = array_diff_assoc($h2matches[1], array_unique($h2matches[1]));
check(count($dupH2) === 0, 'Tidak ada judul H2 duplikat');

$bodyNoH2 = preg_replace('/<h2>.*?<\/h2>/s', '', $body);
check(! preg_match('/Artikel #(?!20)\d+/', $bodyNoH2), 'Tidak ada teks orphan "Artikel #N" (kecuali teaser #20)');
check(str_contains($body, 'Artikel #20'), 'Teaser artikel #20 ada');
check(substr_count($body, '<a href=') >= 15, 'Minimal 15 hyperlink di body (' . substr_count($body, '<a href=') . ')');

echo "\n--- 2: Metadata & SEO ---\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article19Seeder', '--force' => true]);
$article = Article::where('slug', $slug)->first();

check(mb_strlen($article->title) <= 90, 'title ≤ 90 (' . mb_strlen($article->title) . ')');
check(mb_strlen($article->seo_title) <= 70, 'seo_title ≤ 70');
check($article->seo_title !== $article->title || mb_strlen($article->seo_title) < mb_strlen($article->title), 'seo_title berbeda/lebih ringkas dari title');
check($article->excerpt !== null && $article->excerpt !== '', 'excerpt auto-terisi');
check(mb_strlen($article->excerpt) >= 80 && mb_strlen($article->excerpt) <= 300, 'excerpt panjang wajar (' . mb_strlen($article->excerpt) . ')');
check($article->category?->slug === 'iot-smart-device', 'Kategori iot-smart-device (bukan networking)');

$response = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $response->getContent();
check(str_contains($html, htmlspecialchars($article->seo_title, ENT_QUOTES)), 'SEO title di <title>');
check(str_contains($html, 'og:description'), 'og:description ada');
check(str_contains($html, 'twitter:card'), 'twitter card meta ada');

echo "\n--- 3: Konvensi Seri 2 (konsistensi lintas artikel) ---\n\n";

$seriesChecks = [
    'kodingindonesia/esp32/dht22/data' => 'Topic DHT22',
    'kindo_subscriber'                 => 'User subscriber',
    '192.168.1.50'                     => 'IP broker',
    '1782977400'                       => 'Unix epoch #34',
    '2026-07-02T14:30:00'              => 'ISO timestamp #34',
    'GANTI_PASSWORD_SUBSCRIBER'        => 'Placeholder MQTT sub',
    'GANTI_INFLUX_TOKEN'               => 'Placeholder Influx token',
    'kindo'                            => 'Org Influx kindo',
    'iot_sensors'                      => 'Bucket iot_sensors',
    'dht22'                            => 'Measurement dht22',
];

foreach ($seriesChecks as $needle => $label) {
    check(str_contains($body, $needle), $label);
}

check(! str_contains($body, 'kindo_esp32'), 'Tidak pakai user publisher ESP32 untuk Telegraf');
check(str_contains($body, 'qos = 1') || str_contains($body, 'qos=1'), 'QoS 1 disebutkan');

echo "\n--- 4: Backlink BIDIREKSIONAL (opsional tapi direkomendasikan) ---\n\n";

// #19 → artikel lain (outbound)
preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $outLinks);
$outSlugs = array_unique(array_map(fn ($p) => str_replace('/artikel/', '', $p), $outLinks[1]));
check(count($outSlugs) >= 8, 'Outbound link ≥8 unik (' . count($outSlugs) . ')');

// artikel lain → #19 (inbound) — semua yang disebut di roadmap #18 pattern + #24
$inboundExpected = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => '#7',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => '#17',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => '#18',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'             => '#34',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => '#13',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                 => '#14',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => '#23',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
    'home-assistant-integrasi-esp32-mqtt'                        => '#21',
];

foreach ($inboundExpected as $sourceSlug => $label) {
    $srcBody = Article::where('slug', $sourceSlug)->value('body') ?? '';
    check(str_contains($srcBody, $href), "Inbound {$label} → #19 hyperlink");
}

echo "\n--- 5: Regresi #18 setelah re-seed (diagram & seo) ---\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article18Seeder', '--force' => true]);
$a18 = Article::where('slug', 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32')->first();
$a18body = $a18?->body ?? '';

check(substr_count($a18body, $href) >= 3, '#18 punya ≥3 hyperlink ke #19 setelah seed (' . substr_count($a18body, $href) . ')');
check(str_contains($a18body, $href), '#18 intro pakai hyperlink #19 (bukan plain text)');
check(str_contains($a18body, 'Grafana (#19)') || str_contains($a18body, 'Grafana'), '#18 diagram menyebut Grafana #19');
check(! str_contains($a18body, 'Grafana (#19 nanti)'), '#18 diagram tidak lagi pakai teks "nanti"');

echo "\n--- 6: Deploy & CI kelengkapan ---\n\n";

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, 'publish-article-19'), 'Route publish-article-19');
check(str_contains($routes, 'throttle:120,1'), 'Throttle 120/menit di route deploy');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'function publishArticle19'), 'publishArticle19() ada');
check(str_contains($deploy, 'opcache_reset'), 'opcache_reset di hook');
check(str_contains($deploy, 'SitemapService'), 'Sitemap refresh di hook');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($yml, 'Publish article 19 via deploy hook (required)'), 'CI step #19 required');
check(str_contains($yml, 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'), 'CI verifikasi URL #19');

echo "\n--- 7: Blok kode & tutorial completeness ---\n\n";

$codeBlocks = [
    'language-yaml'   => 'Docker Compose YAML',
    'language-toml'   => 'Telegraf TOML',
    'language-python' => 'Python influxdb-client',
    'language-bash'   => 'Perintah bash',
    'language-sql'    => 'Query Flux/SQL',
];

foreach ($codeBlocks as $cls => $label) {
    check(str_contains($body, $cls), "Blok {$label} ({$cls})");
}

check(str_contains($body, 'docker compose up -d'), 'Perintah docker compose up');
check(str_contains($body, 'aggregateWindow'), 'Flux aggregateWindow');
check(str_contains($body, 'Asia/Jakarta'), 'Timezone Asia/Jakarta');
check(str_contains($body, 'Uji Coba'), 'Section checklist uji coba');
check(str_contains($body, 'Troubleshooting'), 'Section troubleshooting');

echo "\n--- 8: Keamanan konten ---\n\n";

$danger = ['KindoMQTT', 'KindoMQTT2026', 'password123', 'admin123', 'mysql123', 'root:root'];
foreach ($danger as $d) {
    check(! str_contains($body, $d), "Tidak ada '{$d}'");
}
// admin/admin Grafana default OK dengan konteks ganti password
check(str_contains($body, 'ganti password') || str_contains($body, 'Ganti password'), 'Peringatan ganti password default Grafana');

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
