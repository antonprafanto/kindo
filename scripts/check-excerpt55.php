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
echo str_contains($ex, '#55 (ini)') ? "OK has #55 in DB excerpt\n" : "FAIL no #55 in DB excerpt\n";
$card = \Illuminate\Support\Str::limit($ex, \App\Support\ArticleExcerpt::CARD_PREVIEW_LIMIT);
echo "Card preview: {$card}\n";
echo \App\Support\ArticleExcerpt::markerVisibleInCardPreview($ex) ? "OK visible on card\n" : "FAIL hidden on card (100 char limit)\n";
