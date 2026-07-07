<?php
/** Quick final verification — run: php scripts/audit-article20-final.php */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$slug = 'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32';
$href = '/artikel/' . $slug;
$passed = 0;
$failed = 0;

function c(bool $ok, string $l): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " $l\n";
    $ok ? $passed++ : $failed++;
}

echo "=== FINAL CROSS-CHECK #20 ===\n\n";

foreach ([
    'Article20Seeder', 'Article19Seeder', 'Article18Seeder', 'Article10Seeder',
    'Article7Seeder', 'Article6Seeder', 'Article17Seeder', 'Article16Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$a = Article::where('slug', $slug)->first();
c($a && $a->status === 'published', 'Artikel #20 published di DB');
c($a && $a->read_time_minutes >= 8, 'read_time ≥ 8 menit (' . ($a->read_time_minutes ?? 0) . ')');
c(strtotime('2026-07-02 14:30:00 +0700') === 1782977400, 'Unix/timestamp WIB konsisten #34');

$sources = [
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32'        => '#18',
    'influxdb-grafana-dashboard-histori-sensor-esp32-mqtt'         => '#19',
];
foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    c(str_contains($b, $href), "$lbl → #20 hyperlink");
}

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
c($r->getStatusCode() === 200, 'HTTP 200 lokal');
c(str_contains($html, 'Arsitektur Hybrid') && str_contains($html, '/api/data'), 'Konten kunci ter-render');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
if (preg_match('/function publishArticle20\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m)) {
    $hookBody = $m[1];
    c(strpos($hookBody, 'Article20Seeder') < strpos($hookBody, 'Article19Seeder'), 'Hook: seed #20 sebelum re-seed backlink');
    c(strpos($hookBody, 'Article6Seeder') < strpos($hookBody, 'runDuplicateBme280Cleanup'), 'Hook: re-seed #6 sebelum cleanup');
} else {
    c(false, 'publishArticle20() ditemukan di DeployController');
}

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
c(strpos($yml, 'publish-article-19') < strpos($yml, 'publish-article-20'), 'CI: hook #19 sebelum #20');
c(preg_match('/Publish article 20 via deploy hook \(required\)/', $yml) === 1, 'CI: hook #20 required');

echo "\n=== RESULT: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
