<?php

/**
 * Deep-audit pass-1 #57 (ramah awam + SEO + locks).
 * Usage: php scripts/audit-article57-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article57Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #57 ===\n\n";

$ref = new ReflectionClass(Article57Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php');
$plain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');

check(str_word_count($plain) >= 550, 'Prosa ≥550 kata ('.str_word_count($plain).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.mb_strlen($m[1] ?? '').')');
check(str_contains($body, 'validate') && str_contains($body, '422'), 'Validate + 422');
check(str_contains($body, 'Kenapa belum langsung') || str_contains($body, 'tanpa framework'), 'Fondasi PHP validasi');
check(str_contains($body, 'Seri 4') && str_contains($body, '#57 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel');
check(substr_count($body, '/artikel/laravel-routing-json-perpustakaan-api') >= 2, '≥2 link #56');
check(substr_count($body, '/artikel/laravel-routing-json-perpustakaan-api') >= 3, '≥3 link #56 (termasuk gloss 404)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[89]|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #58+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#56(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #56');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'laravel_request_validasi_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-request-validasi-api'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle57'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 57 via deploy hook (required)'), 'CI #57 required');
check(! preg_match('/Publish article 57 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #57 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php'), 'laravel-request-validasi-api'), '#56 hardlink #57');
check(str_contains($body, '8/8 Capstone Laravel selesai'), 'Progress 8/8');
check(str_contains($body, 'stack Laravel') || str_contains($body, '2/5'), 'Framing stack Laravel');
check(str_contains($body, 'Arti awam') || str_contains($body, 'penjaga'), 'Gloss awam');
check(str_contains($body, '/artikel/laravel-controller-service-eloquent'), 'Hardlink #58');
check(str_contains($body, 'loket') || str_contains($body, 'penjaga'), 'Analogi loket/penjaga');
check(str_contains($body, '<td>Request</td>') || str_contains($body, 'Request</td>'), 'Gloss Request');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(! str_contains($body, 'Unprocessable Entity') && ! str_contains($body, 'Client /'), 'Tanpa Unprocessable/Client');
check(str_contains($body, 'required') && str_contains($body, 'wajib'), 'Gloss pipe required');
check(str_contains($body, '<td>POST</td>') || str_contains($body, '>POST</td>'), 'Gloss POST');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(! str_contains($body, 'JSON body'), 'Tanpa JSON body');
check(str_contains($body, 'sering disebut frontend') || str_contains($body, 'tampilan di browser'), 'Gloss frontend');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#56(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #56');

echo "\n=== Deep-audit pass-1 #57: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH — post-live ramah-awam polish terkunci. STOP AUDIT → oke deploy (resync prod #57).\n";
}
exit($failed > 0 ? 1 : 0);
