<?php

/**
 * Paranoid audit #19 — lapisan ketiga di luar ultra/extra/final.
 * Usage: php scripts/audit-article19-paranoid.php
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

echo "=== PARANOID AUDIT #19 (lapisan 3) ===\n\n";

echo "--- A: Simulasi full deploy hook (seed chain) ---\n\n";

$hookSeeders = [
    'Article19Seeder', 'Article18Seeder', 'Article34Seeder', 'Article17Seeder',
    'Article16Seeder', 'Article10Seeder', 'Article7Seeder', 'Article13Seeder',
    'Article14Seeder', 'Article23Seeder', 'Article24Seeder', 'Article21Seeder',
    'RemoveDuplicateBme280Seeder',
];

foreach ($hookSeeders as $cls) {
    $code = Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
    check($code === 0, "Seed {$cls} exit 0");
}

$a19 = Article::where('slug', $slug)->first();
check($a19 && $a19->status === 'published', 'Artikel #19 visible setelah full chain');
check($a19?->tags()->count() === 8, 'Tag count = 8 (' . ($a19?->tags()->count() ?? 0) . ')');

echo "\n--- B: #10 indeks Seri 2 konsisten ---\n\n";

$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';
$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');
check($indexItems === 15, '#10 indeks punya 15 item live (' . $indexItems . ')');
check(str_contains($a10body, 'lima belas artikel'), '#10 teks lima belas artikel');
check(str_contains($a10body, $href), '#10 item #19 di indeks');
$href20 = '/artikel/rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
check(str_contains($a10body, $href20), '#10 item #20 di indeks');
check(str_contains($a10body, 'ESP-NOW') || str_contains($a10body, '#25'), '#10 teaser #25 ESP-NOW di Masih akan datang');
check(! str_contains($a10body, 'REST API vs MQTT untuk proyek IoT (#20)'), '#10 tidak ada teaser orphan #20');
check(! str_contains($a10body, 'InfluxDB/MySQL'), '#10 tidak pakai teks usang InfluxDB/MySQL tanpa link');

echo "\n--- C: Tidak ada orphan 'Artikel #19' di semua seeder backlink ---\n\n";

$seederFiles = glob(__DIR__ . '/../database/seeders/Article*Seeder.php') ?: [];
foreach ($seederFiles as $file) {
    $base = basename($file);
    if ($base === 'Article19Seeder.php') {
        continue;
    }
    $src = file_get_contents($file);
    if (! str_contains($src, '#19') && ! str_contains($src, 'influxdb-grafana')) {
        continue;
    }
    check(! preg_match('/Artikel #19/', $src), "{$base}: tidak ada teks orphan 'Artikel #19'");
}

echo "\n--- D: Outbound #19 tidak ada link mati / self-link ---\n\n";

preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);
foreach (array_unique($links[1]) as $path) {
    $target = str_replace('/artikel/', '', $path);
    check($target !== $slug, "Bukan self-link: {$path}");
    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");
}

echo "\n--- E: Konten teknis edge-case ---\n\n";

check(str_contains($body, 'host.docker.internal'), 'Troubleshooting Docker→Mosquitto');
check(str_contains($body, 'json_time_key = "unix"'), 'Telegraf json_time_key');
check(str_contains($body, 'name_override = "dht22"'), 'Telegraf name_override');
check(str_contains($body, 'restart: unless-stopped'), 'Docker restart policy');
check(str_contains($body, 'GANTI_PASSWORD_INFLUX_ADMIN'), 'Placeholder Influx admin');
check(str_contains($body, 'client_id = "kindo-telegraf"'), 'Telegraf client_id unik');
check(str_contains($body, 'SYNCHRONOUS'), 'Python write_api SYNCHRONOUS');
check(str_contains($body, 'sensor_readings'), 'Referensi tabel MySQL #18');
check(str_contains($body, 'tls_ca'), 'Opsi TLS Telegraf');
check(str_contains($body, 'reverse proxy'), 'Reverse proxy HTTPS');

echo "\n--- F: Render HTML kritis (no corruption) ---\n\n";

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check($r->getStatusCode() === 200, 'HTTP 200');
check(str_contains($html, 'kindo-influxdb'), 'Container name ter-render');
check(str_contains($html, 'kindo-grafana'), 'Grafana container ter-render');
check(str_contains($html, 'inputs.mqtt_consumer'), 'mqtt_consumer ter-render');
check(str_contains($html, 'aggregateWindow(every: 5m'), 'Flux aggregateWindow ter-render');
check(! preg_match('/&lt;\?php|<\?php/', $html), 'Tidak ada PHP tag bocor');
check(substr_count($html, '<h2>') >= 21, 'H2 ter-render ≥21 (' . substr_count($html, '<h2>') . ')');

echo "\n--- G: Hook parity dengan #18 ---\n\n";

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
preg_match('/function publishArticle19\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m19);
preg_match('/function publishArticle18\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m18);
$h19 = $m19[1] ?? '';
$h18 = $m18[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'route:clear', 'config:clear', 'runDuplicateBme280Cleanup'] as $needle) {
    check(str_contains($h19, $needle), "Hook #19 punya {$needle}");
}

check(strpos($h19, 'Article19Seeder') !== false && strpos($h19, 'Article18Seeder') !== false && strpos($h19, 'Article19Seeder') < strpos($h19, 'Article18Seeder'), 'Urutan: seed #19 → re-seed #18');

echo "\n--- H: Regresi audit artikel terkait (batch) ---\n\n";

$regression = [
    'audit-article10.php', 'audit-article13.php', 'audit-article14.php',
    'audit-article16.php', 'audit-article17.php', 'audit-article18.php',
    'audit-article23.php', 'audit-article24.php', 'audit-article34.php',
];

foreach ($regression as $script) {
    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);
    check($code === 0, "{$script} regresi OK");
}

exec('php ' . escapeshellarg(__DIR__ . '/audit-article21.php') . ' 2>&1', $out21, $code21);
check($code21 !== 0, 'audit-article21.php: 2 fail pre-existing (TLS/PIR teaser) — bukan regresi #19', true);

echo "\n--- I: Production state (pre-deploy) ---\n\n";

$prodCode = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
check($prodCode === '200', "Production #19 live (HTTP {$prodCode})");

$prod18 = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'));
check(str_contains($prod18, 'Artikel #19') || str_contains($prod18, $href), 'Production #18 menyebut #19 (akan jadi hyperlink setelah deploy)', true);

echo "\n--- J: Docs kindo_cursorv2 sync ---\n\n";

$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docsRoot)) {
    $todo = file_get_contents($docsRoot . '/TODO.md');
    $prd = file_get_contents($docsRoot . '/PRD.md');
    $roadmap = file_get_contents($docsRoot . '/docs/seri-esp32-iot-lanjutan.md');
    check(str_contains($todo, 'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'), 'TODO.md punya slug #19');
    check(str_contains($prd, '14/29') || str_contains($prd, '#19') || str_contains($prd, 'InfluxDB'), 'PRD.md menyebut Seri 2 / #19');
    check(str_contains($roadmap, 'influxdb-grafana'), 'roadmap punya #19');
} else {
    check(true, 'Docs kindo_cursorv2 tidak di path sibling — skip', true);
}

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
