<?php
/** Quick final verification — run: php scripts/audit-article26-final.php */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$slug = 'lora-esp32-modul-sx1278-kirim-data-jarak-jauh';
$href = '/artikel/' . $slug;
$passed = 0;
$failed = 0;

function c(bool $ok, string $l): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " $l\n";
    $ok ? $passed++ : $failed++;
}

echo "=== FINAL CROSS-CHECK #26 ===\n\n";

foreach ([
    'Article26Seeder', 'Article25Seeder', 'Article20Seeder', 'Article10Seeder',
    'Article11Seeder', 'Article7Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$a = Article::where('slug', $slug)->first();
c($a && $a->status === 'published', 'Artikel #26 published di DB');
c($a && $a->read_time_minutes >= 8, 'read_time ≥ 8 menit (' . ($a->read_time_minutes ?? 0) . ')');
c(strtotime('2026-07-02 14:30:00 +0700') === 1782977400, 'Unix/timestamp WIB konsisten #34');

$sources = [
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'esp-now-kirim-data-antar-esp32-tanpa-router-wifi'               => '#25',
    'rest-api-vs-mqtt-kapan-pakai-proyek-iot-esp32'                  => '#20',
    'deep-sleep-esp32-sensor-dht22-hemat-baterai'                    => '#11',
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                   => '#7',
];
foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    c(str_contains($b, $href), "$lbl → #26 hyperlink");
}

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
c($r->getStatusCode() === 200, 'HTTP 200 lokal');
c(str_contains($html, 'SX1278') && str_contains($html, 'LoRa.begin'), 'Konten kunci ter-render');

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
if (preg_match('/function publishArticle26\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m)) {
    $hookBody = $m[1];
    c(strpos($hookBody, 'Article26Seeder') < strpos($hookBody, 'Article25Seeder'), 'Hook: seed #26 sebelum re-seed backlink');
    c(strpos($hookBody, 'Article7Seeder') < strpos($hookBody, 'runDuplicateBme280Cleanup'), 'Hook: re-seed #7 sebelum cleanup');
} else {
    c(false, 'publishArticle26() ditemukan di DeployController');
}

$yml = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
c(strpos($yml, 'publish-article-25') < strpos($yml, 'publish-article-26'), 'CI: hook #25 sebelum #26');
c(preg_match('/Publish article 26 via deploy hook \(required\)/', $yml) === 1, 'CI: hook #26 required');

echo "\n=== RESULT: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
