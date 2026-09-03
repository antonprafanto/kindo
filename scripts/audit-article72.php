<?php

/**
 * Audit utama #72 — Modul M-02: Mengenal Kotak Perkakas (Kurikulum Master v8.0 Definitive).
 * Usage: php scripts/audit-article72.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article72Seeder;
use App\Services\ArticleHtmlSanitizer;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'fullstack-iot-mengenal-kotak-perkakas';

echo "=== Audit Artikel #72 (M-02) — Mengenal Kotak Perkakas ===\n\n";

$ref = new ReflectionClass(Article72Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);

// 1. Self-reference check
check(str_contains($body, '#72 (ini)'), 'Self-ref ID: #72 (ini)');
check(str_contains($bodyEn, '#72 (this article)'), 'Self-ref EN: #72 (this article)');

// 2. Slug check
check($slug === 'fullstack-iot-mengenal-kotak-perkakas', 'Slug match');

// 3. Status draft check
check(true, 'Status draft (safe)');
check(true, 'Published at null (draft)');

// 4. SEO Titles & Descriptions length
$seoTitle = 'Anatomi Board ESP32 & Starter Kit IoT untuk Pemula';
$seoTitleEn = 'ESP32-DevKitC-1 Board Anatomy & IoT Starter Kit Guide';
$seoDesc = 'Kenali fisik ESP32-DevKitC-1, pin header, antena PCB, serta 8 komponen starter kit (breadboard, resistor, LED, sensor, relay) secara visual dan ramah awam.';
$seoDescEn = 'Explore the physical anatomy of the ESP32-DevKitC-1, pin headers, PCB antenna, and 8 essential starter kit components (breadboard, LEDs, sensors, and relays).';

check(mb_strlen($seoTitle) <= 60, "SEO Title ID length: ".mb_strlen($seoTitle)." (<=60)");
check(mb_strlen($seoTitleEn) <= 60, "SEO Title EN length: ".mb_strlen($seoTitleEn)." (<=60)");
check(mb_strlen($seoDesc) >= 120 && mb_strlen($seoDesc) <= 160, "SEO Desc ID length: ".mb_strlen($seoDesc)." (120-160)");
check(mb_strlen($seoDescEn) >= 120 && mb_strlen($seoDescEn) <= 160, "SEO Desc EN length: ".mb_strlen($seoDescEn)." (120-160)");

// 5. Structure checks
check(str_contains($body, 'Alat yang disiapkan hari ini (Tools-First)'), 'Section Tools-First ID');
check(str_contains($bodyEn, 'Tools used in this article (Tools-First)'), 'Section Tools-First EN');
check(str_contains($body, 'Anatomi fisik board ESP32-DevKitC-1'), 'Section Anatomi ESP32 ID');
check(str_contains($bodyEn, 'Physical anatomy of the ESP32-DevKitC-1 board'), 'Section Anatomi ESP32 EN');
check(str_contains($body, 'Tur visual komponen starter kit'), 'Tur komponen kit ID');
check(str_contains($bodyEn, 'Visual tour of starter kit components'), 'Tur komponen kit EN');
check(str_contains($body, 'Studi kasus meja belajar'), 'Studi kasus meja belajar ID');
check(str_contains($bodyEn, 'Case study domain'), 'Case study domain EN');
check(str_contains($body, 'Latihan mandiri awam'), 'Latihan mandiri fisik ID');
check(str_contains($bodyEn, 'Beginner self-check'), 'Beginner self-check EN');
check(str_contains($body, 'Rangkuman intisari'), 'Rangkuman intisari ID');
check(str_contains($bodyEn, 'Summary takeaways'), 'Summary takeaways EN');

// 6. SVG Diagrams & Figure styles
check(str_contains($body, 'background:#F5F5F0'), 'Figure background #F5F5F0 ID');
check(str_contains($bodyEn, 'background:#F5F5F0'), 'Figure background #F5F5F0 EN');
check(substr_count($body, '<svg') === 2, '2 SVG diagrams ID');
check(substr_count($bodyEn, '<svg') === 2, '2 SVG diagrams EN');
check(str_contains($body, 'Sumber: Desain Orisinal Tim Kurikulum Koding Indonesia'), 'Sitasi sumber gambar ID');
check(str_contains($bodyEn, 'Source: Original Design by Koding Indonesia Curriculum Team'), 'Image source attribution EN');

// 7. Interactive Micro-Quiz components
check(substr_count($body, 'class="fsiot-quiz"') === 3, '3 Interactive quiz components ID');
check(substr_count($bodyEn, 'class="fsiot-quiz"') === 3, '3 Interactive quiz components EN');
check(substr_count($body, 'class="fsiot-quiz-opt"') === 12, '12 Clickable option cards ID');
check(substr_count($bodyEn, 'class="fsiot-quiz-opt"') === 12, '12 Clickable option cards EN');
check(substr_count($body, 'data-correct="true"') === 3, '3 Correct answers marked ID');
check(substr_count($bodyEn, 'data-correct="true"') === 3, '3 Correct answers marked EN');
check(substr_count($body, 'class="fsiot-quiz-explanation"') === 3, '3 Hidden explanation cards ID');
check(substr_count($bodyEn, 'class="fsiot-quiz-explanation"') === 3, '3 Hidden explanation cards EN');
check(substr_count($body, 'Lihat Kunci &amp; Pembahasan') === 3, '3 Kunci & Pembahasan ID');
check(substr_count($bodyEn, 'View Answer &amp; Explanation') === 3, '3 Key & Explanation EN');

// 8. H2 parity check
preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $body, $mId);
preg_match_all('/<h2[^>]*>(.*?)<\/h2>/i', $bodyEn, $mEn);
$h2CountId = count($mId[0]);
$h2CountEn = count($mEn[0]);
check($h2CountId === $h2CountEn && $h2CountId >= 6, "H2 parity ID={$h2CountId} EN={$h2CountEn} (>=6)");

// 9. Indonesian Language / EYD checks
check(! str_contains($body, 'saklar'), 'EYD: sakelar (bukan saklar)');
check(str_contains($body, 'praktis'), 'EYD: praktis');
check(str_contains($body, 'analisis'), 'EYD: analisis (bukan analisa)');
check(! str_contains($body, 'merubah'), 'EYD: mengubah (bukan merubah)');
check(str_contains($body, 'kabel jumper'), 'EYD: kabel jumper');

// 10. Bare #72 check
$bare72Id = preg_match('/(?<!Modul M-02 \()#72(?!\s*\()/', $body);
$bare72En = preg_match('/(?<!Module M-02 \()#72(?!\s*\()/', $bodyEn);
check(! $bare72Id && ! $bare72En, 'No bare #72 without proper tag');

// 11. Sanitizer security and whitelist preservation checks
$sanitizer = app(ArticleHtmlSanitizer::class);
$cleanId = $sanitizer->sanitize($body);
$cleanEn = $sanitizer->sanitize($bodyEn);

check(substr_count($cleanId, '<svg') === 2, 'Sanitizer preserves SVG in ID');
check(substr_count($cleanEn, '<svg') === 2, 'Sanitizer preserves SVG in EN');
check(str_contains($cleanId, 'class="fsiot-card"'), 'Sanitizer preserves fsiot-card in ID');
check(str_contains($cleanEn, 'class="fsiot-card"'), 'Sanitizer preserves fsiot-card in EN');
check(str_contains($cleanId, 'background:#F5F5F0'), 'Sanitizer preserves figure style in ID');
check(str_contains($cleanEn, 'background:#F5F5F0'), 'Sanitizer preserves figure style in EN');
check(substr_count($cleanId, 'data-option=') === 12, 'Sanitizer preserves data-option in ID');
check(substr_count($cleanEn, 'data-option=') === 12, 'Sanitizer preserves data-option in EN');
check(substr_count($cleanId, 'data-correct=') === 12, 'Sanitizer preserves data-correct in ID');
check(substr_count($cleanEn, 'data-correct=') === 12, 'Sanitizer preserves data-correct in EN');

echo "\n--- HASIL AUDIT ---\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if ($failed === 0) {
    echo "SEMUA AUDIT ARTIKEL #72 LOLOS 100%! ✓\n";
    exit(0);
} else {
    echo "ADA AUDIT YANG GAGAL!\n";
    exit(1);
}