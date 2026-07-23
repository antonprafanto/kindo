<?php

/**
 * Content / checklist audit #57.
 * Usage: php scripts/audit-article57-content.php
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

echo "=== Content / checklist audit #57 ===\n\n";

$ref = new ReflectionClass(Article57Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php');
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(str_contains($body, '#57 (ini)'), 'Self-ref #57 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:5[89]|60)(?!\s*\(ini\))/', $plain), 'Tidak plain #58+');
$plainAll = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#56(?!\d)(?!\s*\(ini\))/', $plainAll), 'Tidak bare #56 di prosa');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Link #56');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_request_validasi_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan singkat'), 'Latihan');
check(str_contains($body, 'FAQ singkat'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel57reqArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'laravel-request-validasi-api'), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-57'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'laravel-request-validasi-api'), 'CI slug');
check(str_contains($body, '5/8 menuju Capstone Laravel'), 'Progress 5/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Arti awam'), 'Gloss awam');
check(str_contains($body, '422'), 'Status 422');
check(! str_contains($body, 'tanpa hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article56Seeder.php'), 'laravel-request-validasi-api'), '#56 hardlink #57');
check(file_exists(__DIR__.'/audit-article57.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article57-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article57-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article57-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'Kenapa belum langsung Form Request') || str_contains($body, 'Kenapa belum langsung'), 'Narasi PHP dulu');
check(str_contains($body, 'loket') || str_contains($body, 'penjaga'), 'Analogi penjaga/loket');
check(str_contains($body, '<td>Request</td>') || str_contains($body, '>Request</td>'), 'Gloss Request');
check(str_contains($body, 'Form Request'), 'Gloss Form Request');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Pakai') && str_contains($body, 'Laravel 11+'), 'Versi Laravel awam');
check(! str_contains($body, 'Unprocessable Entity'), 'Tanpa jargon Unprocessable');
check(! str_contains($body, 'Client /'), 'SVG Browser bukan Client');
check(str_contains($body, 'required') && str_contains($body, 'wajib'), 'Gloss aturan required');
check(str_contains($body, '<td>POST</td>') || str_contains($body, '>POST</td>'), 'Gloss POST');
check(! str_contains($body, '@param') && ! str_contains($body, '@return'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'JSON body'), 'Tanpa jargon JSON body');
check(str_contains($body, 'tampilan di browser') || str_contains($body, 'sering disebut frontend'), 'Gloss frontend awam');
check(str_contains($body, 'pengatur kode (controller)') && str_contains($body, 'layanan (service)'), 'Gloss controller/service soft');

preg_match_all('/<a href="[^"]+">([^<]*)<\/a>/', $body, $anchors);
$thin = 0;
foreach ($anchors[1] as $text) {
    if (preg_match('/^#\d+$/', trim(html_entity_decode($text)))) {
        $thin++;
    }
}
check($thin === 0, 'Thin anchor = 0');

echo "\n=== Hasil: {$passed} passed, {$failed} failed ===\n";
exit($failed > 0 ? 1 : 0);
