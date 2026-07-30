<?php

require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Services\ArticleHtmlSanitizer;
use Database\Seeders\Article78Seeder;

$ref = new ReflectionClass(Article78Seeder::class);
$s = $ref->newInstanceWithoutConstructor();
$idM = $ref->getMethod('body');
$idM->setAccessible(true);
$enM = $ref->getMethod('bodyEn');
$enM->setAccessible(true);
$id = $idM->invoke($s);
$en = $enM->invoke($s);
$san = $app->make(ArticleHtmlSanitizer::class);
$idS = $san->sanitize($id);
$enS = $san->sanitize($en);

$needles = [
    '#78 (ini)', 'FS-08', 'Analogi air', 'V = I x R', 'kit-led-5mm.jpg', 'kit-resistor.jpg',
    '220', '330', 'fsiot-resistor-calc-root', 'fsiot-electric-checklist', 'FS-09',
    '/belajar/fullstack-iot', 'Tidak ada wiring breadboard',
];
foreach ($needles as $n) {
    echo (str_contains($idS, $n) ? 'OK  ' : 'FAIL')." ID: {$n}\n";
}

$enNeedles = [
    '#78 (this article)', 'Beginner:', 'Water analogy', 'V = I x R',
    'fsiot-resistor-calc-root', 'fsiot-electric-checklist', 'FS-09',
];
foreach ($enNeedles as $n) {
    echo (str_contains($enS, $n) ? 'OK  ' : 'FAIL')." EN: {$n}\n";
}

echo 'seo_title len: '.strlen('Listrik Mini Awam — Hitung Resistor LED 3.3V — Full Stack IoT #78')."\n";
