<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article67Seeder;

$ref = new ReflectionClass(Article67Seeder::class);
$body = (new ReflectionMethod(Article67Seeder::class, 'body'))->invoke($ref->newInstanceWithoutConstructor());
$bodyEn = (new ReflectionMethod(Article67Seeder::class, 'bodyEn'))->invoke($ref->newInstanceWithoutConstructor());
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
$sanEn = app(ArticleHtmlSanitizer::class)->sanitize($bodyEn);
$prevSlug = 'laravel-policy-otorisasi-api';

$checks = [
    'laravel67resourceArrow' => str_contains($san, 'laravel67resourceArrow'),
    'color:#1a1a1a' => str_contains($san, 'color:#1a1a1a'),
    'demo file' => str_contains($san, 'laravel_api_resource_json_demo.php'),
    'JsonResource' => str_contains($san, 'JsonResource'),
    'PeminjamanResource' => str_contains($san, 'PeminjamanResource'),
    'toArray' => str_contains($san, 'toArray'),
    'demo(' => str_contains($san, 'demo('),
    'Seri 5' => str_contains($san, 'Seri 5'),
    '#67 (ini)' => str_contains($san, '#67 (ini)'),
    '4/7' => str_contains($san, '4/7'),
    'prevSlug' => str_contains($san, $prevSlug),
    'Pola Dasar' => str_contains($san, 'Pola Dasar'),
    'Persiapan' => str_contains($san, 'Persiapan'),
    'notepad Resource path' => str_contains($san, 'notepad app\Http\Resources\PeminjamanResource.php'),
    'rapikan-cek.php' => str_contains($san, 'rapikan-cek.php'),
    'EN #67 this' => str_contains($sanEn, '#67 (this article)'),
    'EN Beginner' => str_contains($sanEn, 'Beginner:'),
    'EN Tools' => str_contains($sanEn, 'Tools used in this article'),
    'EN Preparation' => str_contains($sanEn, 'Preparation'),
    'EN JsonResource' => str_contains($sanEn, 'JsonResource'),
    'EN PeminjamanResource' => str_contains($sanEn, 'PeminjamanResource'),
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
