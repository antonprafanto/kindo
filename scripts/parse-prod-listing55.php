<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Str;

$url = $argv[1] ?? 'https://kodingindonesia.com/artikel';
$html = file_get_contents($url);
if ($html === false) {
    fwrite(STDERR, "Failed to fetch {$url}\n");
    exit(1);
}

preg_match_all(
    '/<a href="[^"]*\/artikel\/([^"]+)"[^>]*class="theme-heading[^"]*"[^>]*>\s*(.*?)\s*<\/a>.*?<p class="text-sm[^"]*">(.*?)<\/p>.*?(\d{1,2} \w{3} \d{4})/s',
    $html,
    $m,
    PREG_SET_ORDER
);

$found = false;
foreach ($m as $i => $row) {
    if ($row[1] !== 'oop-php-visibility-composition') {
        continue;
    }
    $found = true;
    $excerpt = trim(strip_tags(html_entity_decode($row[3])));
    $card = Str::limit($excerpt, 100);
    echo 'Position: '.($i + 1)."\n";
    echo "Excerpt: {$excerpt}\n";
    echo "Card: {$card}\n";
    echo str_contains($excerpt, '#55 (ini)') ? "Has #55 (ini) in DB: YES\n" : "Has #55 (ini) in DB: NO\n";
    echo str_contains($card, '#55 (ini)') ? "Has #55 (ini) on card: YES\n" : "Has #55 (ini) on card: NO\n";
}

if (! $found) {
    echo "Article 55 NOT on page 1\n";
    exit(2);
}
