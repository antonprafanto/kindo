<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article65Seeder;

$ref = new ReflectionClass(Article65Seeder::class);
$body = (new ReflectionMethod(Article65Seeder::class, 'body'))->invoke($ref->newInstanceWithoutConstructor());
$bodyEn = (new ReflectionMethod(Article65Seeder::class, 'bodyEn'))->invoke($ref->newInstanceWithoutConstructor());
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
$sanEn = app(ArticleHtmlSanitizer::class)->sanitize($bodyEn);
$prevSlug = 'laravel-eloquent-relasi-peminjaman';

$checks = [
    'laravel65pageArrow' => str_contains($san, 'laravel65pageArrow'),
    'color:#1a1a1a' => str_contains($san, 'color:#1a1a1a'),
    'demo file' => str_contains($san, 'laravel_pagination_filter_pencarian_demo.php'),
    'paginate' => str_contains($san, 'paginate'),
    'array_slice' => str_contains($san, 'array_slice'),
    'demo(' => str_contains($san, 'demo('),
    'Seri 5' => str_contains($san, 'Seri 5'),
    '#65 (ini)' => str_contains($san, '#65 (ini)'),
    '2/7' => str_contains($san, '2/7'),
    'prevSlug' => str_contains($san, $prevSlug),
    'Pola Dasar' => str_contains($san, 'Pola Dasar'),
    'Persiapan' => str_contains($san, 'Persiapan'),
    'notepad path' => str_contains($san, 'notepad app\Http\Controllers\PeminjamanController.php'),
    'EN #65 this' => str_contains($sanEn, '#65 (this article)'),
    'EN Beginner' => str_contains($sanEn, 'Beginner:'),
    'EN Tools' => str_contains($sanEn, 'Tools used in this article'),
    'EN Preparation' => str_contains($sanEn, 'Preparation'),
    'EN notepad' => str_contains($sanEn, 'notepad app\Http\Controllers\PeminjamanController.php'),
    'EN paginate' => str_contains($sanEn, 'paginate'),
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
