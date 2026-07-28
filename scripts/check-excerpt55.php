<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Support\ArticleExcerpt;
use Database\Seeders\Article55Seeder;

$ref = new ReflectionClass(Article55Seeder::class);
$body = (new ReflectionMethod(Article55Seeder::class, 'body'))->invoke($ref->newInstanceWithoutConstructor());
$ex = ArticleExcerpt::fromHtml($body);
echo "len=".strlen($ex)."\n";
echo $ex."\n";
echo str_contains($ex, '#55 (ini)') ? "OK has #55\n" : "FAIL no #55 in auto excerpt\n";
