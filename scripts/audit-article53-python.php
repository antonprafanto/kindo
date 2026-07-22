<?php

/**
 * Compile-check + pedagogi #53.
 * Usage: php scripts/audit-article53-python.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article53Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article53Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 5, 'Minimal 5 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a53_'.uniqid();
mkdir($tmpDir);

$expectedSnippets = [
    0 => '201',
    1 => 'OK baca',
    2 => 'REST Ringkas',
    3 => '405',
    4 => 'jumlah tetap',
    5 => 'method tidak diizinkan',
];

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.py';
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('python -m py_compile '.escapeshellarg($file).' 2>&1', $out, $rc);
    check($rc === 0, 'py_compile block #'.($i + 1).(empty($out) ? '' : ' — '.implode(' ', $out)));
}

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = $tmpDir.DIRECTORY_SEPARATOR.'run_'.($i + 1).'.py';
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('python '.escapeshellarg($file).' 2>&1', $out, $rc);
    $joined = implode("\n", $out);
    check($rc === 0, 'run block #'.($i + 1).' exit 0'.($rc === 0 ? '' : ' — '.$joined));
    if (isset($expectedSnippets[$i])) {
        check(str_contains($joined, $expectedSnippets[$i]), 'run block #'.($i + 1).' output: '.$expectedSnippets[$i]);
    }
}

foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

check(str_contains($body, 'def demo('), 'Ada demo()');
check(str_contains($body, 'def dispatch('), 'Ada dispatch()');
check(str_contains($body, 'HttpRequest'), 'Ada HttpRequest');
check(! str_contains($body, 'input('), 'Tidak ada input()');
check(str_contains($body, 'http_rest_kontrak.py'), 'File contoh');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Link #52');
check(str_contains($body, 'oop53Arrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Framing Seri 4');

$fullIdx = null;
foreach ($blocks as $i => $raw) {
    $decoded = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (str_contains($decoded, 'def demo(') && str_contains($decoded, 'if __name__')) {
        $fullIdx = $i;
        break;
    }
}
check($fullIdx !== null, 'Ada blok kode lengkap dengan demo()');
if ($fullIdx !== null) {
    $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a53_run_'.uniqid().'.py';
    $code = html_entity_decode($blocks[$fullIdx], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    file_put_contents($tmp, $code);
    $out = [];
    $rc = 0;
    exec('python '.escapeshellarg($tmp).' 2>&1', $out, $rc);
    @unlink($tmp);
    $joined = implode("\n", $out);
    check($rc === 0, 'demo() runnable exit 0'.($rc === 0 ? '' : ' — '.$joined));
    check(str_contains($joined, '201') && str_contains($joined, 'REST'), 'demo output create 201');
    check(str_contains($joined, '400') && str_contains($joined, 'judul wajib'), 'demo output 400');
    check(str_contains($joined, '405'), 'demo output 405');
}

echo "\n=== Python/pedagogi audit #53: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
