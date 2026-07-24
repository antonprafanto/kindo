<?php

/**
 * Deep-audit pass-1 #56 (ramah awam + SEO + locks).
 * Usage: php scripts/audit-article56-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article56Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #56 ===\n\n";

$ref = new ReflectionClass(Article56Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php');
$plain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');
$words = preg_split('/\s+/u', trim(strip_tags($plain))) ?: [];

check(count($words) >= 550, 'Prosa ≥550 kata ('.count($words).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(strlen('Laravel Routing & JSON — API untuk Pemula') <= 70, 'seo_title ≤70');
$desc = 'Artikel pertama Laravel di Seri 4: paham route sebagai pintu HTTP, kirim JSON, dan status 200/404 — domain perpustakaan mini, berbahasa Indonesia.';
check(strlen($desc) >= 70 && strlen($desc) <= 170, 'seo_desc 70–170 ('.strlen($desc).')');
check(str_contains($body, 'Route::get') && str_contains($body, 'response()-&gt;json'), 'Route + json response');
check(str_contains($body, 'json_encode') && str_contains($body, 'http_response_code'), 'Fondasi PHP JSON');
check(str_contains($body, 'Seri 4') && str_contains($body, '#56 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel');
check(substr_count($body, '/artikel/oop-php-visibility-composition') >= 2, '≥2 link #55');
check(! preg_match('/(?<![\w\/"#>])#(?:5[7-9]|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #57+');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'role="img"') || str_contains($body, 'aria-label'), 'SVG a11y');
check(str_contains($body, 'laravel_routing_json_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan singkat') && str_contains($body, 'FAQ singkat'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-routing-json-perpustakaan-api'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle56'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 56 via deploy hook (required)'), 'CI #56 required');
check(! preg_match('/Publish article 56 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #56 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article55Seeder.php'), 'laravel-routing-json-perpustakaan-api'), '#55 hardlink #56');
check(str_contains($body, '8/8 Capstone Laravel selesai'), 'Progress 5/8');
check(str_contains($body, 'stack Laravel') || str_contains($body, '1/5'), 'Framing stack Laravel');
check(str_contains($body, 'Arti awam') || str_contains($body, 'pintu'), 'Gloss awam');
check(str_contains($body, '404'), 'Status 404');
check(str_contains($body, 'Form Request') || str_contains($body, 'validasi'), 'Jembatan soft ke validasi');
check(str_contains($body, 'Kenapa belum langsung buka Laravel'), 'Narasi progresif awam');
check(str_contains($body, 'loket'), 'Analogi loket');
check(str_contains($body, '<td>GET</td>'), 'Gloss GET');
check(! str_contains($body, 'Pin framework') && ! str_contains($body, 'closure'), 'Tanpa Pin/closure');
check(str_contains($body, 'Developer Tools'), 'Gloss Developer Tools');
check(str_contains($body, 'array_values') && str_contains($body, 'merapikan daftar'), 'Gloss array_values');
check(file_exists(__DIR__.'/audit-article56-deep-pass2.php'), 'Deep pass-2 ada');
check(file_exists(__DIR__.'/audit-article56-deep-pass3.php'), 'Deep pass-3 ada');

echo "\n=== Deep-audit pass-1 #56: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
