<?php

/**
 * Deep-audit pass-2 #54 — thin-anchor, output=prose, hook/CI locks.
 * Usage: php scripts/audit-article54-deep-pass2.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article53Seeder;
use Database\Seeders\Article54Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-2 #54 ===\n\n";

$ref = new ReflectionClass(Article54Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

$ref53 = new ReflectionClass(Article53Seeder::class);
$m53 = $ref53->getMethod('body');
$m53->setAccessible(true);
$body53 = $m53->invoke($ref53->newInstanceWithoutConstructor());

// Thin anchors
preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check($thin === 0, 'Thin anchor = 0 ('.$thin.')');

// Bare future / sibling nums outside links & pre
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[5-9]|60)(?!\s*\(ini\))/', $plain), 'Tidak bare #55+ di prosa');
check(! preg_match('/(?<![\w\/"#>])#53(?!\s*\(ini\))/', $plain), 'Tidak bare #53 di prosa (harus dilink)');

check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php'), 'Body link #53');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article53Seeder.php'), 'mengenal-oop-cara-berpikir-dengan-objek-php'), 'Slug resolve seeder #53');
check(! str_contains($body53, '1/8 menuju Capstone Laravel'), '#53 tidak stale 1/8');
check(str_contains($body53, '7/8 menuju Capstone Laravel'), '#53 Progress 5/8 setelah oke');
check(! str_contains($body53, '3/8 menuju Capstone Laravel'), '#53 tidak stale 3/8');
check(str_contains($body53, '/artikel/oop-php-property-method-constructor'), '#53 hardlink #54 LIVE');

// Output = prose pairs
preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>\s*<p>Output(?: yang diharapkan)?:<\/p>\s*<pre><code>(.*?)<\/code><\/pre>/s', $body, $pairs, PREG_SET_ORDER);
check(count($pairs) >= 3, '≥3 pasangan kode+output ('.count($pairs).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a54p2_'.uniqid();
mkdir($tmpDir);
foreach ($pairs as $i => $pair) {
    $code = html_entity_decode($pair[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $expected = trim(str_replace("\r\n", "\n", html_entity_decode($pair[2], ENT_QUOTES | ENT_HTML5, 'UTF-8')));
    $file = $tmpDir.DIRECTORY_SEPARATOR.'p'.($i + 1).'.php';
    file_put_contents($file, $code);
    $out = [];
    $rc = 0;
    exec('php '.escapeshellarg($file).' 2>&1', $out, $rc);
    $joined = trim(str_replace("\r\n", "\n", implode("\n", $out)));
    check($rc === 0, 'Pass2 run pair #'.($i + 1).' exit 0');
    $linesOk = true;
    foreach (preg_split('/\n/', $expected) ?: [] as $line) {
        $line = trim($line);
        if ($line !== '' && ! str_contains($joined, $line)) {
            $linesOk = false;
            break;
        }
    }
    check($linesOk && $joined !== '', 'Pass2 output prosa = run pair #'.($i + 1));
}
foreach (glob($tmpDir.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmpDir);

$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
check(str_contains($deploy, 'publishArticle54'), 'Hook ada');
check(str_contains($deploy, 'oop54phpArrow') && str_contains($deploy, '7/8 menuju Capstone Laravel'), 'Hook body locks');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article54Seeder.php'), 'oop-php-visibility-composition'), '#54 hardlink #55');
check(preg_match('/Publish article 54 via deploy hook \(required\)/u', $yml) === 1, 'CI #54 required');
check(! preg_match('/Publish article 54 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #54 tidak continue-on-error');
check(str_contains($body, 'Kenapa contoh pertama belum pakai constructor'), 'Narasi progresif property→constructor');
check(str_contains($body, 'Mengenal OOP PHP (#53)') || str_contains($body, 'OOP PHP pengantar (#53)'), 'Anchor #53 berjudul penuh');
check(file_exists(__DIR__.'/audit-article54-deep.php'), 'Pass-1 ada');
check(str_contains($body, 'declare(strict_types=1)'), 'strict_types di kode lengkap');
check(! str_contains($body, '→'), 'ASCII only (no unicode arrow)');
check(! str_contains($body, 'tanpa hardlink'), 'Tanpa suara editor');

echo "\n=== Deep-audit pass-2 #54: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: residual pass-1 dikunci · siap STOP AUDIT setelah reconfirm / oke deploy.\n";
}
exit($failed > 0 ? 1 : 0);
