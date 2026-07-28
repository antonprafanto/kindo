<?php

/**
 * Audit SEO field lengths for contributor articles (local DB).
 *
 * Usage: php scripts/audit-contributor-seo-fields.php
 */

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Article;
use App\Support\ArticleSeoLimits;

$limits = ArticleSeoLimits::fieldLimits();
$violations = [];

Article::query()
    ->select(array_merge(['id', 'slug', 'title', 'user_id'], array_keys($limits)))
    ->orderBy('id')
    ->chunkById(100, function ($articles) use ($limits, &$violations) {
        foreach ($articles as $article) {
            foreach ($limits as $field => $max) {
                $raw = $article->getRawOriginal($field);
                if (! is_string($raw) || $raw === '') {
                    continue;
                }

                $len = mb_strlen($raw);
                if ($len > $max) {
                    $violations[] = [
                        'id' => $article->id,
                        'slug' => $article->slug,
                        'field' => $field,
                        'length' => $len,
                        'max' => $max,
                    ];
                }
            }
        }
    });

$total = count($violations);
echo "Contributor SEO audit — violations: {$total}\n";

foreach ($violations as $v) {
    echo "#{$v['id']} {$v['slug']} — {$v['field']} {$v['length']}/{$v['max']}\n";
}

exit($total === 0 ? 0 : 1);
