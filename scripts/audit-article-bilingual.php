<?php

/**
 * Audit: per-article bilingual wiring (title_en/excerpt_en/body_en/seo_*_en).
 * Verifies the Article model's display* accessors fall back correctly, and
 * that public views no longer read the raw ID-only fields directly.
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use Illuminate\Support\Facades\App;

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;
    echo ($ok ? 'OK    ' : 'FAIL  ') . $label . PHP_EOL;
    $ok ? $pass++ : $fail++;
}

// Article WITH full English translation.
$bilingual = new Article([
    'title' => 'Judul ID',
    'title_en' => 'EN Title',
    'excerpt' => 'Ringkasan ID',
    'excerpt_en' => 'EN excerpt',
    'body' => '<p>Isi ID</p>',
    'body_en' => '<p>EN body</p>',
    'seo_title' => 'SEO ID',
    'seo_title_en' => 'SEO EN',
    'seo_description' => 'Deskripsi ID',
    'seo_description_en' => 'EN description',
    'read_time_minutes' => 5,
]);

// Article WITHOUT English translation (older / not-yet-translated article).
$idOnly = new Article([
    'title' => 'Judul ID Saja',
    'excerpt' => 'Ringkasan ID saja',
    'body' => '<p>Isi ID saja</p>',
    'seo_title' => 'SEO ID saja',
    'seo_description' => 'Deskripsi ID saja',
    'read_time_minutes' => 3,
]);

check('has_english true when body_en filled', $bilingual->has_english === true);
check('has_english false when body_en empty', $idOnly->has_english === false);

App::setLocale('id');
check('ID locale: display_title stays ID even if EN exists', $bilingual->display_title === 'Judul ID');
check('ID locale: display_body stays ID', $bilingual->display_body === '<p>Isi ID</p>');

App::setLocale('en');
check('EN locale: display_title uses title_en', $bilingual->display_title === 'EN Title');
check('EN locale: display_excerpt uses excerpt_en', $bilingual->display_excerpt === 'EN excerpt');
check('EN locale: display_body uses body_en', $bilingual->display_body === '<p>EN body</p>');
check('EN locale: display_seo_title uses seo_title_en', $bilingual->display_seo_title === 'SEO EN');
check('EN locale: display_seo_description uses seo_description_en', $bilingual->display_seo_description === 'EN description');
check('EN locale: display_read_time_minutes recomputed from body_en word count', $bilingual->display_read_time_minutes >= 1);

check('EN locale + no translation: display_title falls back to ID', $idOnly->display_title === 'Judul ID Saja');
check('EN locale + no translation: display_body falls back to ID', $idOnly->display_body === '<p>Isi ID saja</p>');
check('EN locale + no translation: display_read_time_minutes falls back to stored value', $idOnly->display_read_time_minutes === 3);

// Partial EN metadata without body_en must NOT flip chrome to EN (avoid mixed ID/EN page).
$partial = new Article([
    'title' => 'Judul ID Partial',
    'title_en' => 'EN Title Partial',
    'excerpt' => 'Ringkasan ID',
    'excerpt_en' => 'EN excerpt partial',
    'body' => '<p>Isi ID</p>',
    'seo_title' => 'SEO ID',
    'seo_title_en' => 'SEO EN Partial',
    'read_time_minutes' => 4,
]);
check('Partial EN (no body_en): has_english false', $partial->has_english === false);
check('Partial EN: display_title stays ID (no mixed page)', $partial->display_title === 'Judul ID Partial');
check('Partial EN: display_body stays ID', $partial->display_body === '<p>Isi ID</p>');
check('Partial EN: display_seo_title stays ID', $partial->display_seo_title === 'SEO ID');

// EN body present but seo_title_en empty → fall back to title_en.
$enNoSeo = new Article([
    'title' => 'Judul ID',
    'title_en' => 'EN Title Fallback',
    'body' => '<p>ID</p>',
    'body_en' => '<p>EN</p>',
    'excerpt' => 'ID excerpt',
    'excerpt_en' => 'EN excerpt fallback',
    'seo_title' => 'SEO ID',
]);
check('EN body + empty seo_title_en falls back to title_en', $enNoSeo->display_seo_title === 'EN Title Fallback');
check('EN body + empty seo_description_en falls back to excerpt_en', $enNoSeo->display_seo_description === 'EN excerpt fallback');

App::setLocale('id');

// Structural regression check: public views must use display_* accessors, not raw fields.
$viewsToCheck = [
    __DIR__ . '/../resources/views/articles/show.blade.php',
    __DIR__ . '/../resources/views/components/article-card.blade.php',
];
$rawFieldPattern = '/\$(article|previousArticle|nextArticle|rel)->(title|body|excerpt|seo_title|seo_description|read_time_minutes)\b/';

foreach ($viewsToCheck as $path) {
    $contents = file_get_contents($path);
    $hasRaw = preg_match($rawFieldPattern, $contents);
    check('No raw ID-only field access in ' . basename($path), $hasRaw === 0);
}

// Banner: must suppress when the specific article already has an English body.
App::setLocale('en');
$bannerSuppressed = trim(view('components.locale-article-banner', ['article' => $bilingual])->render());
$bannerShownIdOnly = trim(view('components.locale-article-banner', ['article' => $idOnly])->render());
$bannerShownPartial = trim(view('components.locale-article-banner', ['article' => $partial])->render());
$bannerShownNoArticle = trim(view('components.locale-article-banner')->render());
check('Banner suppressed for article with English body', $bannerSuppressed === '');
check('Banner shown for article without English body', str_contains($bannerShownIdOnly, 'role="status"'));
check('Banner shown for partial EN (no body_en)', str_contains($bannerShownPartial, 'role="status"'));
check('Banner shown on listing pages (no article context)', str_contains($bannerShownNoArticle, 'role="status"'));

App::setLocale('id');
$bannerHiddenId = trim(view('components.locale-article-banner', ['article' => $idOnly])->render());
check('Banner hidden entirely in ID locale', $bannerHiddenId === '');

// Observer + admin table structural checks (source).
$observer = file_get_contents(__DIR__ . '/../app/Observers/ArticleObserver.php');
check('Observer remirrors body_en paths', str_contains($observer, "wasChanged('body_en')"));
$table = file_get_contents(__DIR__ . '/../app/Filament/Resources/Articles/Tables/ArticlesTable.php');
check('ArticlesTable has EN IconColumn', str_contains($table, "IconColumn::make('has_english')"));

echo PHP_EOL . "{$pass} pass / {$fail} fail" . PHP_EOL;
exit($fail > 0 ? 1 : 0);
