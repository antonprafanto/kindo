<?php

$list = file_get_contents(__DIR__.'/../storage/app/prod-artikel-list.html');
$html65 = file_get_contents(__DIR__.'/../storage/app/prod65-audit.html');

echo "=== Listing page excerpts (#N) ===\n";
if (preg_match_all('/<h2[^>]*>\s*(.*?)\s*<\/h2>.*?Artikel ini adalah[^#]*#(\d+)\s*\(ini\)/s', $list, $m, PREG_SET_ORDER)) {
    foreach ($m as $row) {
        $title = trim(strip_tags(html_entity_decode($row[1])));
        echo "- {$title} → excerpt says #{$row[2]} (ini)\n";
    }
} else {
    // fallback: looser
    preg_match_all('/#(\d+)\s*\(ini\)/', $list, $nums);
    echo "found ".count($nums[1])." self-refs: ".implode(', ', $nums[1])."\n";
}

echo "\n=== Titles order (page 1) ===\n";
preg_match_all('/<h2[^>]*>\s*(.*?)\s*<\/h2>/s', $list, $titles);
foreach (array_slice($titles[1], 0, 12) as $i => $t) {
    echo ($i + 1).'. '.trim(strip_tags(html_entity_decode($t)))."\n";
}

echo "\n=== #65 body audit ===\n";
$checks = [
    '#65 (ini)' => str_contains($html65, '#65 (ini)'),
    '2/7' => str_contains($html65, '2/7'),
    'curl.exe' => str_contains($html65, 'curl.exe'),
    'Terminal lagi' => str_contains($html65, 'Terminal</em> lagi') || str_contains($html65, 'menu <em>Terminal</em> lagi'),
    'daftar-saring.php' => str_contains($html65, 'daftar-saring.php'),
    'link #64' => str_contains($html65, 'laravel-eloquent-relasi-peminjaman'),
    'Awam:' => substr_count($html65, 'Awam:'),
    'English unavailable banner' => str_contains($html65, 'English version unavailable') || str_contains($html65, 'versi Inggris belum'),
];
foreach ($checks as $k => $v) {
    if (is_int($v)) {
        echo "Awam count: {$v}\n";
    } elseif ($k === 'English unavailable banner') {
        echo ($v ? 'FAIL' : 'OK')." no EN unavailable banner\n";
    } else {
        echo ($v ? 'OK' : 'FAIL')." {$k}\n";
    }
}
