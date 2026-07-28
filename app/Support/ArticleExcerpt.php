<?php

namespace App\Support;

use Illuminate\Support\Str;

class ArticleExcerpt
{
    public const MARKER_ID = '/#\d+ \(ini\)/';

    public const MARKER_EN = '/#\d+ \(this article\)/';

    public static function fromHtml(string $html, int $limit = 200, ?string $markerPattern = null): string
    {
        $plain = trim(preg_replace('/\s+/', ' ', strip_tags($html)) ?? '');
        if ($plain === '') {
            return '';
        }

        $pattern = $markerPattern ?? self::MARKER_ID;
        $excerpt = Str::limit($plain, $limit);

        if (preg_match($pattern, $excerpt)) {
            return $excerpt;
        }

        if (preg_match($pattern, $plain, $match, PREG_OFFSET_CAPTURE)) {
            $start = max(0, $match[0][1] - 60);

            return Str::limit(substr($plain, $start), $limit);
        }

        return $excerpt;
    }
}
