<?php

/**
 * Deep-audit pass-2 #53 — output=prose · slug resolve · thin-anchor · hook residual.
 * Usage: php scripts/audit-article53-deep-pass2.php
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
$deploy = file_get_contents(__DIR__.'/../app/Http\Controllers/DeployController.php');
$a52 = file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php');
$py = file_get_contents(__DIR__.'/audit-article53-python.php');

echo "=== Deep-audit pass-2 #53 ===\n\n";

$slugs = [
    'oop-flask-fastapi-class-api' => '#52',
    'capstone-sistem-perpustakaan-mini-oop-python' => '#49',
    'composition-vs-inheritance-python' => '#47',
    'mengenal-oop-cara-berpikir-dengan-objek-python' => '#40',
];
foreach ($slugs as $slug => $label) {
    check(str_contains($body, '/artikel/'.$slug), "Body link {$label}");
    // Resolve via sibling seeder source (local sqlite may be empty)
    $seederGuess = match ($label) {
        '#52' => 'Article52Seeder.php',
        '#49' => 'Article49Seeder.php',
        '#47' => 'Article47Seeder.php',
        '#40' => 'Article40Seeder.php',
        default => null,
    };
    if ($seederGuess) {
        $path = __DIR__.'/../database/seeders/'.$seederGuess;
        check(is_file($path) && str_contains((string) file_get_contents($path), $slug), "Slug resolve seeder {$label}");
    }
}

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = [];
foreach ($anchors[1] as $text) {
    $t = trim(html_entity_decode($text));
    if (preg_match('/^#\d+$/', $t)) {
        $thin[] = $t;
    }
}
check(count($thin) === 0, 'Thin anchor #53 body = 0');

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $a52, $a52Anchors);
$thin52 = [];
foreach ($a52Anchors[1] as $text) {
    $t = trim(html_entity_decode($text));
    if (preg_match('/^#\d+$/', $t)) {
        $thin52[] = $t;
    }
}
check(count($thin52) === 0, 'Thin anchor di #52 seeder (backlink package) = 0 ('.implode(',', $thin52).')');

// Prose output blocks: extract labeled Output sections after python and compare to run of preceding block loosely
preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>\s*<p>Output(?: yang diharapkan)?:<\/p>\s*<pre><code>(.*?)<\/code><\/pre>/s', $body, $pairs, PREG_SET_ORDER);
check(count($pairs) >= 5, '≥5 pasangan kode+output prosa ('.count($pairs).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a53_p2_'.uniqid();
mkdir($tmpDir);
$i = 0;
foreach ($pairs as $pair) {
    $i++;
    $code = html_entity_decode($pair[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $expected = trim(html_entity_decode($pair[2], ENT_QUOTES | ENT_HTML5, 'UTF-8'));
    $file = $tmpDir.DIRECTORY_SEPARATOR."p{$i}.py";
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('python '.escapeshellarg($file).' 2>&1', $out, $rc);
    $joined = trim(implode("\n", $out));
    check($rc === 0, "Pass2 run pair #{$i} exit 0");
    // Normalize Windows vs unix newlines; Python dict may use single quotes already
    $normExp = preg_replace("/\r\n?/", "\n", $expected) ?? '';
    $normOut = preg_replace("/\r\n?/", "\n", $joined) ?? '';
    check($normOut === $normExp, "Pass2 output prosa = run pair #{$i}");
}
foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

check(str_contains($deploy, 'PerpustakaanService'), 'Hook cek PerpustakaanService');
check(str_contains($deploy, 'Method Not Allowed'), 'Hook cek Method Not Allowed');
check(str_contains($py, 'Method Not Allowed') || str_contains($py, 'OK baca'), 'Python audit masih lock snippets');
check(file_exists(__DIR__.'/audit-article53-deep.php'), 'Pass-1 deep ada');
check(str_contains($body, 'buatBuku') || str_contains($body, '/api/buku'), 'Narasi resource naming');
check(str_contains($body, 'if code == 405'), '405 di helper progresif');

echo "\n=== Deep-audit pass-2 #53: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
