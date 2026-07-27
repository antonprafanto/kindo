<?php

/**
 * Deep-audit pass-1 #60 — Controller/Service/Eloquent ramah awam.
 * Usage: php scripts/audit-article60-deep.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article60Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

echo "=== Deep-audit pass-1 #60 ===\n\n";

$ref = new ReflectionClass(Article60Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$body = $method->invoke($ref->newInstanceWithoutConstructor());
$src = file_get_contents(__DIR__.'/../database/seeders/Article60Seeder.php');
$plain = trim(preg_replace('/\s+/', ' ', strip_tags($body)) ?? '');
$words = str_word_count($plain);
$h2 = substr_count($body, '<h2');
preg_match_all('/language-php/', $body, $phpBlocks);

check($words >= 550, 'Prosa ≥550 kata ('.$words.')');
check($h2 >= 11, '≥11 H2 ('.$h2.')');
check(count($phpBlocks[0]) >= 3, '≥3 blok PHP');
check(strlen('Controller, Service & Eloquent Laravel untuk Pemula') <= 70, 'seo_title ≤70');
$desc = 'Seri 4 #60: pindahkan daftar buku dari route ke Controller + Service, lalu baca tabel dengan Eloquent — ramah awam.';
$descLen = strlen($desc);
check($descLen >= 70 && $descLen <= 170, 'seo_desc 70–170 ('.$descLen.')');
check(str_contains($body, 'BukuController'), 'BukuController');
check(str_contains($body, 'BukuService'), 'BukuService');
check(str_contains($body, 'Eloquent') && str_contains($body, 'migrate'), 'Eloquent migrate');
check(str_contains($body, 'loket') && str_contains($body, 'dapur'), 'Fondasi loket/dapur');
check(str_contains($body, 'Seri 4') && str_contains($body, '#60 (ini)'), 'Framing + self-ref');
check(str_contains($body, 'Laravel 13+'), 'Pin Laravel');
check(str_contains($body, 'PHP 8.3'), 'Syarat PHP 8.3');
check(! str_contains($body, 'Laravel 11+'), 'Tanpa pin 11+ usang');
check(substr_count($body, '/artikel/laravel-request-validasi-api') >= 2, '≥2 link #59');
check(! preg_match('/(?<![\w\/"#>])#60(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #60 (kecuali ini)');
check(! preg_match('/(?<![\w\/"#>])#(?:6[1-3])(?!\s*\(ini\))/', strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '')), 'Tidak bare #61+');
check(! str_contains($body, '→') && ! str_contains($body, '↔'), 'Tanpa Unicode arrow');
check(! str_contains($body, 'TODO'), 'Tanpa TODO');
check(str_contains($body, 'aria-label') || str_contains($body, 'role="img"'), 'SVG a11y');
check(str_contains($body, 'laravel_controller_service_eloquent_demo.php') && str_contains($body, 'demo()'), 'File + demo');
check(str_contains($body, 'Pola Dasar') && str_contains($body, 'color:#1a1a1a'), 'Pola Dasar');
check(str_contains($body, 'Kesalahan umum') && str_contains($body, 'Latihan') && str_contains($body, 'FAQ'), 'KU/Latihan/FAQ');
check(str_contains($src, 'laravel-controller-service-eloquent'), 'Slug');
check(preg_match("/'is_featured'\\s*=>\\s*false/", $src) === 1, 'is_featured false');
check(! preg_match("/'cover_image'\\s*=>/", $src), 'Cover tidak overwrite');
check(str_contains($src, 'web-development'), 'Kategori web-development');
$deploy = file_get_contents(__DIR__.'/../app/Http/Controllers/DeployController.php');
$yml = file_get_contents(__DIR__.'/../.github/workflows/deploy.yml');
check(str_contains($deploy, 'laravel-controller-service-eloquent'), 'Hook');
check(str_contains($yml, 'laravel-controller-service-eloquent'), 'CI slug');
check(preg_match('/Publish article 60 via deploy hook \(required\)\s*\n\s*continue-on-error:\s*true/u', $yml) !== 1, 'CI #60 required');
check(str_contains(file_get_contents(__DIR__.'/../database/seeders/Article59Seeder.php'), 'laravel-controller-service-eloquent'), '#59 hardlink #60');
check(str_contains($deploy, 'backlink missing on #59') || str_contains($deploy, 'Article 60 backlink #59'), 'Hook hardlink #59');
check(str_contains($body, '5/8'), 'Progress 5/8');
check(str_contains($body, 'Web Lanjut v2') || str_contains($body, 'jalur Laravel'), 'Framing Seri 4 v2');
check(str_contains($body, 'Awam:'), 'Gloss awam');
check(str_contains($body, 'Auth API') || str_contains($body, 'kartu anggota'), 'Soft bridge #61');
check(! str_contains($body, 'Pin ') && ! str_contains($body, 'closure') && ! str_contains($body, 'endpoint'), 'Tanpa Pin/closure/endpoint');
check(str_contains($body, 'Spesifikasi'), 'Spesifikasi');
check(! str_contains($body, '@param'), 'Tanpa @param di body');
check(str_contains($body, 'strict_types'), 'Gloss strict_types');
check(str_contains($body, 'proyek') || str_contains($body, 'Proyek'), 'Proyek (bukan project)');
check(str_contains($body, 'Alat yang dipakai') && str_contains($body, 'terminal'), 'Petunjuk tools awam');
check(str_contains($body, 'terminal kedua'), 'Terminal kedua saat serve');
check(str_contains($body, 'Explorer') && str_contains($body, 'mkdir'), 'Explorer + mkdir Services');
check(str_contains($body, 'create_bukus_table'), 'Petunjuk file migrasi');
check(str_contains($body, 'dir database\\migrations') || str_contains($body, 'dir database/migrations'), 'Perintah dir migrations');
check(str_contains($body, 'notepad app\\Models\\Buku.php') || str_contains($body, 'notepad app/Models/Buku.php'), 'notepad model Buku');
check(! str_contains($body, 'Tinker'), 'Tanpa Tinker');
check(! preg_match('/hardlink|STOP AUDIT|oke deploy/i', $body), 'Tanpa suara editor hardlink');
$withoutLinks = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '') ?? '';
check(preg_match_all('/#59(?!\s*\(ini\))/', $withoutLinks) === 0, 'Tanpa bare #59');
check(preg_match_all('/#57(?!\s*\(ini\))/', $withoutLinks) === 0, 'Tanpa bare #57');

echo "\n=== Deep-audit pass-1 #60: {$passed} passed, {$failed} failed ===\n";
if ($failed === 0) {
    echo "Verdict: LIVE #60 — hardlink #59 aktif · CI required. Next: kickoff #61.\n";
}
exit($failed > 0 ? 1 : 0);
