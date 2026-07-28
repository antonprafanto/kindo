<?php

/**
 * PHP/pedagogi audit #66 — runnable policy demo (skip cuplikan Laravel).
 * Usage: php scripts/audit-article66-php.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article66Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article66Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $blocks);
check(count($blocks[1]) >= 4, 'Minimal 4 blok language-php ('.count($blocks[1]).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a66php_'.uniqid();
mkdir($tmpDir);

$runnable = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (
        str_contains($code, 'Cuplikan Laravel')
        || str_contains($code, 'use App\\Models\\')
        || str_contains($code, 'authorize(')
        || str_contains($code, 'PeminjamanPolicy')
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
    if (str_contains($code, 'function demo')) {
        check(str_contains($joined, '403') || str_contains($joined, 'Tidak punya izin'), 'run demo 403');
        check(str_contains($joined, '200') || str_contains($joined, '"ok"'), 'run demo 200');
        check(str_contains($joined, '404') || str_contains($joined, 'tidak ketemu'), 'run demo 404');
    } elseif (str_contains($code, 'function ubahStatusPinjam')) {
        check(
            str_contains($joined, '403') || str_contains($joined, '200') || str_contains($joined, '404')
            || str_contains($joined, 'Tidak punya izin') || str_contains($joined, '"ok"'),
            'run izin-cek respons'
        );
    }
}

check($runnable >= 2, '≥2 blok runnable PHP ('.$runnable.')');
check(str_contains($body, 'demo('), 'Ada demo()');
check(str_contains($body, 'laravel_policy_otorisasi_api_demo.php'), 'File contoh');
check(str_contains($body, 'laravel66policyArrow'), 'SVG marker');
check(str_contains($body, 'Seri 5'), 'Framing Seri 5');
check(str_contains($body, '#66 (ini)'), 'Self-ref');

echo "\n=== PHP/pedagogi audit #66: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
