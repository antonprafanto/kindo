<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article64Seeder;

$ref = new ReflectionClass(Article64Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__ . '/../database/seeders/Article64Seeder.php');
$a63 = file_get_contents(__DIR__ . '/../database/seeders/Article63Seeder.php');

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;
    echo ($ok ? 'OK    ' : 'FAIL  ') . $label . PHP_EOL;
    $ok ? $pass++ : $fail++;
}

$enPlain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $en) ?? '');
$idPlain = strip_tags(preg_replace('/<pre\b[^>]*>.*?<\/pre>/is', '', $id) ?? '');

check('H2 parity', substr_count($en, '<h2') === substr_count($id, '<h2'));
check('pre parity', substr_count($en, '<pre') === substr_count($id, '<pre'));
check('Beginner count >= 6', substr_count($en, 'Beginner:') >= 6);
check('Awam count mirrored roughly', substr_count($id, 'Awam:') >= 6);
check('Tools used section', str_contains($en, 'Tools used in this article'));
check('Preparation section', str_contains($en, 'Preparation'));
check('Explorer', str_contains($en, 'Explorer'));
check('Laragon + XAMPP', str_contains($en, 'Laragon') && str_contains($en, 'XAMPP'));
check('notepad model path', str_contains($en, 'notepad app\Models\Anggota.php'));
check('Install-from-scratch', str_contains($en, 'Install-from-scratch'));
check('one terminal enough', str_contains($en, 'one terminal is actually enough'));
check('second terminal explained', str_contains($en, 'second terminal'));
check('php file syntax test', str_contains($en, 'php relasi-dasar.php') || str_contains($en, 'php nama-file.php'));
check('make:model commands', str_contains($en, 'php artisan make:model Anggota -m') && str_contains($en, 'php artisan make:model Peminjaman -m'));
check('#64 this article', str_contains($en, '#64 (this article)'));
check('belongsTo + hasMany', str_contains($en, 'belongsTo') && str_contains($en, 'hasMany'));
check('hardlink #63 has slug', str_contains($a63, 'laravel-eloquent-relasi-peminjaman'));
check('Seeder title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));

foreach (['Pendahuluan', 'Persiapan', 'Kesalahan umum', 'Kesimpulan', 'Awam:', 'Alat yang dipakai'] as $w) {
    check('No residual prose: ' . $w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>' . $w));
}

preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $en, $h);
$enH2 = array_map(fn ($t) => trim(html_entity_decode(strip_tags($t))), $h[1]);
$indoH2Hints = 0;
foreach ($enH2 as $t) {
    if (preg_match('/\b(Pendahuluan|Persiapan|Kesalahan|Kesimpulan|Istilah|Spesifikasi fitur|Masuk ke Laravel)\b/u', $t)) {
        $indoH2Hints++;
        echo "WARN  Indo-ish H2: {$t}\n";
    }
}
check('EN H2 titles look English (0 Indo leftovers)', $indoH2Hints === 0);
echo "EN H2s:\n";
foreach ($enH2 as $i => $t) {
    echo '  ' . ($i + 1) . '. ' . $t . PHP_EOL;
}

echo PHP_EOL . "{$pass} pass / {$fail} fail" . PHP_EOL;
exit($fail > 0 ? 1 : 0);
