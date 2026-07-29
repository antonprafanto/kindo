<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article70Seeder;

$ref = new ReflectionClass(Article70Seeder::class);
$body = (new ReflectionMethod(Article70Seeder::class, 'body'))->invoke($ref->newInstanceWithoutConstructor());
$bodyEn = (new ReflectionMethod(Article70Seeder::class, 'bodyEn'))->invoke($ref->newInstanceWithoutConstructor());
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
$sanEn = app(ArticleHtmlSanitizer::class)->sanitize($bodyEn);
$prevSlug = 'laravel-rate-limiting-api';

$checks = [
    'laravel70capArrow' => str_contains($san, 'laravel70capArrow'),
    'color:#1a1a1a' => str_contains($san, 'color:#1a1a1a'),
    'demo file' => str_contains($san, 'capstone_pinjam_kembali_laravel_demo.php'),
    'alur-cek.php' => str_contains($san, 'alur-cek.php'),
    'authorize' => str_contains($san, 'authorize'),
    'throttle:pinjam' => str_contains($san, 'throttle:pinjam'),
    'demo(' => str_contains($san, 'demo('),
    'Seri 5' => str_contains($san, 'Seri 5'),
    '#70 (ini)' => str_contains($san, '#70 (ini)'),
    '7/7' => str_contains($san, '7/7'),
    'prevSlug' => str_contains($san, $prevSlug),
    'Pola Dasar' => str_contains($san, 'Pola Dasar'),
    'Persiapan' => str_contains($san, 'Persiapan'),
    'notepad PeminjamanController path' => str_contains($san, 'notepad app\Http\Controllers\PeminjamanController.php'),
    'tests\Feature' => str_contains($san, 'tests\Feature'),
    'curl.exe' => str_contains($san, 'curl.exe'),
    'EN #70 this' => str_contains($sanEn, '#70 (this article)'),
    'EN Beginner' => str_contains($sanEn, 'Beginner:'),
    'EN Tools' => str_contains($sanEn, 'Tools used in this article'),
    'EN Preparation' => str_contains($sanEn, 'Preparation'),
    'EN authorize' => str_contains($sanEn, 'authorize'),
    'EN throttle' => str_contains($sanEn, 'throttle:pinjam'),
    'EN Mobile Devices soft' => str_contains($sanEn, 'Mobile Devices') && ! preg_match('/\/artikel\/[^"]*mobile/i', $sanEn),
    'EN 7/7 complete' => str_contains($sanEn, '7/7 — complete'),
    'link #64' => str_contains($san, '/artikel/laravel-eloquent-relasi-peminjaman'),
    'Piranti Bergerak soft' => str_contains($san, 'Piranti Bergerak'),
];

$failed = 0;
foreach ($checks as $k => $v) {
    echo ($v ? 'OK' : 'FAIL')." {$k}\n";
    if (! $v) {
        $failed++;
    }
}

echo 'body len raw='.strlen($body).' san='.strlen($san).PHP_EOL;
exit($failed > 0 ? 1 : 0);
