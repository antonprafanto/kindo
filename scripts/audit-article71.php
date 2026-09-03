<?php

/**
 * Audit utama #71 — Modul M-01: Pintu Masuk IoT (Kurikulum Master v8.0 Definitive).
 * Usage: php scripts/audit-article71.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Database\Seeders\Article71Seeder;

$passed = 0;
$failed = 0;

function check(bool $ok, string $label): void
{
    global $passed, $failed;
    echo ($ok ? '✓' : '✗')." {$label}\n";
    $ok ? $passed++ : $failed++;
}

$slug = 'fullstack-iot-pintu-masuk-iot';

echo "=== Audit Artikel #71 (M-01) — Pintu Masuk IoT ===\n\n";

$ref = new ReflectionClass(Article71Seeder::class);
$method = $ref->getMethod('body');
$method->setAccessible(true);
$enMethod = $ref->getMethod('bodyEn');
$enMethod->setAccessible(true);
$instance = $ref->newInstanceWithoutConstructor();
$body = $method->invoke($instance);
$bodyEn = $enMethod->invoke($instance);
$src = file_get_contents(__DIR__.'/../database/seeders/Article71Seeder.php');

// 1. Identitas & CMS
check(str_contains($body, '#71 (ini)'), 'Self-ref ID: #71 (ini)');
check(str_contains($bodyEn, '#71 (this article)'), 'Self-ref EN: #71 (this article)');
check(str_contains($src, "'slug' => \$slug") || str_contains($src, $slug), 'Slug match');
check(str_contains($src, "'status'             => 'draft'"), 'Status draft (safe)');
check(str_contains($src, "'published_at'       => null"), 'Published at null (draft)');

// 2. SEO Limits
preg_match("/'seo_title'\s*=>\s*'([^']+)'/", $src, $mSeoTitle);
preg_match("/'seo_title_en'\s*=>\s*'([^']+)'/", $src, $mSeoTitleEn);
preg_match("/'seo_description'\s*=>\s*'([^']+)'/", $src, $mSeoDesc);
preg_match("/'seo_description_en'\s*=>\s*'([^']+)'/", $src, $mSeoDescEn);

$seoTitle = $mSeoTitle[1] ?? '';
$seoTitleEn = $mSeoTitleEn[1] ?? '';
$seoDesc = $mSeoDesc[1] ?? '';
$seoDescEn = $mSeoDescEn[1] ?? '';

check(mb_strlen($seoTitle) <= 60 && mb_strlen($seoTitle) >= 20, "SEO Title ID length: ".mb_strlen($seoTitle)." (<=60)");
check(mb_strlen($seoTitleEn) <= 60 && mb_strlen($seoTitleEn) >= 20, "SEO Title EN length: ".mb_strlen($seoTitleEn)." (<=60)");
check(mb_strlen($seoDesc) >= 120 && mb_strlen($seoDesc) <= 160, "SEO Desc ID length: ".mb_strlen($seoDesc)." (120-160)");
check(mb_strlen($seoDescEn) >= 120 && mb_strlen($seoDescEn) <= 160, "SEO Desc EN length: ".mb_strlen($seoDescEn)." (120-160)");

// 3. Struktur Konten & Pedagogi
check(str_contains($body, 'Tools-First') || str_contains($body, 'Daftar Alat untuk Modul M-01'), 'Section Tools-First ID');
check(str_contains($bodyEn, 'Tools-First') || str_contains($bodyEn, 'Toolkit for Module M-01'), 'Section Tools-First EN');
check(str_contains($body, 'Empat pilar utama IoT') && str_contains($body, 'Panca Indra') && str_contains($body, 'Otak Pemroses'), '4 Pilar IoT ID');
check(str_contains($bodyEn, 'Four core pillars of IoT') && str_contains($bodyEn, 'Sensory Organs') && str_contains($bodyEn, 'Central Processing Brain'), '4 Pillars IoT EN');
check(str_contains($body, 'Smart Study Desk Station') || str_contains($body, 'Stasiun Pintar Meja Belajar'), 'Studi kasus meja belajar ID');
check(str_contains($bodyEn, 'Smart Study Desk Station'), 'Studi kasus meja belajar EN');

// 4. Diagram Visual SVG Standar & Sitasi Sumber
check(str_contains($body, 'background:#F5F5F0'), 'Figure background #F5F5F0 ID');
check(str_contains($bodyEn, 'background:#F5F5F0'), 'Figure background #F5F5F0 EN');
check(str_contains($body, 'viewBox="0 0 760 320"') && str_contains($body, 'viewBox="0 0 760 260"'), '2 SVG diagrams ID');
check(str_contains($bodyEn, 'viewBox="0 0 760 320"') && str_contains($bodyEn, 'viewBox="0 0 760 260"'), '2 SVG diagrams EN');
check(str_contains($body, '(Sumber: Desain Orisinal Tim Kurikulum Koding Indonesia'), 'Sitasi sumber gambar ID');
check(str_contains($bodyEn, '(Source: Original Design by Koding Indonesia Curriculum Team'), 'Image source attribution EN');

// 5. Hands-on Console Test (Tools-First Execution)
check(str_contains($body, 'console.log("Halo Dunia IoT Koding Indonesia!");'), 'Interactive console test ID');
check(str_contains($bodyEn, 'console.log("Hello Full Stack IoT World!");'), 'Interactive console test EN');
check(str_contains($body, '<code class="language-javascript">') && str_contains($bodyEn, '<code class="language-javascript">'), 'Syntax highlighting class language-javascript');

// 6. Micro-Quiz (3 Soal Interaktif Ala Dicoding dengan Clickable Options & Hidden Explanation)
check(substr_count($body, 'Soal ') === 3 && str_contains($body, 'Soal 1 dari 3'), 'Question badges ID');
check(substr_count($bodyEn, 'Question ') === 3 && str_contains($bodyEn, 'Question 1 of 3'), 'Question badges EN');
check(substr_count($body, 'class="fsiot-quiz"') === 3, '3 Interactive quiz components ID');
check(substr_count($bodyEn, 'class="fsiot-quiz"') === 3, '3 Interactive quiz components EN');
check(substr_count($body, 'class="fsiot-quiz-opt"') === 12, '12 Clickable option cards ID');
check(substr_count($bodyEn, 'class="fsiot-quiz-opt"') === 12, '12 Clickable option cards EN');
check(substr_count($body, 'data-correct="true"') === 3, '3 Correct answers marked ID');
check(substr_count($bodyEn, 'data-correct="true"') === 3, '3 Correct answers marked EN');
check(substr_count($body, 'class="fsiot-quiz-explanation"') === 3, '3 Hidden explanation cards ID');
check(substr_count($bodyEn, 'class="fsiot-quiz-explanation"') === 3, '3 Hidden explanation cards EN');
check(substr_count($body, 'Kunci Jawaban:') === 3, '3 Kunci & Pembahasan ID');
check(substr_count($bodyEn, 'Correct Answer:') === 3, '3 Kunci & Pembahasan EN');

// 7. Paritas Heading
$h2Id = substr_count($body, '<h2');
$h2En = substr_count($bodyEn, '<h2');
check($h2Id === $h2En && $h2Id >= 6, "H2 parity ID={$h2Id} EN={$h2En} (>=6)");

// 8. EYD & Standar Bahasa
check(str_contains($body, 'kelembapan'), 'EYD: kelembapan (bukan kelembaban)');
check(str_contains($body, 'sakelar'), 'EYD: sakelar (bukan saklar)');
check(str_contains($body, 'praktis'), 'EYD: praktis');
check(str_contains($body, 'analisis'), 'EYD: analisis');
check(str_contains($body, 'mengubahnya'), 'EYD: mengubah (bukan merubah)');

// 9. Anti-Bare #N Check
$plainLinked = strip_tags(preg_replace('/<a\b[^>]*>.*?<\/a>/is', '', $body) ?? '');
check(! preg_match('/(?<![\w\/"#>])#71(?!\s*\(ini\))/', $plainLinked), 'No bare #71');

// 10. ArticleHtmlSanitizer Check (Post-Sanitize Verification)
$sanitizer = app(\App\Services\ArticleHtmlSanitizer::class);
$sanitizedId = $sanitizer->sanitize($body);
$sanitizedEn = $sanitizer->sanitize($bodyEn);
check(str_contains($sanitizedId, 'viewBox') && str_contains($sanitizedId, '<svg'), 'Sanitizer preserves SVG in ID');
check(str_contains($sanitizedEn, 'viewBox') && str_contains($sanitizedEn, '<svg'), 'Sanitizer preserves SVG in EN');
check(str_contains($sanitizedId, 'Kunci Jawaban:') && str_contains($sanitizedId, 'Pembahasan:'), 'Sanitizer preserves Micro-Quiz card in ID');
check(str_contains($sanitizedEn, 'Correct Answer:') && str_contains($sanitizedEn, 'Explanation:'), 'Sanitizer preserves Micro-Quiz card in EN');
check(str_contains($sanitizedId, 'background:#F5F5F0'), 'Sanitizer preserves figure style in ID');
check(str_contains($sanitizedEn, 'background:#F5F5F0'), 'Sanitizer preserves figure style in EN');
check(str_contains($sanitizedId, 'console.log'), 'Sanitizer preserves console code in ID');
check(str_contains($sanitizedEn, 'console.log'), 'Sanitizer preserves console code in EN');
check(! str_contains($sanitizedId, '<p style='), 'Sanitizer strips any disallowed p style in ID');
check(! str_contains($sanitizedEn, '<p style='), 'Sanitizer strips any disallowed p style in EN');
check(str_contains($sanitizedId, 'class="fsiot-quiz"') && str_contains($sanitizedId, 'data-option="B"'), 'Sanitizer preserves fsiot-quiz & data-option in ID');
check(str_contains($sanitizedEn, 'class="fsiot-quiz"') && str_contains($sanitizedEn, 'data-option="B"'), 'Sanitizer preserves fsiot-quiz & data-option in EN');

echo "\n--- HASIL AUDIT ---\n";
echo "Passed: {$passed}\n";
echo "Failed: {$failed}\n";

if ($failed > 0) {
    exit(1);
}
echo "SEMUA AUDIT ARTIKEL #71 LOLOS 100%! ✓\n";
