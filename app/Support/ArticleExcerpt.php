<?php

namespace App\Support;

use Illuminate\Support\Str;

class ArticleExcerpt
{
    public const MARKER_ID = '/#\d+ \(ini\)/';

    public const MARKER_EN = '/#\d+ \(this article\)/';

    /** Matches `Str::limit` on article cards in `article-card.blade.php`. */
    public const CARD_PREVIEW_LIMIT = 100;

    public static function fromHtml(string $html, int $limit = 200, ?string $markerPattern = null): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
        if ($plain === '') {
            return '';
        }

        $pattern = $markerPattern ?? self::MARKER_ID;
        $excerpt = Str::limit($plain, $limit);

        if (self::markerVisibleInCardPreview($excerpt, $pattern)) {
            return $excerpt;
        }

        if (preg_match($pattern, $plain, $match, PREG_OFFSET_CAPTURE)) {
            $markerPos = $match[0][1];
            $start = max(0, $markerPos - 20);
            $excerpt = Str::limit(substr($plain, $start), $limit);

            if (self::markerVisibleInCardPreview($excerpt, $pattern)) {
                return $excerpt;
            }

            $start = max(0, $markerPos);

            return Str::limit(substr($plain, $start), $limit);
        }

        return $excerpt;
    }

    public static function markerVisibleInCardPreview(string $excerpt, ?string $pattern = null): bool
    {
        $pattern ??= self::MARKER_ID;

        return (bool) preg_match($pattern, Str::limit($excerpt, self::CARD_PREVIEW_LIMIT));
    }
}
