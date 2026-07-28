<?php

/**
 * Generic EN-completeness gate for a single article, run against the real DB.
 * Use this before sending ANY new article (any seri/jalur) for review/publish,
 * per docs/checklist-konten-artikel.md §8 (bilingual wajib mulai semua artikel baru).
 *
 * Usage: php scripts/audit-article-en-completeness.php <slug>
 */

require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;

$slug = $argv[1] ?? null;

if (! $slug) {
    fwrite(STDERR, "Usage: php scripts/audit-article-en-completeness.php <slug>\n");
    exit(2);
}

$article = Article::where('slug', $slug)->first();

if (! $article) {
    fwrite(STDERR, "Article not found: {$slug}\n");
    exit(2);
}

$pass = 0;
$fail = 0;

function check(string $label, bool $ok): void
{
    global $pass, $fail;
    echo ($ok ? 'OK    ' : 'FAIL  ') . $label . PHP_EOL;
    $ok ? $pass++ : $fail++;
}

echo "=== EN completeness: {$slug} ===" . PHP_EOL . PHP_EOL;

check('title_en filled', filled($article->title_en));
check('excerpt_en filled', filled($article->excerpt_en));
check('body_en filled', filled($article->body_en));
check('seo_title_en filled (recommended)', filled($article->seo_title_en));
check('seo_description_en filled (recommended)', filled($article->seo_description_en));
check('has_english flag true', $article->has_english === true);

if (filled($article->body) && filled($article->body_en)) {
    $idHeadings = substr_count($article->body, '<h2') + substr_count($article->body, '<h3');
    $enHeadings = substr_count($article->body_en, '<h2') + substr_count($article->body_en, '<h3');
    check("Heading count roughly matches (ID={$idHeadings} EN={$enHeadings})", abs($idHeadings - $enHeadings) <= 1);

    $idCode = substr_count($article->body, '<pre') + substr_count($article->body, '<code');
    $enCode = substr_count($article->body_en, '<pre') + substr_count($article->body_en, '<code');
    check("Code block count matches (ID={$idCode} EN={$enCode})", $idCode === $enCode);

    $idWords = str_word_count(strip_tags($article->body));
    $enWords = str_word_count(strip_tags($article->body_en));
    $ratio = $idWords > 0 ? $enWords / $idWords : 0;
    check("EN body length plausible vs ID (ratio " . round($ratio, 2) . ", expect 0.6-1.6)", $ratio >= 0.6 && $ratio <= 1.6);
}

echo PHP_EOL . "{$pass} pass / {$fail} fail" . PHP_EOL;
exit($fail > 0 ? 1 : 0);
