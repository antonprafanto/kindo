<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article63Seeder;

$ref = new ReflectionClass(Article63Seeder::class);
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$seeder = $ref->newInstanceWithoutConstructor();
$id = $idM->invoke($seeder);
$en = $enM->invoke($seeder);
$src = file_get_contents(__DIR__ . '/../database/seeders/Article63Seeder.php');
$a62 = file_get_contents(__DIR__ . '/../database/seeders/Article62Seeder.php');

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
check('Beginner count >= 8', substr_count($en, 'Beginner:') >= 8);
check('Awam count mirrored roughly', substr_count($id, 'Awam:') >= 8);
check('Tools used section', str_contains($en, 'Tools used in this article'));
check('How to open a second terminal', str_contains($en, 'How to open a second terminal'));
check('copy text from Windows terminal', str_contains($en, 'how to copy text from the Windows terminal'));
check('curl.exe', str_contains($en, 'curl.exe'));
check('Option A curl.exe', str_contains($en, 'Option A') && str_contains($en, 'curl.exe'));
check('Option B PowerShell', str_contains($en, 'Option B') && str_contains($en, 'PowerShell'));
check('Option C Postman', str_contains($en, 'Option C') && str_contains($en, 'Postman'));
check('Laragon + XAMPP', str_contains($en, 'Laragon') && str_contains($en, 'XAMPP'));
check('notepad tip path', str_contains($en, 'notepad app\\Http\\Controllers\\BukuController.php') || str_contains($en, 'notepad app\\\\Http\\\\Controllers\\\\BukuController.php') || str_contains($en, 'notepad app\Http\Controllers\BukuController.php'));
check('Install-from-scratch', str_contains($en, 'Install-from-scratch'));
check('#63 (this article)', str_contains($en, '#63 (this article)'));
check('PUT/DELETE not from address bar', str_contains($en, 'address bar'));
check('caret ^ vs backtick explained', str_contains($en, '^') && str_contains($en, 'backtick'));
check('Explorer in tools list', stripos($en, 'Explorer') !== false || stripos($en, 'File Explorer') !== false);
check('File Explorer / folder tip ID had', str_contains($id, 'Explorer'));
check('route:list mentioned EN', str_contains($en, 'route:list'));
check('serve restart after bootstrap EN', str_contains($en, 'Ctrl+C') || stripos($en, 'restart') !== false || stripos($en, 'start again') !== false);
check('NOMOR placeholder kept', str_contains($en, 'NOMOR'));
check('Hardlink #62 has slug 3x', substr_count($a62, 'laravel-crud-api-buku-ubah-hapus') >= 3);
check('Seeder title_en + body_en', str_contains($src, "'title_en'") && str_contains($src, "'body_en'"));
check('CI required not kickoff', str_contains(file_get_contents(__DIR__ . '/../.github/workflows/deploy.yml'), 'Publish article 63 via deploy hook (required)'));

// Residual Indonesian headings/phrases that should have been translated
foreach (['Pendahuluan', 'Persiapan —', 'Kesalahan umum', 'Ringkasan</h2>', 'Alat yang dipakai', 'Awam:', 'Opsi A', 'Opsi B', 'Opsi C', 'Cara buka terminal kedua', 'cara salin teks'] as $w) {
    check('No residual prose: ' . $w, ! str_contains($enPlain, $w) && ! str_contains($en, '<h2>' . $w));
}

// Soft: EN H2 list should be English
preg_match_all('/<h2[^>]*>(.*?)<\/h2>/si', $en, $h);
$enH2 = array_map(fn ($t) => trim(html_entity_decode(strip_tags($t))), $h[1]);
$indoH2Hints = 0;
foreach ($enH2 as $t) {
    if (preg_match('/\b(Pendahuluan|Persiapan|Kesalahan|Ringkasan|Istilah|Spesifikasi fitur|Loket|Sambungkan|Uji ubah)\b/u', $t)) {
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
