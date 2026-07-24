<?php

/**
 * Deep-audit pass-1 #59 (ramah awam + SEO + locks).
 * Usage: php scripts/audit-article59-deep.php
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

echo "=== Deep-audit pass-1 #59 ===\n\n";

$ref = new ReflectionClass(Article59Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php');
$plain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');

check(str_word_count($plain) >= 550, 'Prosa ≥550 kata ('.str_word_count($plain).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.mb_strlen($m[1] ?? '').')');
check(str_contains($body, 'bukti_masuk') && str_contains($body, '401'), 'Bukti + 401');
check(str_contains($body, 'Kenapa belum langsung') || str_contains($body, 'tanpa framework'), 'Fondasi PHP dulu');
check(str_contains($body, 'Seri 4') && str_contains($body, '#59 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel');
check(substr_count($body, '/artikel/laravel-controller-service-eloquent') >= 2, '≥2 link #58');
check(! preg_match('/(?<![\w\/"#>])#60(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #60');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#58(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #58');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'laravel_auth_api_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-auth-api-dasar'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle59'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 59 via deploy hook (required)'), 'CI #59 required');
check(! preg_match('/Publish article 59 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #59 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php'), 'laravel-auth-api-dasar'), '#58 hardlink #59');
check(str_contains($body, '7/8 menuju Capstone Laravel'), 'Progress 7/8');
check(str_contains($body, 'stack Laravel') || str_contains($body, '4/5'), 'Framing stack Laravel');
check(str_contains($body, 'Arti awam') || str_contains($body, 'bukti masuk'), 'Gloss awam');
check(str_contains($body, 'Capstone'), 'Jembatan soft ke Capstone');
check(str_contains($body, 'loket') || str_contains($body, 'perpustakaan'), 'Analogi loket/perpustakaan');
check(str_contains($body, 'Belum diizinkan'), 'Gloss Belum diizinkan');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, 'Bearer') && str_contains($body, 'bukti'), 'Gloss Bearer');
check(str_contains($body, 'middleware') && str_contains($body, 'pemeriksa'), 'Gloss middleware');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(! str_contains($body, 'Unauthorized') && ! str_contains($body, 'JWT'), 'Tanpa Unauthorized/JWT');
check(str_contains($body, 'Hash::check') || str_contains($body, 'terenkripsi'), 'Gloss Hash/sandi');
check(str_contains($body, '422') && str_contains($body, '401'), 'Bedakan 401 vs 422');
check(str_contains($body, 'LoginRequest') && str_contains($body, 'Form Request'), 'Gloss LoginRequest');
check(str_contains($body, 'JsonResponse') && str_contains($body, 'tipe jawaban'), 'Gloss JsonResponse');
check(str_contains($body, 'Authorization') && str_contains($body, 'kotak di header'), 'Gloss Authorization');
check(str_contains($body, 'Cek login'), 'SVG tanpa label Auth mentah');
check(str_contains($body, 'sering disebut Sanctum') || str_contains($body, 'paket bukti masuk Laravel'), 'Sanctum digloss dulu');
check(str_contains($body, 'bin2hex') && str_contains($body, 'teks acak'), 'Gloss random bukti');
check(str_contains($body, 'strict_types') && str_contains($body, 'tipe data lebih ketat'), 'Gloss strict_types');
check(str_contains($body, 'pemeriksa pintu (middleware)'), 'KU middleware digloss');
check(str_contains($body, '/artikel/laravel-request-validasi-api'), 'Link #57 untuk Form Request');

echo "\n=== Deep-audit pass-1 #59: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH LIVE — hardlink #58 terkunci. STOP AUDIT → oke deploy hanya untuk resync/bug.\n";
}
exit($failed > 0 ? 1 : 0);
