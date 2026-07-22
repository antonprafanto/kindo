<?php

/**
 * Deep-audit pass-4 #50 — Capstone domain drift / a11y / ASCII arrow / residual.
 * Usage: php scripts/audit-article50-deep-pass4.php
 */

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article50Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article50Seeder::class);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');

echo "=== Deep-audit pass-4 #50 ===\n\n";

// Capstone domain honesty
check(str_contains($body, 'capstone-sistem-perpustakaan-mini-oop-python'), 'Link Capstone');
check(str_contains($body, 'tanpa alur stok') || str_contains($body, 'tanpa alur stok/pinjam') || str_contains($body, 'lebih ramping'), 'Klarifikasi model lebih ramping dari Capstone');
check(str_contains($body, 'titik masuk praktis') || str_contains($body, 'ganti pembuatan'), 'Retrofit Capstone tetap ada');
check(! str_contains($body, 'def pinjam'), 'Tidak campur alur pinjam Capstone di #50 (fokus pattern)');

// ASCII arrows (konvensi seri)
check(! preg_match('/→/u', $body), 'Tidak panah Unicode → di body');
check(str_contains($body, '-&gt;') || str_contains($body, '->'), 'Pakai ASCII -&gt; / ->');

// a11y figures
check(substr_count($body, 'aria-label') >= 2, '≥2 aria-label');
check(str_contains($body, 'role="img"'), 'SVG figure role=img');
check(str_contains($body, 'figcaption'), 'Figcaption ada');
check(str_contains($body, 'oop50Arrow'), 'Marker oop50');
check(substr_count($body, 'id="oop50Arrow"') === 1, 'Marker id unik 1x');

// Residual / framing
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#50(?!\s*\(ini\))/', $plain), 'Bare #50 hanya (ini)');
check(! preg_match('/#(?:4[0-9]|5[1-9])(?!\s*\(ini\))/', $plain), 'Tidak bare sibling #');
check(! str_contains($body, 'draft <strong>#50'), 'Bukan draft footer');
check(! str_contains($body, 'Factory di level Seri 3'), 'Bukan residual Seri 3 wording');
check(str_contains($body, '10/10'), 'Seri 3 10/10');
check(str_contains($body, 'Tier 2'), 'Tier 2 framing');
check(! str_contains($body, 'input('), 'Tidak input(');
check(! preg_match('/[┌┐└┘│─]/u', $body), 'Tanpa ASCII box');

// Sibling
check(str_contains($a49, 'design-pattern-factory-strategy-python'), '#49 hardlink #50');
check(! str_contains($a49, 'belum jadi artikel live'), '#49 tanpa residual belum live');

// Deploy package
check(str_contains($yml, 'publish-article-50'), 'CI #50');
check(preg_match('/Publish article 50 via deploy hook[\s\S]*?continue-on-error:\s*true/u', $yml) === 1, 'CI continue-on-error');
check(str_contains($src, "'is_featured'") && str_contains($src, 'false'), 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'cover tidak overwrite');

// Pedagogy still present
check(str_contains($body, 'buat_item') && str_contains($body, 'DendaFlat'), 'Factory+Strategy');
check(str_contains($body, 'lib.items'), 'items property');
check(str_contains($body, 'encapsulation-property-python-oop'), 'Link #43');
check(str_contains($body, 'factory_strategy_perpustakaan.py'), 'File contoh');
check(str_contains($body, 'color:#1a1a1a') && str_contains($body, 'background:#F5F5F0'), 'Dark-mode safe figures');

// Runnable
preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $blocks);
$tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a50_p4_'.uniqid();
mkdir($tmp);
$ok = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $f = $tmp.DIRECTORY_SEPARATOR.'b'.($i + 1).'.py';
    file_put_contents($f, $code);
    $out = [];
    $rc = 0;
    exec('python '.escapeshellarg($f).' 2>&1', $out, $rc);
    if ($rc === 0) {
        $ok++;
    }
}
check($ok === count($blocks[1]), "Runnable {$ok}/".count($blocks[1]));
foreach (glob($tmp.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmp);

echo "\n=== Deep pass-4 #50: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
