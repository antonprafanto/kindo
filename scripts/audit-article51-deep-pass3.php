<?php

/**
 * Deep-audit pass-3 #51 — porting / FakePin konsistensi / sibling audit drift / word count.
 * Usage: php scripts/audit-article51-deep-pass3.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article51Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$ref = new ReflectionClass(Article51Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article51Seeder.php');
$a49 = file_get_contents(__DIR__.'/../database/seeders/Article49Seeder.php');
$a49Content = file_get_contents(__DIR__.'/audit-article49-content.php');
$a49Audit = file_get_contents(__DIR__.'/audit-article49.php');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
$tags = file_get_contents(__DIR__.'/../database/seeders/TagSeeder.php');

echo "=== Deep-audit pass-3 #51 ===\n\n";

// Structure / prose
$plainAll = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plainAll)) ?: [];
check(count($words) >= 850, 'Prosa ≥850 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 12, '≥12 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-python') >= 5, '≥5 blok python');
check(str_contains($body, 'Porting singkat'), 'Section Porting singkat');
check(str_contains($body, 'language-text') && str_contains($body, 'from machine import Pin'), 'Blok porting machine.Pin (text)');

// FakePin konsistensi: setiap blok yang punya FakePin harus punya OUT=
preg_match_all('/<pre><code class="language-python">(.*?)<\/code><\/pre>/s', $body, $blocks);
$fakeOk = true;
foreach ($blocks[1] as $raw) {
    $c = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    if (str_contains($c, 'class FakePin') && ! str_contains($c, 'OUT =')) {
        $fakeOk = false;
        break;
    }
}
check($fakeOk, 'Semua FakePin punya OUT =');

// Links dataclass/ABC
check(str_contains($body, 'special-methods-dataclass-python'), 'Link #48 dataclass');
check(str_contains($body, 'abstraction-abc-python-oop'), 'Link #46 ABC');

// Sibling audit drift
check(str_contains($a49Content, 'oop-micropython-esp32-class-sensor'), 'audit-article49-content expect #51');
check(str_contains($a49Audit, 'oop-micropython-esp32-class-sensor'), 'audit-article49.php expect #51');
check(substr_count($a49, 'oop-micropython-esp32-class-sensor') >= 2, 'Seeder #49 ≥2 hardlink');
check(! str_contains($a49, 'Ide berikutnya (belum live): MicroPython'), 'Capstone tanpa MicroPython belum live');

// Tags / SEO / residual
check(str_contains($tags, 'micropython') && str_contains($tags, 'esp32'), 'TagSeeder micropython+esp32');
preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $seoT);
preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $seoD);
check(strlen($seoT[1] ?? '') <= 70, 'seo_title ≤70');
check(strlen($seoD[1] ?? '') >= 70 && strlen($seoD[1] ?? '') <= 170, 'seo_desc 70–170');
check(! str_contains($body, 'TODO') && ! str_contains($body, 'FIXME'), 'Tanpa TODO/FIXME');
check(! preg_match('/→/u', $body), 'Tanpa panah Unicode');
check(! str_contains($body, 'input('), 'Tanpa input()');
check(str_contains($body, '/artikel/oop-flask-fastapi-class-api'), 'Hardlink #52 Flask/FastAPI');

// Hook / CI
check(str_contains($deploy, 'from machine import Pin'), 'Hook body check porting Pin');
check(str_contains($deploy, 'Article 51 backlink #49 incomplete'), 'Hook verify #49');
check(preg_match('/Publish article 51 via deploy hook \(required\)/u', $yml) === 1, 'CI #51 required');
check(preg_match('/Publish article 50 via deploy hook \(required\)/u', $yml) === 1, 'CI #50 required');

// a11y
check(substr_count($body, 'aria-label') >= 2, '≥2 aria-label');
check(str_contains($body, 'role="img"'), 'SVG role=img');
check(substr_count($body, 'id="oop51Arrow"') === 1, 'Marker oop51 unik 1x');

// cover
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'cover tidak overwrite');

echo "\n=== Deep-audit pass-3 #51: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
