<?php
/** Quick final verification — run: php scripts/audit-article27-final.php */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$slug = 'esp32-cam-streaming-mjpeg-capture-foto-wifi';
$href = '/artikel/' . $slug;
$passed = 0;
$failed = 0;

function c(bool $ok, string $l): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " $l\n";
    $ok ? $passed++ : $failed++;
}

echo "=== FINAL CROSS-CHECK #27 ===\n\n";

foreach ([
    'Article27Seeder', 'Article26Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article6Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$a = Article::where('slug', $slug)->first();
c($a && $a->status === 'published', 'Artikel #27 published di DB');
c($a && $a->read_time_minutes >= 8, 'read_time ≥ 8 menit (' . ($a->read_time_minutes ?? 0) . ')');
c(strtotime('2026-07-02 14:30:00 +0700') === 1782977400, 'Unix/timestamp WIB konsisten #34');

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'lora-esp32-modul-sx1278-kirim-data-jarak-jauh'                  => '#26',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'membuat-web-server-esp32-monitoring-sensor-dht22'               => '#6',
];
foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    c(str_contains($b, $href), "$lbl → #27 hyperlink");
}

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
c($r->getStatusCode() === 200, 'HTTP 200 lokal');
c(str_contains($html, 'ESP32-CAM') && str_contains($html, 'esp_camera_init'), 'Konten kunci ter-render');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
if (preg_match('/function publishArticle27\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m)) {
    $hookBody = $m[1];
    c(strpos($hookBody, 'Article27Seeder') < strpos($hookBody, 'Article26Seeder'), 'Hook: seed #27 sebelum re-seed backlink');
    c(strpos($hookBody, 'Article20Seeder') < strpos($hookBody, 'runDuplicateBme280Cleanup'), 'Hook: re-seed #20 sebelum cleanup');
} else {
    c(false, 'publishArticle27() ditemukan di DeployController');
}

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
c(strpos($yml, 'publish-article-26') < strpos($yml, 'publish-article-27'), 'CI: hook #26 sebelum #27');
c(preg_match('/Publish article 27 via deploy hook \(required\)/', $yml) === 1, 'CI: hook #27 required');

echo "\n=== RESULT: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
