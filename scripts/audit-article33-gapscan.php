<?php

/** Gap-scan #33. */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Database\Seeders\Article33Seeder;

$passed = 0; $failed = 0;
$slug = 'kontrol-servo-pwm-esp32-mqtt-gerakan-presisi';

function check(bool $ok, string $label): void {
    global $passed, $failed;
    echo ($ok ? '✓' : '✗') . " {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article33Seeder::class);
$m = $ref->getMethod('body'); $m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$article = Article::where('slug', $slug)->first();

echo "=== GAP-SCAN #33 ===\n\n";
check($article !== null, 'Artikel ada di DB');
check(mb_strlen($article?->seo_title ?? '') <= 70, 'seo_title ≤ 70 char');
check(str_contains($body, 'constrain'), 'Validasi constrain sudut');
check($article?->cover_image === null || $article?->cover_image === '', 'Cover belum di-set');
foreach ([
    'database/seeders/Article33Seeder.php',
    'database/seeders/PatchArticle8ServoSeeder.php',
    'scripts/audit-article33.php',
    'scripts/audit-article33-paranoid.php',
] as $f) {
    check(file_exists(__DIR__ . '/../' . $f), "File ada: {$f}");
}
$deploy = file_get_contents(__DIR__ . '/../app/Http/Controllers/DeployController.php');
check(str_contains($deploy, 'publishArticle33'), 'DeployController publishArticle33');
$r = app()->handle(Illuminate\Http\Request::create('/artikel/' . $slug, 'GET'));
$html = $r->getContent();
check(str_contains($html, 'og:title') || str_contains($html, 'property="og:title"'), 'OG title ada');
$docs = 'C:/Users/anton/vibecoding/kindo_cursorv2';
if (is_dir($docs)) {
    check(str_contains(file_get_contents($docs . '/TODO.md'), $slug), 'TODO.md #33');
    check(str_contains(file_get_contents($docs . '/PRD.md'), 'Servo') || str_contains(file_get_contents($docs . '/PRD.md'), '#33'), 'PRD.md #33');
}
$code = trim((string) shell_exec('curl -sS --max-time 20 -o NUL -w "%{http_code}" ' . escapeshellarg('https://kodingindonesia.com/artikel/' . $slug)));
check($code === '404', "Production pre-deploy HTTP 404 (got {$code})");
$prod10 = (string) shell_exec('curl -sS --max-time 20 ' . escapeshellarg('https://kodingindonesia.com/artikel/dashboard-esp32-web-server-mqtt-monitoring-dht22'));
check(str_contains($prod10, 'dua puluh tiga artikel'), 'Production #10 masih 23 artikel (pre-#33)');
check(! str_contains($prod10, $slug), 'Production #10 belum link #33');
echo "\n=== RESULT: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
