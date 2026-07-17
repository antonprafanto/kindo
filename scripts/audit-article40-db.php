<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\Artisan;

Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\Article40Seeder', '--force' => true]);

$a = Article::where('slug', 'mengenal-oop-cara-berpikir-dengan-objek-python')->first();
if (! $a) {
    echo "MISSING\n";
    exit(1);
}

$b = $a->body;
$checks = [
    'svg' => str_contains($b, '<svg'),
    'dash' => str_contains($b, 'stroke-dasharray'),
    'flex' => str_contains($b, 'flex-shrink'),
    'orange' => str_contains($b, 'oop40ArrowOrange'),
    'figcaption' => str_contains($b, 'figcaption'),
    'cat18' => str_contains($b, '(#18)'),
    'cat39' => str_contains($b, '(#39)'),
];

foreach ($checks as $k => $ok) {
    echo ($ok ? 'Y' : 'N')." {$k}\n";
}
echo 'h2='.substr_count($b, '<h2')."\n";
echo 'len='.strlen($b)."\n";

$failed = count(array_filter($checks, fn ($v) => ! $v));
exit($failed > 0 ? 1 : 0);
