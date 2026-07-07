<?php

/**
 * Manual supplemental audit #18 — backlink, deploy hook, konsistensi seri.
 * Usage: php scripts/audit-article18-manual.php
 */

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article18Seeder;
use Illuminate\Support\Facades\Artisan;

$passed = 0;
$failed = 0;
$warn   = 0;
$slug   = 'python-subscriber-mqtt-mysql-simpan-data-sensor-esp32';
$href   = '/artikel/' . $slug;

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

$ref = new ReflectionClass(Article18Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

echo "=== MANUAL SUPPLEMENTAL AUDIT #18 ===\n\n";

echo "--- Pass 1: Re-seed semua backlink ---\n\n";

$seeders = [
    'Article18Seeder', 'Article34Seeder', 'Article17Seeder', 'Article16Seeder',
    'Article10Seeder', 'Article7Seeder', 'Article13Seeder', 'Article14Seeder',
    'Article23Seeder', 'Article24Seeder',
];
foreach ($seeders as $class) {
    Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$class}", '--force' => true]);
}
Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\RemoveDuplicateBme280Seeder', '--force' => true]);

echo "--- Pass 2: Tidak ada teks orphan 'Artikel #18' ---\n\n";

$seederFiles = [
    'Article7Seeder.php', 'Article10Seeder.php', 'Article13Seeder.php', 'Article14Seeder.php',
    'Article16Seeder.php', 'Article17Seeder.php', 'Article23Seeder.php', 'Article24Seeder.php',
    'Article34Seeder.php',
];
foreach ($seederFiles as $file) {
    $content = file_get_contents(__DIR__ . '/../database/seeders/' . $file);
    check(! str_contains($content, 'Artikel #18'), "{$file}: tidak ada teks 'Artikel #18' orphan");
}

echo "\n--- Pass 3: Hyperlink di semua sumber backlink (9 artikel) ---\n\n";

$backlinkSlugs = [
    'memahami-mqtt-esp32-kirim-data-sensor-broker'                 => '#7',
    'dashboard-esp32-web-server-mqtt-monitoring-dht22'             => '#10',
    'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32' => '#16',
    'mqtt-tls-qos-lwt-retained-mosquitto-esp32'                  => '#17',
    'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt'             => '#34',
    'i2c-esp32-sensor-bme280-suhu-tekanan-mqtt'                    => '#13',
    'oled-ssd1306-esp32-tampilkan-data-sensor-i2c'                 => '#14',
    'node-red-dashboard-otomasi-iot-mqtt-esp32'                    => '#23',
    'sensor-gerak-pir-esp32-lampu-mqtt-debounce'                   => '#24',
];

foreach ($backlinkSlugs as $sourceSlug => $label) {
    $src = Article::where('slug', $sourceSlug)->first();
    $srcBody = $src?->body ?? '';
    check($src !== null, "Artikel {$label} ada di DB");
    check(str_contains($srcBody, $href), "{$label} punya href ke #18");
    check(
        substr_count($srcBody, $href) >= 1,
        "{$label} minimal 1 hyperlink (#18)"
    );
}

echo "\n--- Pass 4: #34 intro & unix → #18 ter-link ---\n\n";

$a34 = Article::where('slug', 'ntp-timestamp-esp32-waktu-akurat-log-sensor-mqtt')->first();
$b34 = $a34?->body ?? '';
check(str_contains($b34, $href), '#34 body mengandung slug #18');
check(
    preg_match('/wajib[\s\S]{0,200}' . preg_quote($href, '/') . '/', $b34) === 1,
    '#34 intro "wajib sebelum" ter-link ke #18'
);
check(str_contains($b34, 'Field <code>unix</code> akan dipakai di <strong><a href="' . $href . '">'), '#34 penjelasan unix ter-link');

echo "\n--- Pass 5: #16 tabel & intro ter-link ---\n\n";

$a16 = Article::where('slug', 'broker-mosquitto-pribadi-raspberry-pi-vps-autentikasi-esp32')->first();
$b16 = $a16?->body ?? '';
check(
    str_contains($b16, 'href="' . $href . '">Python subscriber (#18)</a>'),
    '#16 intro Jalur B ter-link ke #18'
);
check(
    str_contains($b16, '<strong>Python</strong> (<a href="' . $href . '">#18</a>)'),
    '#16 tabel roadmap ter-link ke #18'
);

echo "\n--- Pass 6: Deploy hook re-seed #23 & #24 ---\n\n";

$deploySource = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploySource, 'Article23Seeder'), 'publishArticle18 memanggil Article23Seeder');
check(str_contains($deploySource, 'Article24Seeder'), 'publishArticle18 memanggil Article24Seeder');

$workflow = file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml');
check(str_contains($workflow, 'publish-article-18'), 'deploy.yml punya hook #18');
check(
    preg_match('/Publish article 18 via deploy hook \(required\)[\s\S]*?test "\$code" = "200"/', $workflow) === 1,
    'deploy.yml hook #18 wajib + verifikasi HTTP 200'
);
check(
    ! preg_match('/Publish article 18[\s\S]*?continue-on-error:\s*true/', $workflow),
    'Hook #18 tidak pakai continue-on-error'
);

echo "\n--- Pass 7: Konsistensi timestamp & payload ---\n\n";

$isoExample = '2026-07-02T14:30:00';
$unixExample = 1782977400;
check(str_contains($body, $isoExample), 'Contoh ISO di #18');
check(str_contains($body, (string) $unixExample), 'Contoh unix di #18');
check(
    strtotime('2026-07-02 14:30:00 +0700') === $unixExample,
    'Pasangan ISO + unix konsisten dengan #34 (WIB)'
);

echo "\n--- Pass 8: #10 indeks hitung 13 artikel ---\n\n";

$a10 = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->first();
$b10 = $a10?->body ?? '';
check(str_contains($b10, 'tiga belas artikel'), '#10 menyebut tiga belas artikel');
$linkCount = substr_count($b10, $href);
check($linkCount >= 1, '#10 indeks punya link #18');
check(
    preg_match_all('/<li><strong><a href="\/artikel\/[^"]+">/', $b10, $m) >= 13,
    '#10 indeks minimal 13 item ber-link (' . count($m[0] ?? []) . ' item)'
);

echo "\n--- Pass 9: Keamanan deploy file list ---\n\n";

check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password brand di #18');
check(str_contains($body, 'chmod 600 .env'), 'Peringatan chmod .env');

echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";
exit($failed > 0 ? 1 : 0);
