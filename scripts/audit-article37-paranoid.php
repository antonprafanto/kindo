<?php



/**

 * Paranoid audit #37.

 * Usage: php scripts/audit-article37-paranoid.php

 */



require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();



use App\Models\Article;

use Database\Seeders\Article37Seeder;

use Illuminate\Support\Facades\Artisan;



$passed = 0; $failed = 0; $warn = 0;

$slug = 'sd-card-spi-esp32-logging-data-sensor-offline';

$slug36 = 'esp8266-nodemcu-vs-esp32-kapan-pakai-upgrade';



function check(bool $ok, string $label, bool $warning = false): void {

    global $passed, $failed, $warn;

    if ($warning && ! $ok) { echo "⚠ {$label}\n"; $warn++; return; }

    echo ($ok ? '✓' : '✗') . " {$label}\n";

    $ok ? $passed++ : $failed++;

}



$ref = new ReflectionClass(Article37Seeder::class);

$m = $ref->getMethod('body'); $m->setAccessible(true);

$body = $m->invoke($ref->newInstanceWithoutConstructor());



echo "=== PARANOID AUDIT #37 ===\n\n";



foreach (['Article37Seeder', 'Article10Seeder', 'PatchArticle36SdCardSeeder', 'PatchArticle27SdCardSeeder', 'RemoveDuplicateBme280Seeder'] as $cls) {

    check(Artisan::call('db:seed', ['--class' => "Database\\Seeders\\{$cls}", '--force' => true]) === 0, "Seed {$cls} exit 0");

}



$a37 = Article::where('slug', $slug)->first();

check($a37 && $a37->status === 'published', 'Artikel #37 visible setelah full chain');

check($a37?->tags()->count() === 6, 'Tag count = 6');

check($a37?->read_time_minutes >= 8, 'read_time ≥ 8 menit');



$a10body = Article::where('slug', 'dashboard-esp32-web-server-mqtt-monitoring-dht22')->value('body') ?? '';

$indexItems = substr_count($a10body, '<li><strong><a href="/artikel/');

check($indexItems === 28, '#10 indeks 28 item (' . $indexItems . ')');

check(str_contains($a10body, 'dua puluh delapan artikel'), '#10 teks dua puluh delapan artikel');

check(str_contains($a10body, $slug), '#10 item #37');

check(str_contains($a10body, '#38') || str_contains($a10body, 'HTTPS'), '#10 teaser #38');

$a27body = Article::where('slug', 'esp32-cam-streaming-mjpeg-capture-foto-wifi')->value('body') ?? '';
check(str_contains($a27body, $slug), '#27 backlink → #37');
check(! str_contains($a27body, 'akan datang'), '#27 tidak ada teks akan datang #37');



foreach (['Article10Seeder.php', 'Article36Seeder.php'] as $file) {

    $src = file_get_contents(__DIR__ . '/../database/seeders/' . $file);

    check(! preg_match('/Artikel #37/', $src), "{$file}: tidak ada orphan 'Artikel #37'");

}



preg_match_all('/href="(\/artikel\/[^"]+)"/', $body, $links);

foreach (array_unique($links[1]) as $path) {

    $target = str_replace('/artikel/', '', $path);

    if ($target === '') continue;

    check($target !== $slug, "Bukan self-link: {$path}");

    check(Article::where('slug', $target)->where('status', 'published')->exists(), "Target published: {$path}");

}



check(str_contains($body, 'SPI.h'), 'Include SPI.h');

check(str_contains($body, '/sensor.csv'), 'File sensor.csv');

check(str_contains($body, 'Tier 2'), 'Menyebut Tier 2');

check(! str_contains($body, 'KindoMQTT'), 'Tidak ada password literal');



$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));

$html = $r->getContent();

check($r->getStatusCode() === 200, 'HTTP 200');

check(str_contains($html, 'SPI') || str_contains($html, 'SD Card'), 'Konten kunci ter-render');



$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');

preg_match('/function publishArticle37\(\)[^{]*\{([\s\S]*?)\n    \}/', $deploy, $m37);

$h37 = $m37[1] ?? '';

foreach (['opcache_reset', 'SitemapService', 'view:clear', 'runDuplicateBme280Cleanup', 'PatchArticle36SdCardSeeder', 'PatchArticle27SdCardSeeder'] as $needle) {

    check(str_contains($h37, $needle), "Hook #37 punya {$needle}");

}

check(strpos($deploy, 'function publishArticle36') < strpos($deploy, 'function publishArticle37'), 'DeployController: #36 sebelum #37');

check(preg_match('/Publish article 37 via deploy hook \(required\)/', file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml')) === 1, 'CI hook #37 required');



foreach (['audit-article37.php', 'audit-article36.php', 'audit-article10.php', 'audit-article37-manual.php', 'audit-article37-spotcheck.php'] as $script) {

    exec('php ' . escapeshellarg(__DIR__ . '/' . $script) . ' 2>&1', $out, $code);

    check($code === 0, "{$script} regresi OK");

}



$prod37 = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));

$preDeploy = $prod37 === '404';

check($preDeploy, "Production #37 belum live (HTTP {$prod37}) — expected pre-deploy", ! $preDeploy);



$prod10html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));

if ($preDeploy) {

    check(str_contains($prod10html, 'dua puluh enam artikel'), 'Production #10 dua puluh enam artikel (pre-#37)');

    check(! str_contains($prod10html, $slug), 'Production #10 belum link #37');

    $prod36html = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug36));

    check(! str_contains($prod36html, $slug), 'Production #36 belum hyperlink #37 (normal pre-deploy)');

}



$docsRoot = 'C:/Users/anton/vibecoding/kindo_cursorv2';

if (is_dir($docsRoot)) {

    $todo = file_get_contents($docsRoot . '/TODO.md');

    check(str_contains($todo, '#37') || str_contains($todo, $slug), 'TODO.md punya #37');

    check(str_contains(file_get_contents($docsRoot . '/PRD.md'), '#37') || str_contains(file_get_contents($docsRoot . '/PRD.md'), 'SD Card'), 'PRD.md #37');

}



echo "\n=== RESULT: {$passed} passed, {$failed} failed, {$warn} warnings ===\n";

exit($failed > 0 ? 1 : 0);

