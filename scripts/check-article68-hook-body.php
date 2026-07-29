<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article68Seeder;

$ref = new ReflectionClass(Article68Seeder::class);
$body = (new ReflectionMethod(Article68Seeder::class, 'body'))->invoke($ref->newInstanceWithoutConstructor());
$bodyEn = (new ReflectionMethod(Article68Seeder::class, 'bodyEn'))->invoke($ref->newInstanceWithoutConstructor());
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
$sanEn = app(ArticleHtmlSanitizer::class)->sanitize($bodyEn);
$prevSlug = 'laravel-api-resource-json';

$checks = [
    'laravel68testArrow' => str_contains($san, 'laravel68testArrow'),
    'color:#1a1a1a' => str_contains($san, 'color:#1a1a1a'),
    'demo file' => str_contains($san, 'laravel_feature_test_api_demo.php'),
    'assertJson' => str_contains($san, 'assertJson'),
    'assertStatus' => str_contains($san, 'assertStatus'),
    'demo(' => str_contains($san, 'demo('),
    'Seri 5' => str_contains($san, 'Seri 5'),
    '#68 (ini)' => str_contains($san, '#68 (ini)'),
    '5/7' => str_contains($san, '5/7'),
    'prevSlug' => str_contains($san, $prevSlug),
    'Pola Dasar' => str_contains($san, 'Pola Dasar'),
    'Persiapan' => str_contains($san, 'Persiapan'),
    'notepad Feature path' => str_contains($san, 'notepad tests\Feature\PeminjamanResourceTest.php'),
    'uji-cek.php' => str_contains($san, 'uji-cek.php'),
    'php artisan test' => str_contains($san, 'php artisan test') || str_contains($san, 'vendor/bin/phpunit'),
    'EN #68 this' => str_contains($sanEn, '#68 (this article)'),
    'EN Beginner' => str_contains($sanEn, 'Beginner:'),
    'EN Tools' => str_contains($sanEn, 'Tools used in this article'),
    'EN Preparation' => str_contains($sanEn, 'Preparation'),
    'EN assertJson' => str_contains($sanEn, 'assertJson'),
    'EN Feature Test' => str_contains($sanEn, 'Feature Test'),
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
