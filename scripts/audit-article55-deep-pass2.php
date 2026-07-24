<?php

/**
 * Deep-audit pass-2 #55 — thin-anchor, bare #N, output=prose, hook/CI locks.
 * Usage: php scripts/audit-article55-deep-pass2.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article54Seeder;
use Database\Seeders\Article55Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-2 #55 ===\n\n";

$ref = new ReflectionClass(Article55Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());

$ref54 = new ReflectionClass(Article54Seeder::class);
$m54 = $ref54->getMethod('body');
$m54->setAccessible(true);
$body54 = $m54->invoke($ref54->newInstanceWithoutConstructor());

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check($thin === 0, 'Thin anchor = 0 ('.$thin.')');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#(?:5[6-9]|60)(?!\s*\(ini\))/', $plain), 'Tidak bare #56+ di prosa');
check(! preg_match('/(?<![\w\/"#>])#53(?!\s*\(ini\))/', $plain), 'Tidak bare #53 di prosa (harus dilink)');
check(! preg_match('/(?<![\w\/"#>])#54(?!\s*\(ini\))/', $plain), 'Tidak bare #54 di prosa (harus dilink)');
check(! preg_match('/#53\s*[–-]\s*#55/', $plain), 'Tidak rentang bare #53–#55');

check(str_contains($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php'), 'Body link #53');
check(str_contains($body, '/artikel/oop-php-property-method-constructor'), 'Body link #54');
check(substr_count($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php') >= 2, '≥2 link #53');
check(substr_count($body, '/artikel/oop-php-property-method-constructor') >= 3, '≥3 link #54');

check(str_contains($body54, 'oop-php-visibility-composition'), '#54 hardlink #55 LIVE');
check(str_contains($body54, '8/8 Capstone Laravel selesai'), '#54 Progress 5/8');
check(! str_contains($body54, '3/8 menuju Capstone Laravel'), '#54 tidak stale 3/8');

preg_match_all('/<pre><code class="language-php">(.*?)<\/code><\/pre>\s*<p>Output(?: yang diharapkan)?:<\/p>\s*<pre><code>(.*?)<\/code><\/pre>/s', $body, $pairs, PREG_SET_ORDER);
check(count($pairs) >= 3, '≥3 pasangan kode+output ('.count($pairs).')');

$tmpDir = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a55p2_'.uniqid();
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
check(str_contains($deploy, 'publishArticle55'), 'Hook ada');
check(str_contains($deploy, 'oop55phpArrow') && str_contains($deploy, '8/8 Capstone Laravel selesai'), 'Hook body locks');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php'), 'laravel-routing-json-perpustakaan-api'), '#55 hardlink #56');
check(str_contains($deploy, 'Article 55 backlink #54') || str_contains($deploy, 'backlink missing on #54'), 'Hook reseed/verifikasi #54');
check(preg_match('/Publish article 55 via deploy hook \(required\)/u', $yml) === 1, 'CI #55 required');
check(! preg_match('/Publish article 55 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml), 'CI #55 tidak continue-on-error');
check(! preg_match('/Publish article 55 via deploy hook \(continue-on-error until oke\)/u', $yml), 'CI #55 bukan continue-on-error');

check(str_contains($body, 'Masalah kalau semua public'), 'Narasi progresif public→private');
check(str_contains($body, 'setTahun(1800)') || str_contains($body, 'InvalidArgumentException'), 'Jelaskan gagal keras validasi');
check(str_contains($body, '@var') && str_contains($body, 'catatan untuk manusia'), 'Gloss PHPDoc @var awam');
check(str_contains($body, 'Mengenal OOP PHP (#53)') || str_contains($body, 'OOP PHP pengantar (#53)'), 'Anchor #53 berjudul penuh');
check(str_contains($body, 'Property, Method &amp; Constructor (#54)'), 'Anchor #54 berjudul penuh');
check(str_contains($body, 'declare(strict_types=1)'), 'strict_types di kode lengkap');
check(! str_contains($body, '→'), 'ASCII only (no unicode arrow)');
check(! str_contains($body, 'tanpa hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(str_contains($body, 'laravel-routing-json-perpustakaan-api'), 'Hardlink slug Laravel #56');
check(file_exists(__DIR__.'/audit-article55-deep.php'), 'Pass-1 ada');

echo "\n=== Deep-audit pass-2 #55: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: residual pass-1 dikunci · lanjut pass-3 / STOP AUDIT.\n";
}
exit($failed > 0 ? 1 : 0);
