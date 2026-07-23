<?php

/**
 * Deep-audit pass-2 #56 — thin-anchor, bare #N, output=prose, hook/CI, awam.
 * Usage: php scripts/audit-article56-deep-pass2.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article55Seeder;
use Database\Seeders\Article56Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-2 #56 ===\n\n";

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

$ref55 = new ReflectionClass(Article55Seeder::class);
$m55 = $ref55->getMethod('body');
$m55->setAccessible(true);
$body55 = $m55->invoke($ref55->newInstanceWithoutConstructor());

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check($thin === 0, 'Thin anchor = 0 ('.$thin.')');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[7-9]|60)(?!\d)(?!\s*\(ini\))/', $plain), 'Tidak bare #57+ di prosa');
check(! preg_match('/(?<![\w\/"#>])#55(?!\d)(?!\s*\(ini\))/', $plain), 'Tidak bare #55 di prosa (harus dilink)');
check(! preg_match('/(?<![\w\/"#>])#56(?!\d)(?!\s*\(ini\))/', $plain), 'Tidak bare #56 tanpa (ini)');

check(str_contains($body, '/artikel/oop-php-visibility-composition'), 'Body link #55');
check(substr_count($body, '/artikel/oop-php-visibility-composition') >= 2, '≥2 link #55');
check(str_contains($body, 'Visibility &amp; Composition (#55)'), 'Anchor #55 berjudul penuh');

check(str_contains($body55, 'laravel-routing-json-perpustakaan-api'), '#55 hardlink #56 LIVE');
check(str_contains($body55, '4/8 menuju Capstone Laravel'), '#55 progress 4/8');
check(! str_contains($body55, '3/8 menuju Capstone Laravel'), '#55 tidak stale 3/8');

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>/s', $body, $blocks, PREG_OFFSET_CAPTURE);
$pairs = [];
foreach ($blocks[0] as $i => $full) {
    $after = substr($body, $full[1] + strlen($full[0]), 800);
    if (! preg_match('/^\s*<p>Output(?: yang diharapkan)?:<\/p>\s*<pre><code>(.*?)<\/code><\/pre>/s', $after, $outMatch)) {
        continue;
    }
    $codeRaw = $blocks[1][$i][0];
    if (str_contains(html_entity_decode($codeRaw, ENT_QUOTES | ENT_HTML5, 'UTF-8'), 'Route::')) {
        continue; // cuplikan Laravel tidak punya output berurutan yang dijalankan
    }
    $pairs[] = [$codeRaw, $outMatch[1]];
}
check(count($pairs) >= 3, '≥3 pasangan kode+output runnable ('.count($pairs).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a56p2_'.uniqid();
mkdir($tmpDir);
foreach ($pairs as $i => $pair) {
    $code = html_entity_decode($pair[0], ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $expected = trim(str_replace("\r\n", "\n", html_entity_decode($pair[1], ENT_QUOTES | ENT_HTML5, 'UTF-8')));
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
check(str_contains($deploy, 'publishArticle56'), 'Hook ada');
check(str_contains($deploy, 'laravel56jsonArrow') && str_contains($deploy, '4/8 menuju Capstone Laravel'), 'Hook body locks');
check(str_contains($deploy, 'Article 56 backlink #55') || str_contains($deploy, 'backlink missing on #55'), 'Hook reseed/verifikasi #55');
check(preg_match('/Publish article 56 via deploy hook \(required\)/u', $yml) === 1, 'CI #56 required');
check(! preg_match('/Publish article 56 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #56 tidak continue-on-error');
check(! preg_match('/Publish article 56 via deploy hook \(continue-on-error until oke\)/u', $yml), 'CI #56 bukan continue-on-error');

check(str_contains($body, 'Kenapa belum langsung buka Laravel'), 'Narasi progresif PHP dulu');
check(str_contains($body, 'bayangkan loket') || str_contains($body, 'loket perpustakaan'), 'Analogi loket awam');
check(str_contains($body, '>GET</td>') || str_contains($body, '<td>GET</td>'), 'Gloss GET di tabel');
check(str_contains($body, 'mixed $data') && str_contains($body, 'bermacam bentuk'), 'Gloss mixed awam');
check(str_contains($body, 'menulis pintu JSON'), 'H2 Laravel ramah awam');
check(str_contains($body, 'declare(strict_types=1)'), 'strict_types di kode lengkap');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel 11+');
check(! str_contains($body, '→'), 'ASCII only (no unicode arrow)');
check(! str_contains($body, 'tanpa hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(! str_contains($body, 'laravel-request-validasi'), 'Tanpa hardlink slug #57');
check(file_exists(__DIR__.'/audit-article56-deep.php'), 'Pass-1 ada');

echo "\n=== Deep-audit pass-2 #56: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: residual pass-1 dikunci · lanjut pass-3 / STOP AUDIT.\n";
}
exit($failed > 0 ? 1 : 0);
