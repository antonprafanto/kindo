<?php

/**
 * Deep-audit pass-3 #52 — sanitize keep / slug resolve / output match / hook residual / jenuh.
 * Usage: php scripts/audit-article52-deep-pass3.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
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
$src = file_get_contents(__DIR__.'/../database/seeders/Article52Seeder.php');
$a40 = file_get_contents(__DIR__.'/../database/seeders/Article40Seeder.php');
$a40Audit = file_get_contents(__DIR__.'/audit-article40.php');
$a50 = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$pyAudit = file_get_contents(__DIR__.'/audit-article52-python.php');

echo "=== Deep-audit pass-3 #52 ===\n\n";

$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
foreach ([
    'oop52Arrow' => 'Sanitize keep oop52Arrow',
    'AppShell' => 'Sanitize keep AppShell',
    'JSONResponse' => 'Sanitize keep JSONResponse',
    'handle_create' => 'Sanitize keep handle_create',
    'perpustakaan_api_oop.py' => 'Sanitize keep file contoh',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt' => 'Sanitize keep #39',
    'inheritance-pewarisan-class-python' => 'Sanitize keep #44',
    'language-text' => 'Sanitize keep porting text',
    'demo(' => 'Sanitize keep demo(',
    '#52 (ini)' => 'Sanitize keep self-ref',
    'Status selalu 200' => 'Sanitize keep tip status 200',
    'Sketsa: query param' => 'Sanitize keep query-param note',
] as $needle => $label) {
    check(str_contains($san, $needle), $label);
}
check(strlen($san) > strlen($body) * 0.85, 'Sanitize tidak memangkas >15%');

preg_match_all('/href="\/artikel\/([a-z0-9-]+)"/', $body, $slugMatches);
$slugs = array_unique($slugMatches[1] ?? []);
$blob = '';
foreach (glob(__DIR__.'/../database/seeders/*.php') ?: [] as $f) {
    $blob .= file_get_contents($f);
}
$unknown = [];
foreach ($slugs as $s) {
    if (! str_contains($blob, $s)) {
        $unknown[] = $s;
    }
}
check($unknown === [], 'Semua slug internal dikenal'.($unknown ? ' — '.implode(',', $unknown) : ''));
check(count($slugs) >= 8, '≥8 slug internal ('.count($slugs).')');

// Expected: #40 #43 #44 #45 #47 #49 #50 #51 #39
$requiredSlugs = [
    'oop-micropython-esp32-class-sensor',
    'capstone-sistem-perpustakaan-mini-oop-python',
    'design-pattern-factory-strategy-python',
    'composition-vs-inheritance-python',
    'mengenal-oop-cara-berpikir-dengan-objek-python',
    'inheritance-pewarisan-class-python',
    'encapsulation-property-python-oop',
    'polymorphism-python-oop',
    'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt',
];
foreach ($requiredSlugs as $s) {
    check(in_array($s, $slugs, true), "Slug wajib: {$s}");
}

check(str_contains($a40, 'oop-flask-fastapi-class-api'), 'Seeder #40 hardlink #52');
check(str_contains($a40Audit, 'oop-flask-fastapi-class-api'), 'audit-article40.php expect #52');
check(str_contains($a50, 'oop-flask-fastapi-class-api'), '#50 hardlink #52');

check(str_contains($deploy, 'smart-greenhouse-esp32-sensor-aktuator-dashboard-mqtt'), 'Hook body cek #39 greenhouse');
check(str_contains($deploy, 'Status selalu 200'), 'Hook body cek tip status 200');

// Progressive expected outputs appear in prose (normalize CRLF)
$normBody = str_replace("\r\n", "\n", $body);
check(str_contains($normBody, "service terpasang, jumlah= 0"), 'Output blok AppShell');
check(str_contains($normBody, "[{'judul': 'OOP Python', 'penulis': 'Kindo'}]"), 'Output blok service');
check(str_contains($normBody, "201\n{'items': [{'judul': 'Flask Ringkas', 'penulis': 'Dewi'}]}\n400"), 'Output blok stub HTTP');
check(str_contains($normBody, 'jenis tidak dikenal: majalah'), 'Output blok factory');
check(str_contains($normBody, "201 {'judul': 'OOP Python', 'penulis': 'Kindo'}"), 'Output demo lengkap');

check(str_contains($pyAudit, 'run block'), 'Python audit run progressive');
check(file_exists(__DIR__.'/audit-article52-deep.php'), 'Pass-1 deep ada');
check(file_exists(__DIR__.'/audit-article52-deep-pass2.php'), 'Pass-2 deep ada');

check(str_contains($deploy, 'AppShell') && str_contains($deploy, 'JSONResponse') && str_contains($deploy, 'inheritance-pewarisan-class-python'), 'Hook body residual pass-2');
check(str_contains($deploy, 'Article 52 backlink #40 incomplete'), 'Hook verify #40');
check(preg_match('/Publish article 52 via deploy hook \(required\)/u', $yml) === 1, 'CI #52 required');

$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#52(?!\s*\(ini\))/', $plain), 'Tidak plain #52 selain (ini)');
check(! preg_match('/→/u', $body), 'Tanpa panah Unicode');
check(! str_contains($body, 'input('), 'Tanpa input()');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'FIXME'), 'Tanpa TODO/FIXME');
check(! str_contains($body, 'belum live): Flask'), 'Tanpa residual Flask belum live');

preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1
    ? check(true, 'is_featured false')
    : check(false, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');

$plainAll = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plainAll)) ?: [];
check(count($words) >= 800, 'Prosa ≥800 ('.count($words).')');

echo "\n=== Deep-audit pass-3 #52: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Status: jenuh confirm — tidak ada gap konten baru material; siap oke deploy.\n";
}
exit($failed > 0 ? 1 : 0);
