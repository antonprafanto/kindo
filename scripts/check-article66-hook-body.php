<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article66Seeder;

$ref = new ReflectionClass(Article66Seeder::class);
$body = (new ReflectionMethod(Article66Seeder::class, 'body'))->invoke($ref->newInstanceWithoutConstructor());
$bodyEn = (new ReflectionMethod(Article66Seeder::class, 'bodyEn'))->invoke($ref->newInstanceWithoutConstructor());
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
$sanEn = app(ArticleHtmlSanitizer::class)->sanitize($bodyEn);
$prevSlug = 'laravel-pagination-filter-pencarian';

$checks = [
    'laravel66policyArrow' => str_contains($san, 'laravel66policyArrow'),
    'color:#1a1a1a' => str_contains($san, 'color:#1a1a1a'),
    'demo file' => str_contains($san, 'laravel_policy_otorisasi_api_demo.php'),
    'authorize' => str_contains($san, 'authorize'),
    'PeminjamanPolicy' => str_contains($san, 'PeminjamanPolicy'),
    '403' => str_contains($san, '403'),
    'demo(' => str_contains($san, 'demo('),
    'Seri 5' => str_contains($san, 'Seri 5'),
    '#66 (ini)' => str_contains($san, '#66 (ini)'),
    '3/7' => str_contains($san, '3/7'),
    'prevSlug' => str_contains($san, $prevSlug),
    'Pola Dasar' => str_contains($san, 'Pola Dasar'),
    'Persiapan' => str_contains($san, 'Persiapan'),
    'notepad path' => str_contains($san, 'notepad app\Http\Controllers\PeminjamanController.php'),
    'izin-cek.php' => str_contains($san, 'izin-cek.php'),
    'EN #66 this' => str_contains($sanEn, '#66 (this article)'),
    'EN Beginner' => str_contains($sanEn, 'Beginner:'),
    'EN Tools' => str_contains($sanEn, 'Tools used in this article'),
    'EN Preparation' => str_contains($sanEn, 'Preparation'),
    'EN authorize' => str_contains($sanEn, 'authorize'),
    'EN PeminjamanPolicy' => str_contains($sanEn, 'PeminjamanPolicy'),
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
