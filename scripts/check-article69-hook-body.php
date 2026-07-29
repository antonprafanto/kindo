<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article69Seeder;

$ref = new ReflectionClass(Article69Seeder::class);
$body = (new ReflectionMethod(Article69Seeder::class, 'body'))->invoke($ref->newInstanceWithoutConstructor());
$bodyEn = (new ReflectionMethod(Article69Seeder::class, 'bodyEn'))->invoke($ref->newInstanceWithoutConstructor());
$san = app(ArticleHtmlSanitizer::class)->sanitize($body);
$sanEn = app(ArticleHtmlSanitizer::class)->sanitize($bodyEn);
$prevSlug = 'laravel-feature-test-api';

$checks = [
    'laravel69rateArrow' => str_contains($san, 'laravel69rateArrow'),
    'color:#1a1a1a' => str_contains($san, 'color:#1a1a1a'),
    'demo file' => str_contains($san, 'laravel_rate_limiting_api_demo.php'),
    'RateLimiter' => str_contains($san, 'RateLimiter'),
    'throttle' => str_contains($san, 'throttle'),
    '429' => str_contains($san, '429'),
    'demo(' => str_contains($san, 'demo('),
    'Seri 5' => str_contains($san, 'Seri 5'),
    '#69 (ini)' => str_contains($san, '#69 (ini)'),
    '6/7' => str_contains($san, '6/7'),
    'prevSlug' => str_contains($san, $prevSlug),
    'Pola Dasar' => str_contains($san, 'Pola Dasar'),
    'Persiapan' => str_contains($san, 'Persiapan'),
    'notepad AppServiceProvider path' => str_contains($san, 'notepad app\Providers\AppServiceProvider.php'),
    'batas-cek.php' => str_contains($san, 'batas-cek.php'),
    'curl.exe' => str_contains($san, 'curl.exe'),
    'EN #69 this' => str_contains($sanEn, '#69 (this article)'),
    'EN Beginner' => str_contains($sanEn, 'Beginner:'),
    'EN Tools' => str_contains($sanEn, 'Tools used in this article'),
    'EN Preparation' => str_contains($sanEn, 'Preparation'),
    'EN RateLimiter' => str_contains($sanEn, 'RateLimiter'),
    'EN throttle' => str_contains($sanEn, 'throttle'),
    'EN Capstone soft' => str_contains($sanEn, 'Capstone') && ! str_contains($sanEn, 'capstone-pinjam-kembali-laravel'),
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
