<?php

/**
 * Deep-audit pass-1 #58 (ramah awam + SEO + locks).
 * Usage: php scripts/audit-article58-deep.php
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

echo "=== Deep-audit pass-1 #58 ===\n\n";

$ref = new ReflectionClass(Article58Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article58Seeder.php');
$plain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $body) ?? '');

check(str_word_count($plain) >= 550, 'Prosa ≥550 kata ('.str_word_count($plain).')');
check(substr_count($body, '<h2') >= 11, '≥11 H2 ('.substr_count($body, '<h2').')');
check(substr_count($body, 'language-php') >= 4, '≥4 blok PHP');
check(preg_match("/'seo_title'\\s*=>\\s*'([^']+)'/", $src, $m) === 1 && mb_strlen($m[1]) <= 70, 'seo_title ≤70');
check(preg_match("/'seo_description'\\s*=>\\s*'([^']+)'/", $src, $m) === 1 && mb_strlen($m[1]) >= 70 && mb_strlen($m[1]) <= 170, 'seo_desc 70–170 ('.mb_strlen($m[1] ?? '').')');
check(str_contains($body, 'BukuService') && str_contains($body, 'Eloquent'), 'Service + Eloquent');
check(str_contains($body, 'Kenapa belum langsung') || str_contains($body, 'tanpa framework'), 'Fondasi PHP dulu');
check(str_contains($body, 'Seri 4') && str_contains($body, '#58 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 11+'), 'Pin Laravel');
check(substr_count($body, '/artikel/laravel-request-validasi-api') >= 2, '≥2 link #57');
check(! preg_match('/(?<![\w\/"#>])#(?:59|60)(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #59+');
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#57(?!\d)(?!\s*\(ini\))/', $plainLinked), 'Tidak bare #57');
check(! str_contains($body, '→'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') && str_contains($body, 'figcaption'), 'SVG a11y');
check(str_contains($body, 'laravel_controller_service_demo.php') && str_contains($body, 'demo('), 'File + demo');
check(str_contains($body, 'Pola Dasar'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-controller-service-eloquent'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
check(str_contains(file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php'), 'publishArticle58'), 'Hook');
check(str_contains(file_get_contents(__DIR__.'/../.github/workflows/deploy.yml'), 'Publish article 58 via deploy hook (required)'), 'CI #58 required');
check(! preg_match('/Publish article 58 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', file_get_contents(__DIR__.'/../.github/workflows/deploy.yml')), 'CI #58 tidak continue-on-error');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article57Seeder.php'), 'laravel-controller-service-eloquent'), '#57 hardlink #58');
check(str_contains($body, '8/8 Capstone Laravel selesai'), 'Progress 8/8');
check(str_contains($body, '/artikel/laravel-auth-api-dasar'), 'Hardlink #59');
check(str_contains($body, 'stack Laravel') || str_contains($body, '3/5'), 'Framing stack Laravel');
check(str_contains($body, 'Arti awam') || str_contains($body, 'pengatur kode'), 'Gloss awam');
check(str_contains($body, 'otentikasi'), 'Auth framing');
check(str_contains($body, 'loket') || str_contains($body, 'perpustakaan'), 'Analogi loket/perpustakaan');
check(str_contains($body, '<td>Controller</td>') || str_contains($body, '>Controller</td>'), 'Gloss Controller');
check(! str_contains($body, 'closure') && ! str_contains($body, 'Pin framework'), 'Tanpa Pin/closure');
check(str_contains($body, '$fillable') || str_contains($body, 'fillable'), 'Gloss fillable');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(str_contains($body, 'validated()'), 'Gloss validated()');
check(str_contains($body, 'menyiapkan layanan otomatis') || str_contains($body, 'tidak perlu'), 'Gloss DI konstruktor');
check(str_contains($body, 'langkah kerja'), 'Gloss langkah kerja (bukan logika bisnis mentah)');
check(! str_contains($body, 'MassAssignmentException'), 'Tanpa MassAssignmentException');
check(str_contains($body, 'bukti masuk') || str_contains($body, 'login'), 'Soft bridge tanpa jargon token mentah');
check(! str_contains($body, 'BukuController@store'), 'Tanpa notasi @store');
check(str_contains($body, '/artikel/laravel-routing-json-perpustakaan-api'), 'Link #56 di jembatan route');
check(str_contains($body, 'orderBy') && str_contains($body, 'urutkan'), 'Gloss query orderBy');
check(str_contains($body, 'skrip pembuat tabel') || str_contains($body, 'migrasi'), 'Gloss migrasi awam');
check(str_contains($body, 'Buku::create'), 'Eloquent create cuplikan');
check(str_contains($body, 'JsonResponse') && str_contains($body, 'tipe jawaban'), 'Gloss JsonResponse');
check(str_contains($body, 'callable') && str_contains($body, 'bisa dipanggil'), 'Gloss callable');
check(str_contains($body, 'Perintah database tersebar'), 'KU tanpa SQL mentah');
check(! str_contains($body, 'Logika bisnis ditulis') && ! str_contains($body, 'pekerjaan bisnis'), 'Tanpa logika/pekerjaan bisnis mentah');
check(str_contains($body, 'Isian tidak tersimpan') || str_contains($body, 'isian yang sudah lolos'), 'Gloss isian (bukan field mentah)');
check(str_contains($body, 'Pindahkan langkah kerja ke service'), 'Pola Dasar langkah kerja');
check(str_contains($body, 'strict_types') && str_contains($body, 'tipe data lebih ketat'), 'Gloss strict_types');

echo "\n=== Deep-audit pass-1 #58: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: JENUH post-live ramah-awam — STOP AUDIT → oke deploy (resync prod #58).\n";
}
exit($failed > 0 ? 1 : 0);
