<?php
/** Quick final verification — run: php scripts/audit-article18-final.php */
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

$slug = 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32';
$href = '/artikel/' . $slug;
$passed = 0; $failed = 0;

function c(bool $ok, string $l): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " $l\n";
    $ok ? $passed++ : $failed++;
}

echo "=== FINAL CROSS-CHECK #18 ===\n\n";

foreach ([
    'Article18Seeder','Article34Seeder','Article17Seeder','Article16Seeder',
    'Article10Seeder','Article7Seeder','Article13Seeder','Article14Seeder',
    'Article23Seeder','Article24Seeder',
] as $cls) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]);
}

$a = Article::where('slug', $slug)->first();
c($a && $a->status === 'published', 'Artikel #18 published di DB');
c(strtotime('2026-07-02 14:30:00 +0700') === 1782977400, 'Unix/timestamp WIB konsisten #34');

$sources = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker' => '#7',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22' => '#10',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32' => '#17',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt' => '#34',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt' => '#13',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c' => '#14',
    'node-red-dashboard-otomasi-iot-mqtt-esp32' => '#23',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce' => '#24',
];
foreach ($sources as $s => $lbl) {
    $b = Article::where('slug', $s)->value('body') ?? '';
    c(str_contains($b, $href), "$lbl → #18 hyperlink");
}

$r = app()->handle(Illuminate\Http\Request::create('/artikel/'.$slug, 'GET'));
$html = $r->getContent();
c($r->getStatusCode() === 200, 'HTTP 200 lokal');
c(str_contains($html, 'sensor_readings') && str_contains($html, 'parse_waktu'), 'Konten kunci ter-render');

$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
if (preg_match('/function publishArticle18\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m)) {
    $hookBody = $m[1];
    c(strpos($hookBody, 'Article18Seeder') < strpos($hookBody, 'Article34Seeder'), 'Hook: seed #18 sebelum re-seed backlink');
    c(strpos($hookBody, 'Article23Seeder') < strpos($hookBody, 'runDuplicateBme280Cleanup'), 'Hook: re-seed #23 sebelum cleanup');
    c(strpos($hookBody, 'Article24Seeder') < strpos($hookBody, 'runDuplicateBme280Cleanup'), 'Hook: re-seed #24 sebelum cleanup');
} else {
    c(false, 'publishArticle18() ditemukan di DeployController');
}

$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
c(strpos($yml, 'publish-article-34') < strpos($yml, 'publish-article-18'), 'CI: hook #34 sebelum #18');
c(preg_match('/Publish article 18 via deploy hook \(required\)/', $yml) === 1, 'CI: hook #18 required');

echo "\n=== RESULT: $passed passed, $failed failed ===\n";
exit($failed > 0 ? 1 : 0);
