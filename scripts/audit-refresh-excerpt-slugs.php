<?php

/**
 * Pastikan slug di RefreshLaravelSeriesExcerptsSeeder cocok dengan seeder artikel.
 * Usage: php scripts/audit-refresh-excerpt-slugs.php
 */

$slugs = [
    'mengenal-oop-cara-berpikir-dengan-objek-php' => 53,
    'oop-php-property-method-constructor' => 54,
    'oop-php-visibility-composition' => 55,
    'laravel-instalasi-proyek-pertama' => 56,
    'laravel-struktur-env-artisan' => 57,
    'laravel-routing-json-perpustakaan-api' => 58,
    'laravel-request-validasi-api' => 59,
    'laravel-controller-service-eloquent' => 60,
    'laravel-auth-api-dasar' => 61,
    'capstone-api-perpustakaan-laravel' => 62,
    'laravel-crud-api-buku-ubah-hapus' => 63,
    'laravel-eloquent-relasi-peminjaman' => 64,
    'laravel-pagination-filter-pencarian' => 65,
];

$failed = 0;
foreach ($slugs as $slug => $num) {
    $seeder = __DIR__."/../database/seeders/Article{$num}Seeder.php";
    if (! is_readable($seeder)) {
        echo "FAIL missing seeder Article{$num}Seeder.php\n";
        $failed++;

        continue;
    }
    $content = file_get_contents($seeder);
    if (! str_contains($content, "'{$slug}'") && ! str_contains($content, "\"{$slug}\"")) {
        echo "FAIL slug mismatch Article{$num}Seeder vs refresh list: {$slug}\n";
        $failed++;

        continue;
    }
    echo "OK #{$num} {$slug}\n";
}

echo $failed === 0 ? "\nAll refresh excerpt slugs OK\n" : "\n{$failed} failed\n";
exit($failed > 0 ? 1 : 0);
