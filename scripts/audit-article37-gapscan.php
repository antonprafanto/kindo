<?php



/** Gap-scan #37. */



require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../bootstrap/app.php';

$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();



use App\Models\Article;

use Database\Seeders\Article37Seeder;



$passed = 0; $failed = 0;

$slug = 'sd-card-spi-esp32-logging-data-sensor-offline';



function check(bool $ok, string $label): void {

    global $passed, $failed;

    echo ($ok ? '✓' : '✗') . " {$label}\n";

    $ok ? $passed++ : $failed++;

}



$ref = new ReflectionClass(Article37Seeder::class);

$m = $ref->getMethod('body'); $m->setAccessible(true);

$body = $m->invoke($ref->newInstanceWithoutConstructor());

$article = Article::where('slug', $slug)->first();



echo "=== GAP-SCAN #37 ===\n\n";

check($article !== null, 'Artikel ada di DB');

check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');

check(str_contains($body, 'appendLog'), 'Fungsi appendLog ada');

check(str_contains($body, 'FAT32'), 'Sebut FAT32');

check($article?->cover_image === null || $article?->cover_image === '', 'Cover belum di-set');

foreach ([

    'database/seeders/Article37Seeder.php',

    'database/seeders/PatchArticle36SdCardSeeder.php',
    'database/seeders/PatchArticle27SdCardSeeder.php',

    'scripts/audit-article37.php',

    'scripts/audit-article37-paranoid.php',

] as $f) {

    check(file_exists(__DIR__ . '/../' . $f), "File ada: {$f}");

}

$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');

check(str_contains($deploy, 'publishArticle37'), 'DeployController publishArticle37');

$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));

$html = $r->getContent();

check(str_contains($html, 'og:title') || str_contains($html, 'property="og:title"'), 'OG title ada');

$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';

if (is_dir($docs)) {

    check(str_contains(file_get_contents($docs . '/TODO.md'), $slug) || str_contains(file_get_contents($docs . '/TODO.md'), '#37'), 'TODO.md #37');

    check(str_contains(file_get_contents($docs . '/PRD.md'), 'SD Card') || str_contains(file_get_contents($docs . '/PRD.md'), '#37'), 'PRD.md #37');

}

$code = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));

check($code === '404', "Production pre-deploy HTTP 404 (got {$code})");

$prod10 = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));

check(str_contains($prod10, 'dua puluh enam artikel'), 'Production #10 masih 26 artikel (pre-#37)');

check(! str_contains($prod10, $slug), 'Production #10 belum link #37');

echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";

exit($failed > 0 ? 1 : 0);

