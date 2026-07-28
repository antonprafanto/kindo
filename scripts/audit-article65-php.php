<?php

/**
 * PHP/pedagogi audit #65 — runnable pagination demo (skip cuplikan Laravel).
 * Usage: php scripts/audit-article65-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article65Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article65Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $blocks);
check(count($blocks[1]) >= 4, 'Minimal 4 blok language-php ('.count($blocks[1]).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a65php_'.uniqid();
mkdir($tmpDir);

$runnable = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (
        str_contains($code, 'Cuplikan Laravel')
        || str_contains($code, 'use App\\Models\\')
        || str_contains($code, 'Illuminate\\Http\\Request')
        || str_contains($code, 'paginate(')
        || str_contains($code, 'whereHas')
        || str_contains($code, 'current_page')
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
    if (str_contains($code, 'function demo') || str_contains($code, 'function daftarPinjam')) {
        check(str_contains($joined, '422') || str_contains($joined, 'Isian halaman'), 'run demo halaman kotor / 422');
        check(str_contains($joined, '200') || str_contains($joined, '"ok"'), 'run demo 200');
        check(str_contains($joined, 'Dasar PHP') || str_contains($joined, 'judul_buku'), 'run demo data pinjam');
    }
}

check($runnable >= 2, '≥2 blok runnable PHP ('.$runnable.')');
check(str_contains($body, 'demo('), 'Ada demo()');
check(str_contains($body, 'laravel_pagination_filter_pencarian_demo.php'), 'File contoh');
check(str_contains($body, 'laravel65pageArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Framing Seri 5');
check(str_contains($body, '#65 (ini)'), 'Self-ref');

echo "\n=== PHP/pedagogi audit #65: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
