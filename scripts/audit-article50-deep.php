<?php

/**
 * Deep-audit pass #50 — residual pedagogi / sibling / framing.
 * Usage: php scripts/audit-article50-deep.php
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
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');

echo "=== Deep-audit #50 ===\n\n";

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#50(?!\s*\(ini\))/', $plain), 'Bare #50 hanya bentuk (ini)');
check(! preg_match('/#(?:4[0-9]|5[2-9])(?!\s*\(ini\))/', $plain), 'Tidak bare #40–#49/#52+ di prosa');
check(! preg_match('/[┌┐└┘│─╔╗╚╝║═]/u', $body), 'Tanpa ASCII box');
check(str_contains($body, 'langkah <strong>#50 (ini)</strong>'), 'Footer langkah #50 (ini)');
check(! str_contains($body, 'draft <strong>#50'), 'Tidak residual draft #50 di footer');
check(! str_contains($body, 'Factory di level Seri 3'), 'Tidak residual level Seri 3');
check(str_contains($body, 'Factory di level Tier 2'), 'Framing Factory Tier 2');
check(str_contains($body, '@property') || str_contains($body, 'def items'), 'Ada property items');
check(str_contains($body, 'for item in lib.items'), 'Demo iterasi lib.items');
check(! preg_match('/for item in lib\._items/', $body), 'Tidak for-loop lib._items');
check(str_contains($body, 'Mutasi <code>lib._items</code>') || str_contains($body, 'lib._items'), 'Kesalahan umum bahas lib._items');
check(str_contains($body, 'encapsulation-property-python-oop'), 'Jembatan #43');
check(str_contains($body, 'error: jenis tidak dikenal'), 'Output ValueError majalah');
check(str_contains($body, 'buat_item("majalah"') || str_contains($body, "buat_item(\"majalah\""), 'Demo jenis majalah');
check(substr_count($body, 'lower().strip()') >= 2, 'Normalisasi jenis di ≥2 factory');
check(str_contains($a49, 'design-pattern-factory-strategy-python'), 'Sibling #49 hardlink #50');
check(! str_contains($a49, 'belum jadi artikel live'), '#49 tanpa residual “belum jadi artikel live”');
check(str_contains($deploy, 'lib.items') && str_contains($deploy, 'encapsulation-property-python-oop'), 'Hook cek items + #43');
check(str_contains($deploy, 'Article 50 backlink #49 incomplete'), 'Hook verifikasi backlink #49');
check(str_contains($src, "'composition'") && str_contains($src, "'design-pattern'"), 'Seeder sync tag composition');
check(str_contains($body, '10/10'), 'Sebut Seri 3 10/10');
check(str_contains($body, 'MicroPython') || str_contains($body, 'oop-micropython-esp32-class-sensor'), 'Teaser/hardlink #51');
check(str_contains($body, '/artikel/oop-micropython-esp32-class-sensor'), 'Hardlink MicroPython #51');
check(! preg_match('/\/artikel\/[a-z0-9-]*flask/', $body), 'Tidak hardlink Flask #52');
check(substr_count($body, 'language-python') >= 5, '≥5 blok python');
check(str_contains($body, 'oop50Arrow'), 'Marker oop50');
check(str_contains($body, 'background:#F5F5F0'), 'Figure bg');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar aman dark mode');
check(file_exists(__DIR__.'/audit-article50.php'), 'Suite audit utama ada');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'continue-on-error: true')
    && str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'publish-article-50'), 'CI #50 continue-on-error');

// Executable output spot-check (middle-dot encoding-safe)
preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $blocks);
$tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.'kindo_a50_deep_'.uniqid();
mkdir($tmp);
$runOk = 0;
foreach ($blocks[1] as $i => $raw) {
    $code = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $f = $tmp.DIRECTORY_SEPARATOR.'b'.($i + 1).'.py';
    file_put_contents($f, $code);
    $out = [];
    $rc = 0;
    exec('python '.escapeshellarg($f).' 2>&1', $out, $rc);
    if ($rc === 0) {
        $runOk++;
    } else {
        echo '✗ run block #'.($i + 1).' — '.implode(' ', $out)."\n";
        $failed++;
    }
}
check($runOk === count($blocks[1]), "Semua {$runOk}/".count($blocks[1]).' blok runnable exit 0');
foreach (glob($tmp.DIRECTORY_SEPARATOR.'*') ?: [] as $f) {
    @unlink($f);
}
@rmdir($tmp);

echo "\n=== Deep-audit #50: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
