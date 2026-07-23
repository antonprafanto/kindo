<?php

/**
 * Content / checklist audit #58.
 * Usage: php scripts/audit-article58-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article58Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #58 ===\n\n";

$ref = new ReflectionClass(Article58Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php');
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(str_contains($body, '#58 (ini)'), 'Self-ref #58 (ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:59|60)(?!\s*\(ini\))/', $plain), 'Tidak plain #59+');
$plainAll = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#57(?!\d)(?!\s*\(ini\))/', $plainAll), 'Tidak bare #57 di prosa');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Link #57');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_controller_service_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan singkat'), 'Latihan');
check(str_contains($body, 'FAQ singkat'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel58ctrlArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'laravel-controller-service-eloquent'), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-58'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'laravel-controller-service-eloquent'), 'CI slug');
check(str_contains($body, '6/8 menuju Capstone Laravel'), 'Progress 6/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Arti awam'), 'Gloss awam');
check(str_contains($body, 'Eloquent'), 'Eloquent');
check(! str_contains($body, 'tanpa hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php'), 'laravel-controller-service-eloquent'), '#57 hardlink #58');
check(file_exists(__DIR__.'/audit-article58.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article58-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article58-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article58-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'Kenapa belum langsung Eloquent') || str_contains($body, 'Kenapa belum langsung'), 'Narasi PHP dulu');
check(str_contains($body, 'loket') || str_contains($body, 'perpustakaan'), 'Analogi loket/perpustakaan');
check(str_contains($body, '<td>Controller</td>') || str_contains($body, '>Controller</td>'), 'Gloss Controller');
check(str_contains($body, '<td>Service</td>') || str_contains($body, '>Service</td>'), 'Gloss Service');
check(str_contains($body, '<td>Eloquent</td>') || str_contains($body, '>Eloquent</td>'), 'Gloss Eloquent');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Pakai') && str_contains($body, 'Laravel 11+'), 'Versi Laravel awam');
check(str_contains($body, 'otentikasi'), 'Soft bridge auth');
check(str_contains($body, '$fillable') || str_contains($body, 'fillable'), 'Gloss fillable');
check(! str_contains($body, '@param') && ! str_contains($body, '@return'), 'Tanpa PHPDoc @param di demo');

check(str_contains($body, 'validated()'), 'Gloss validated()');
check(! str_contains($body, 'MassAssignmentException'), 'Tanpa MassAssignmentException');
check(str_contains($body, 'langkah kerja'), 'Gloss langkah kerja');
check(! str_contains($body, 'BukuController@store'), 'Tanpa @store');
check(str_contains($body, 'bukti masuk') || ! str_contains($body, 'token'), 'Tanpa token mentah / ada bukti masuk');
check(str_contains($body, 'JsonResponse') && str_contains($body, 'tipe jawaban'), 'Gloss JsonResponse');
check(str_contains($body, 'callable') && str_contains($body, 'bisa dipanggil'), 'Gloss callable');
check(! str_contains($body, 'Logika bisnis ditulis') && ! str_contains($body, 'pekerjaan bisnis'), 'Tanpa bisnis-jargon mentah di KU/FAQ');
check(str_contains($body, 'Perintah database tersebar'), 'KU perintah database awam');

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
