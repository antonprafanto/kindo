<?php

/**
 * Compile-check blok language-python Article52Seeder + pedagogi.
 * Usage: php scripts/audit-article52-python.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article52Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article52Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $matches);
$blocks = $matches[1] ?? [];

check(count($blocks) >= 5, 'Minimal 5 blok language-python');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a52_'.uniqid();
mkdir($tmpDir);

foreach ($blocks as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $file = $tmpDir.DIRECTORY_SEPARATOR.'block_'.($i + 1).'.py';
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('python -m py_compile '.escapeshellarg($file).' 2>&1', $out, $rc);
    check($rc === 0, 'py_compile block #'.($i + 1).(empty($out) ? '' : ' — '.implode(' ', $out)));
}

// Progressive self-contained: tiap blok harus runnable sendiri (exit 0) + output inti
$expectedSnippets = [
    0 => 'service terpasang, jumlah= 0',
    1 => 'OOP Python',
    2 => 'Flask Ringkas',
    3 => 'jenis tidak dikenal: majalah',
    4 => 'judul wajib',
];
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

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#52(?!\s*\(ini\))/', $plain), 'Tidak ada plain #52 selain bentuk #52 (ini)');
check(str_contains($body, 'PerpustakaanService'), 'Ada PerpustakaanService');
check(str_contains($body, 'HttpResponse'), 'Ada HttpResponse');
check(str_contains($body, 'def demo('), 'Ada demo()');
check(! str_contains($body, 'input('), 'Tidak ada input()');
check(str_contains($body, 'perpustakaan_api_oop.py'), 'File contoh');
check(str_contains($body, 'Tier 2'), 'Framing Tier 2');
check(str_contains($body, '/artikel/oop-micropython-esp32-class-sensor'), 'Link #51');
check(str_contains($body, 'oop52Arrow'), 'SVG marker oop52');
check(str_contains($body, 'Flask') && str_contains($body, 'FastAPI'), 'Sebut Flask + FastAPI');

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
    $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a52_run_'.uniqid().'.py';
    $code = html_entity_decode($blocks[$fullIdx], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    file_put_contents($tmp, $code);
    $out = [];
    $rc = 0;
    exec('python '.escapeshellarg($tmp).' 2>&1', $out, $rc);
    @unlink($tmp);
    $joined = implode("\n", $out);
    check($rc === 0, 'demo() runnable exit 0'.($rc === 0 ? '' : ' — '.$joined));
    check(str_contains($joined, '201') && str_contains($joined, 'OOP Python'), 'demo output create 201');
    check(str_contains($joined, '400') && str_contains($joined, 'judul wajib'), 'demo output 400 judul wajib');
    check(str_contains($joined, "'items'") || str_contains($joined, 'items'), 'demo output list items');
}

echo "\n=== Python/pedagogi audit #52: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
