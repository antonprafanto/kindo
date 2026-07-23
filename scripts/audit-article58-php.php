<?php

/**
 * PHP/pedagogi audit #58 — runnable controller/service demo (skip cuplikan Laravel).
 * Usage: php scripts/audit-article58-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article58Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article58Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $blocks);
check(count($blocks[1]) >= 4, 'Minimal 4 blok language-php ('.count($blocks[1]).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a58php_'.uniqid();
mkdir($tmpDir);

$runnable = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (
        str_contains($code, 'Cuplikan Laravel')
        || str_contains($code, 'namespace App\\Http\\Controllers')
        || str_contains($code, 'namespace App\\Models')
        || str_contains($code, 'namespace App\\Services')
        || str_contains($code, 'extends Model')
        || str_contains($code, 'BukuController')
    ) {
        check(true, 'skip Laravel cuplikan block #'.($i + 1));
        continue;
    }
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
    if ($runnable === 1) {
        check(str_contains($joined, 'Belajar PHP') || str_contains($joined, '"ok"'), 'run block #'.($i + 1).' output: tambah buku');
    }
    if ($runnable === 2) {
        check(str_contains($joined, 'data') || str_contains($joined, 'Belajar PHP'), 'run block #'.($i + 1).' output: daftar');
    }
    if (str_contains($code, 'function demo')) {
        check(str_contains($joined, 'POST bersih') || str_contains($joined, 'GET daftar'), 'run demo output framing');
        check(str_contains($joined, 'Belajar PHP') && str_contains($joined, '"ok"'), 'run demo has sukses + daftar');
    }
}

check($runnable >= 3, '≥3 blok runnable PHP ('.$runnable.')');
check(str_contains($body, 'demo('), 'Ada demo()');
check(str_contains($body, 'laravel_controller_service_demo.php'), 'File contoh');
check(str_contains($body, 'BukuService'), 'Ada BukuService');
check(str_contains($body, 'laravel58ctrlArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');
check(str_contains($body, '#58 (ini)'), 'Self-ref');

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

echo "\n=== PHP/pedagogi audit #58: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
