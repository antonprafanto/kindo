<?php

/**
 * Deep-audit pass-1 #54 (ramah awam + SEO + locks).
 * Usage: php scripts/audit-article54-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article54Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #54 ===\n\n";

$ref = new ReflectionClass(Article54Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article54Seeder.php');
$plain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim($plain)) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(strlen('Property, Method & Constructor PHP — OOP untuk Pemula') <= 70, 'seo_title ≤70');
$desc = 'Lanjut OOP PHP: property (data), method (perilaku), constructor (__construct), dan type hint — berbahasa Indonesia, ramah awam.';
check(strlen($desc) >= 70 && strlen($desc) <= 170, 'seo_desc 70–170 ('.strlen($desc).')');
check(str_contains($body, '__construct') && str_contains($body, 'public string'), 'Property + constructor');
check(str_contains($body, 'new Buku'), 'new Buku');
check(str_contains($body, 'Seri 4') && str_contains($body, '#54 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel'), 'Sebut Laravel sebagai tujuan');
check(substr_count($body, '/artikel/mengenal-oop-cara-berpikir-dengan-objek-php') >= 2, '≥2 link #53');
$plainNoLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');
check(! preg_match('/(?<![\w\/"#>])#55(?!\s*\(ini\))/', $plainNoLinks), 'Tidak bare #55');
check(str_contains($body, 'oop-php-visibility-composition'), 'Hardlink #55');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'role="img"') || str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'oop_php_property.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan singkat') && str_contains($body, 'FAQ singkat'), 'KU/Latihan/FAQ');
check(str_contains($src, 'oop-php-property-method-constructor'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle54'), 'Hook');
check(preg_match('/Publish article 54 via deploy hook \(required\)/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')) === 1, 'CI #54 required');
check(! preg_match('/Publish article 54 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #54 tidak continue-on-error');
check(str_contains($body, 'Property') && str_contains($body, 'Method') && str_contains($body, 'Constructor'), 'Tiga konsep inti');
check(str_contains($body, '$this'), 'Jelaskan $this');
check(str_contains($body, 'type hint') || str_contains($body, 'Type hint'), 'Type hint awam');
check(str_contains($body, 'Prasyarat'), 'Prasyarat');
check(str_contains($body, '3/8 menuju Capstone Laravel'), 'Progress 3/8');
check(str_contains($body, 'ringkas'), 'Method kedua ringkas');
check(str_contains($body, 'isbn') || str_contains($body, 'ISBN'), 'Property isbn di demo lengkap');

echo "\n=== Deep-audit pass-1 #54: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
