<?php

/**
 * Content / checklist audit #59.
 * Usage: php scripts/audit-article59-content.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article59Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Content / checklist audit #59 ===\n\n";

$ref = new ReflectionClass(Article59Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php');
$plain = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '') ?? '');

check(str_contains($body, '#59 (ini)'), 'Self-ref #59 (ini)');
check(! preg_match('/(?<![\w\/"#>])#60(?!\s*\(ini\))/', $plain), 'Tidak plain #60');
$plainAll = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#58(?!\d)(?!\s*\(ini\))/', $plainAll), 'Tidak bare #58 di prosa');
check(str_contains($body, '/artikel/laravel-controller-service-eloquent'), 'Link #58');
check(! str_contains($body, '→'), 'Tidak panah Unicode');
check(substr_count($body, 'background:#F5F5F0') >= 2, '≥2 figure #F5F5F0');
check(str_contains($body, 'color:#1a1a1a'), 'Pola Dasar dark-safe');
check(str_contains($body, 'laravel_auth_api_demo.php'), 'File contoh');
check(str_contains($body, 'Latihan singkat'), 'Latihan');
check(str_contains($body, 'FAQ singkat'), 'FAQ');
check(str_contains($body, 'Kesalahan umum'), 'Kesalahan umum');
check(str_contains($body, 'laravel59authArrow'), 'SVG marker');
check(str_contains($body, 'Seri 4'), 'Seri 4');
check(substr_count($body, 'language-php') >= 4, '≥4 language-php');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'laravel-auth-api-dasar'), 'Slug');
check(str_contains(file_get_contents(__DIR__.'/../routes/web.php'), 'publish-article-59'), 'Route hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'laravel-auth-api-dasar'), 'CI slug');
check(str_contains($body, '7/8 menuju Capstone Laravel'), 'Progress 7/8');
check(str_contains($body, 'Prasyarat'), 'Prasyarat awam');
check(str_contains($body, 'Arti awam'), 'Gloss awam');
check(str_contains($body, '401'), 'Status 401');
check(! str_contains($body, 'tanpa hardlink') && ! str_contains($body, 'STOP AUDIT'), 'Tanpa suara editor');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php'), 'laravel-auth-api-dasar'), '#58 hardlink #59');
check(file_exists(__DIR__.'/audit-article59.php'), 'Audit utama ada');
check(file_exists(__DIR__.'/audit-article59-php.php'), 'Audit PHP ada');
check(file_exists(__DIR__.'/audit-article59-sanitize.php'), 'Audit sanitize ada');
check(file_exists(__DIR__.'/audit-article59-deep.php'), 'Deep pass-1 ada');
check(str_contains($body, 'Kenapa belum langsung') || str_contains($body, 'tanpa framework'), 'Narasi PHP dulu');
check(str_contains($body, 'loket') || str_contains($body, 'perpustakaan'), 'Analogi loket/perpustakaan');
check(str_contains($body, 'bukti masuk'), 'Gloss bukti masuk');
check(str_contains($body, 'Belum diizinkan'), 'Gloss Belum diizinkan');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Pakai') && str_contains($body, 'Laravel 11+'), 'Versi Laravel awam');
check(str_contains($body, 'Capstone'), 'Soft bridge Capstone');
check(str_contains($body, 'Bearer') && str_contains($body, 'bukti'), 'Gloss Bearer');
check(str_contains($body, 'middleware') && str_contains($body, 'pemeriksa'), 'Gloss middleware');
check(! str_contains($body, '@param') && ! str_contains($body, '@return'), 'Tanpa PHPDoc @param di demo');
check(! str_contains($body, 'Unauthorized'), 'Tanpa jargon Unauthorized');
check(! str_contains($body, 'JWT'), 'Tanpa JWT');
check(str_contains($body, 'LoginRequest') && str_contains($body, 'Form Request'), 'Gloss LoginRequest');
check(str_contains($body, 'JsonResponse') && str_contains($body, 'tipe jawaban'), 'Gloss JsonResponse');
check(str_contains($body, 'Authorization') && str_contains($body, 'kotak di header'), 'Gloss Authorization');
check(str_contains($body, 'Cek login'), 'SVG Cek login');
check(str_contains($body, 'pemeriksa pintu (middleware)'), 'KU middleware awam');
check(str_contains($body, 'Teks acak di header'), 'Istilah teks acak');
check(str_contains($body, 'proyek Laravel'), 'Proyek Laravel');
check(str_contains($body, 'yang memanggil API'), 'Gloss pemanggil');
check(str_contains($body, 'Authorization: Bearer kartu-'), 'Contoh header Bearer');

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
