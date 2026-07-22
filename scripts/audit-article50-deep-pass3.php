<?php

/**
 * Deep-audit pass-3 #50 — SEO / typo / CI drift / Capstone-jembatan / residual.
 * Usage: php scripts/audit-article50-deep-pass3.php
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

$src = file_get_contents(__DIR__.'/../database/seeders/Article50Seeder.php');
$ref = new ReflectionClass(Article50Seeder::class);
$m = $ref->getMethod('body');
$m->setAccessible(true);
$body = $m->invoke($ref->newInstanceWithoutConstructor());
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$routes = file_get_contents(__DIR__.'/../routes/web.php');
$tags = file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php');
$a49Audit = file_get_contents(__DIR__.'/audit-article49.php');
$a49Content = file_get_contents(__DIR__.'/audit-article49-content.php');

echo "=== Deep-audit pass-3 #50 ===\n\n";

// SEO
preg_match("/'seo_title'\s*=>\s*'([^']*)'/", $src, $seoT);
preg_match("/'seo_description'\s*=>\s*'([^']*)'/", $src, $seoD);
$seoTitle = $seoT[1] ?? '';
$seoDesc = $seoD[1] ?? '';
check($seoTitle !== '', 'seo_title ada');
check($seoDesc !== '', 'seo_description ada');
check(mb_strlen($seoTitle) <= 70, 'seo_title ≤70 chars ('.mb_strlen($seoTitle).')');
check(mb_strlen($seoDesc) >= 70 && mb_strlen($seoDesc) <= 170, 'seo_description 70–170 ('.mb_strlen($seoDesc).')');
check(str_contains(mb_strtolower($seoTitle), 'factory') && str_contains(mb_strtolower($seoTitle), 'strategy'), 'seo_title keywords');
check(str_contains(mb_strtolower($seoDesc), 'python'), 'seo_description sebut python');

// Structure
check(substr_count($body, '<h2') >= 10, '≥10 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-python') >= 5, '≥5 blok python');
$plainAll = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plainAll)) ?: [];
check(count($words) >= 900, 'Prosa ≥900 kata ('.count($words).')');

// Capstone jembatan (pass-3 fokus)
check(str_contains($body, 'capstone-sistem-perpustakaan-mini-oop-python'), 'Link Capstone');
check(
    str_contains($body, 'memasang') || str_contains($body, 'retrofit') || str_contains($body, 'gabung') || str_contains($body, 'Capstone') && str_contains($body, 'buat_item'),
    'Ada jembatan Capstone↔Factory (kata kunci)'
);
check(str_contains($body, 'Gabungkan di domain Capstone'), 'Section Gabungkan Capstone');

// Typo / residual ID
check(! str_contains($body, 'tinggal tunggu') && ! str_contains($body, 'coming soon'), 'Tidak teaser kosong');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'FIXME'), 'Tidak TODO/FIXME di body');
check(! str_contains($body, 'lorem ipsum'), 'Tidak lorem');
check(! preg_match('/\bdengan dengan\b|\byang yang\b/u', $plainAll), 'Tidak typo ganda kasar');
check(! str_contains($body, 'Pindahkan ke class <code>hitung</code>'), 'Tidak residual “class hitung” (method, bukan class)');
check(str_contains($body, 'method <code>hitung</code>') || str_contains($body, 'method hitung'), 'Kesalahan umum sebut method hitung');
check(str_contains($body, 'titik masuk praktis') || str_contains($body, 'ganti pembuatan'), 'Jembatan retrofit Capstone eksplisit');

// Framing drift
check(! str_contains($body, '11/10'), 'Tidak framing 11/10');
check(str_contains($body, '10/10'), 'Tetap sebut 10/10 Seri 3');
check(str_contains($body, 'Tier 2'), 'Framing Tier 2');
check(! str_contains($body, 'draft <strong>#50'), 'Bukan draft footer');

// Sibling audit drift: #49 audits must REQUIRE #50 (not forbid)
check(str_contains($a49Audit, 'design-pattern-factory-strategy-python'), 'audit-article49.php expect hardlink #50');
check(str_contains($a49Content, 'design-pattern-factory-strategy-python'), 'audit-article49-content expect #50');
check(! str_contains($a49Audit, 'Tidak hardlink slug #50+ unpublished'), 'audit #49 tidak lagi forbid #50');
check(str_contains($a49, 'design-pattern-factory-strategy-python'), 'Seeder #49 hardlink #50');

// CI / hook / route
check(str_contains($routes, 'publish-article-50'), 'Route #50');
check(preg_match('/Publish article 50[\s\S]{0,200}continue-on-error:\s*true/u', $yml) === 1
    || (str_contains($yml, 'publish-article-50') && str_contains($yml, 'continue-on-error: true')), 'CI #50 continue-on-error');
// More precise: find article 50 step block
$ciOk = false;
if (preg_match('/Publish article 50 via deploy hook[\s\S]*?(?=- name:)/u', $yml, $ciBlock)) {
    $ciOk = str_contains($ciBlock[0], 'continue-on-error: true');
}
check($ciOk, 'CI step #50 explicitly continue-on-error');
check(str_contains($deploy, 'publishArticle50'), 'DeployController method');
check(str_contains($deploy, 'Article 50 backlink #49 incomplete'), 'Hook verify #49 backlink');
check(str_contains($deploy, 'lib.items'), 'Hook cek lib.items');

// Tags
check(str_contains($tags, 'design-pattern'), 'TagSeeder design-pattern');
check(str_contains($src, "'composition'"), 'Seeder tag composition');

// Pedagogy leftovers
check(str_contains($body, 'ValueError'), 'Bahas ValueError');
check(str_contains($body, 'over-engineer') || str_contains($body, 'Over-engineer'), 'Peringatan over-engineer');
check(str_contains($body, 'DendaBertingkat') || str_contains($body, 'audiobook'), 'Latihan lanjut ada');
check(str_contains($body, 'oop50Arrow'), 'Marker unik');
check(substr_count($body, 'id="oop50Arrow"') === 1, 'Marker id tepat 1x');
check(! str_contains($body, 'input('), 'Tidak input(');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar #1a1a1a');
check(str_contains($body, 'background:#F5F5F0'), 'Figure #F5F5F0');

// Bare hashes
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/#50(?!\s*\(ini\))/', $plain), 'Bare #50 hanya (ini)');
check(! preg_match('/#(?:4[0-9]|5[1-9])(?!\s*\(ini\))/', $plain), 'Tidak bare sibling numbers');

// Unpublished hardlinks
check(! preg_match('/\/artikel\/[a-z0-9-]*(flask|fastapi)/', $body), 'Tidak hardlink #52 slug');
check(str_contains($body, '/artikel/oop-micropython-esp32-class-sensor'), 'Hardlink #51 MicroPython');

// cover / featured
check(preg_match("/'is_featured'\s*=>\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\s*=>/", $src), 'cover_image tidak di-set');

echo "\n=== Deep pass-3 #50: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
