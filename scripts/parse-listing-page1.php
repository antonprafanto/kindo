<?php

$html = file_get_contents(__DIR__.'/../storage/app/prod-list-id.html');
preg_match_all(
    '/<a href="[^"]*\/artikel\/([^"]+)"[^>]*class="theme-heading[^"]*"[^>]*>\s*(.*?)\s*<\/a>.*?<p class="text-sm[^"]*">(.*?)<\/p>.*?(\d{1,2} \w{3} \d{4})/s',
    $html,
    $m,
    PREG_SET_ORDER
);

echo "=== Page 1 cards (parsed) ===\n";
foreach ($m as $i => $row) {
    $slug = $row[1];
    $title = trim(strip_tags(html_entity_decode($row[2])));
    $excerpt = trim(strip_tags(html_entity_decode($row[3])));
    $date = $row[4];
    $num = '—';
    if (preg_match('/#(\d+)\s*\(ini\)/', $excerpt, $n)) {
        $num = '#'.$n[1];
    }
    echo ($i + 1).". {$date} · {$num} · {$title} · {$slug}\n";
}
