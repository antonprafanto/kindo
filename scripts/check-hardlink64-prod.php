<?php

$html = file_get_contents(__DIR__.'/../storage/app/prod64.html');
if ($html === false || $html === '') {
    fwrite(STDERR, "Missing prod64.html\n");
    exit(1);
}

$needlesId = [
    'Berikutnya alami: <strong>Pagination, Filter &amp; Pencarian</strong> untuk daftar slip pinjam yang makin panjang.',
    'Setelah ini, daftar pinjam panjang akan jauh lebih mudah dibaca dan diolah.',
    'Berikutnya: <strong>Pagination, Filter &amp; Pencarian</strong>.</p>',
];

foreach ($needlesId as $n) {
    echo (str_contains($html, $n) ? 'MATCH' : 'MISS')." ID\n";
}

$target = 'laravel-pagination-filter-pencarian';
$patched = $html;
foreach ([
    ['Berikutnya alami: <strong>Pagination, Filter &amp; Pencarian</strong> untuk daftar slip pinjam yang makin panjang.', 'Berikutnya alami: <a href="/artikel/'.$target.'">Pagination, Filter &amp; Pencarian (#65)</a> untuk daftar slip pinjam yang makin panjang.'],
    ['Setelah ini, daftar pinjam panjang akan jauh lebih mudah dibaca dan diolah.', 'Setelah ini, lanjut ke <a href="/artikel/'.$target.'">Pagination, Filter &amp; Pencarian (#65)</a> supaya daftar pinjam panjang terasa rapi dan mudah diolah.'],
    ['Berikutnya: <strong>Pagination, Filter &amp; Pencarian</strong>.</p>', 'Berikutnya: <a href="/artikel/'.$target.'">Pagination, Filter &amp; Pencarian (#65)</a>.</p>'],
] as [$from, $to]) {
    $patched = str_replace($from, $to, $patched);
}

$count = substr_count($patched, $target);
echo "hardlink count after patch: {$count}\n";
exit($count >= 3 ? 0 : 1);
