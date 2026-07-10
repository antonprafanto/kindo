<?php

/**
 * Ultra-deep supplemental audit #26 — cek di luar 5 skrip utama.
 * Usage: php scripts/audit-article26-ultra.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article26Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn = 0;
$slug = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';
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

$ref = new ReflectionClass(Article26Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== ULTRA DEEP AUDIT #26 ===\n\n";

echo "--- 1: HTML & konten struktural ---\n\n";

foreach (['h2', 'table', 'pre', 'blockquote', 'ul', 'ol', 'dl'] as $tag) {
    $open = substr_count($body, '<' . $tag . '>');
    $close = substr_count($body, '</' . $tag . '>');
    check($open === $close, "Tag <{$tag}> seimbang ({$open}/{$close})");
}

preg_match_all('/<h2>(.*?)<\/h2>/', $body, $h2matches);
$dupH2 = array_diff_assoc($h2matches[1], array_unique($h2matches[1]));
check(count($dupH2) === 0, 'Tidak ada judul H2 duplikat');

$bodyNoH2 = preg_replace('/<h2>.*?<\/h2>/s', '', $body);
check(! preg_match('/Artikel #\d+/', $bodyNoH2), 'Tidak ada teks orphan "Artikel #N"');
check(str_contains($body, '#28'), 'Teaser gateway #28 ada');
check(substr_count($body, '<a href=') >= 10, 'Minimal 10 hyperlink di body (' . substr_count($body, '<a href=') . ')');

echo "\n--- 2: Metadata & SEO ---\n\n";

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article26Seeder', '--force' => true]);
$article = Article::where('slug', $slug)->first();

check(mb_strlen($article->title) <= 90, 'title ≤ 90 (' . mb_strlen($article->title) . ')');
check(mb_strlen($article->seo_title) <= 70, 'seo_title ≤ 70');
check($article->seo_title !== $article->title || mb_strlen($article->seo_title) < mb_strlen($article->title), 'seo_title berbeda/lebih ringkas dari title');
check($article->excerpt !== null && $article->excerpt !== '', 'excerpt auto-terisi');
check(mb_strlen($article->excerpt) >= 80 && mb_strlen($article->excerpt) <= 300, 'excerpt panjang wajar (' . mb_strlen($article->excerpt) . ')');
check($article->category?->slug === 'esp32-arduino', 'Kategori esp32-arduino');

$response = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $response->getContent();
check(str_contains($html, htmlspecialchars($article->seo_title, ENT_QUOTES)), 'SEO title di <title>');
check(str_contains($html, 'og:description'), 'og:description ada');
check(str_contains($html, 'twitter:card'), 'twitter card meta ada');

echo "\n--- 3: Konvensi Seri 2 ---\n\n";

$seriesChecks = [
    'kodingindonesia/esp32/dht22/data' => 'Topic DHT22',
    'kindo_esp32'                      => 'User MQTT publisher',
    '192.168.1.50'                     => 'IP broker',
    '1782977400'                       => 'Unix epoch #34',
    '2026-07-02T14:30:00'              => 'ISO timestamp #34',
    'GANTI_PASSWORD_MQTT'              => 'Placeholder MQTT',
];

foreach ($seriesChecks as $needle => $label) {
    check(str_contains($body, $needle), $label);
}

check(str_contains($body, 'Jalur D'), 'Menyebut Jalur D');
check(str_contains($body, 'peer-to-peer') || str_contains($body, 'point-to-point'), 'Konsep P2P LoRa');

echo "\n--- 4: Backlink BIDIREKSIONAL ---\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $outLinks);
$outSlugs = array_unique(array_map(fn ($p) => str_replace('/artikel/', '', $p), $outLinks[1]));
check(count($outSlugs) >= 8, 'Outbound link ≥8 unik (' . count($outSlugs) . ')');

foreach ([
    'Article25Seeder', 'Article20Seeder', 'Article10Seeder', 'Article11Seeder', 'Article7Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$inboundExpected = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'               => '#25',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
];

foreach ($inboundExpected as $sourceSlug => $label) {
    $srcBody = Article::where('slug', $sourceSlug)->value('body') ?? '';
    check(str_contains($srcBody, $href), "Inbound {$label} → #26 hyperlink");
}

echo "\n--- 5: Regresi #25 setelah re-seed ---\n\n";

$a25body = Article::where('slug', 'esp-now-kirim-data-antar-esp32-tanpa-router-wifi')->value('body') ?? '';
check(str_contains($a25body, $href), '#25 Langkah Selanjutnya hyperlink ke #26');
check(! str_contains($a25body, 'LoRa SX1278 (#26):'), '#25 tidak ada teaser orphan LoRa (#26) tanpa link');

echo "\n--- 6: Deploy & CI ---\n\n";

$routes = file_get_contents(__DIR__ . '/../routes/web.php');
check(str_contains($routes, 'publish-article-26'), 'Route publish-article-26');
check(str_contains($routes, 'throttle:120,1'), 'Throttle 120/menit');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'function publishArticle26'), 'publishArticle26() ada');
check(str_contains($deploy, 'SitemapService'), 'Sitemap refresh di hook');

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($yml, 'Publish article 26 via deploy hook (required)'), 'CI step #26 required');
check(str_contains($yml, 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'), 'CI verifikasi URL #26');
check(strpos($yml, 'publish-article-25') < strpos($yml, 'publish-article-26'), 'CI #25 sebelum #26');

echo "\n--- 7: Blok kode & tutorial ---\n\n";

foreach (['language-cpp' => 'C++', 'language-bash' => 'bash'] as $cls => $label) {
    check(str_contains($body, $cls), "Blok {$label} ({$cls})");
}

check(str_contains($body, 'mosquitto_sub'), 'Perintah mosquitto_sub');
check(str_contains($body, 'Uji Coba'), 'Section uji coba');
check(str_contains($body, 'Troubleshooting'), 'Section troubleshooting');
check(str_contains($body, 'FAQ'), 'Section FAQ');
check(str_contains($body, 'Estimasi biaya'), 'Estimasi biaya');

echo "\n--- 8: Keamanan konten ---\n\n";

$danger = ['KindoMQTT', 'KindoMQTT2026', 'password123', 'admin123'];
foreach ($danger as $d) {
    check(! str_contains($body, $d), "Tidak ada '{$d}'");
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
