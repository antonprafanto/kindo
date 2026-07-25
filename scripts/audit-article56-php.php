<?php

/**
 * PHP/pedagogi audit #56 — runnable demo cek versi (simulasi string).
 * Usage: php scripts/audit-article56-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article56Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $blocks);
check(count($blocks[1]) >= 4, 'Minimal 4 blok language-php ('.count($blocks[1]).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a56php_'.uniqid();
mkdir($tmpDir);

$runnable = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (
        str_contains($code, 'Simulasi output')
        && ! str_contains($code, 'function demo')
    ) {
        $runnable++;
        $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.php';
        file_put_contents($file, $code);
        $lint = [];
        $lrc = 0;
        exec('php -l '.escapeshellarg($file).' 2>&1', $lint, $lrc);
        check($lrc === 0, 'php -l block #'.($i + 1).' — '.trim(implode(' ', $lint)));
        $out = [];
        $rc = 0;
        exec('php '.escapeshellarg($file).' 2>&1', $out, $rc);
        $joined = implode("\n", $out);
        check($rc === 0, 'run block #'.($i + 1).' exit 0');
        if (str_contains($code, 'PHP 8.')) {
            check(str_contains($joined, 'PHP 8.'), 'run block #'.($i + 1).' output: PHP version');
        }
        if (str_contains($code, 'Composer version')) {
            check(str_contains($joined, 'Composer version'), 'run block #'.($i + 1).' output: Composer');
        }
        if (str_contains($code, 'Laravel Framework')) {
            check(str_contains($joined, 'Laravel Framework'), 'run block #'.($i + 1).' output: Laravel');
        }
        continue;
    }
    if (str_contains($code, 'function demo')) {
        $runnable++;
        $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.php';
        file_put_contents($file, $code);
        $lint = [];
        $lrc = 0;
        exec('php -l '.escapeshellarg($file).' 2>&1', $lint, $lrc);
        check($lrc === 0, 'php -l demo block — '.trim(implode(' ', $lint)));
        $out = [];
        $rc = 0;
        exec('php '.escapeshellarg($file).' 2>&1', $out, $rc);
        $joined = implode("\n", $out);
        check($rc === 0, 'run demo block exit 0');
        check(str_contains($joined, 'php -v') || str_contains($joined, 'PHP 8.'), 'run demo output: PHP');
        check(str_contains($joined, 'composer -V') || str_contains($joined, 'Composer version'), 'run demo output: Composer');
        check(str_contains($joined, 'artisan --version') || str_contains($joined, 'Laravel Framework'), 'run demo output: Artisan');
        check(str_contains($joined, 'artisan serve') || str_contains($joined, '127.0.0.1:8000'), 'run demo output: serve');
        continue;
    }
    check(true, 'skip non-runnable block #'.($i + 1));
}

check($runnable >= 4, '≥4 blok runnable PHP ('.$runnable.')');
check(str_contains($body, 'demo('), 'Ada demo()');
check(str_contains($body, 'laravel_instalasi_proyek_pertama_demo.php'), 'File contoh');
check(str_contains($body, 'laravel56installArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');
check(str_contains($body, '#56 (ini)'), 'Self-ref');

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

echo "\n=== PHP/pedagogi audit #56: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
