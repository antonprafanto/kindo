<?php

namespace App\Support;

use App\Models\Article;

class ArticleSeoLimits
{
    public const TITLE_MAX = 70;

    public const DESCRIPTION_MAX = 160;

    /**
     * @return array<string, int>
     */
    public static function fieldLimits(): array
    {
        return [
            'seo_title' => self::TITLE_MAX,
            'seo_title_en' => self::TITLE_MAX,
            'seo_description' => self::DESCRIPTION_MAX,
            'seo_description_en' => self::DESCRIPTION_MAX,
        ];
    }

    public static function clamp(?string $value, int $max): ?string
    {
        if ($value === null || $value === '') {
            return $value;
        }

        return mb_strlen($value) > $max ? mb_substr($value, 0, $max) : $value;
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    public static function clampAttributes(array $attributes): array
    {
        foreach (self::fieldLimits() as $field => $max) {
            if (! array_key_exists($field, $attributes)) {
                continue;
            }

            $value = $attributes[$field];
            if (is_string($value)) {
                $attributes[$field] = self::clamp($value, $max);
            }
        }

        return $attributes;
    }

    /**
     * @return array{scanned: int, updated: int, fields: array<string, int>}
     */
    public static function clampAllArticles(): array
    {
        $scanned = 0;
        $updated = 0;
        $fields = array_fill_keys(array_keys(self::fieldLimits()), 0);

        Article::query()
            ->select(array_merge(['id'], array_keys(self::fieldLimits())))
            ->orderBy('id')
            ->chunkById(50, function ($articles) use (&$scanned, &$updated, &$fields) {
                foreach ($articles as $article) {
                    $scanned++;
                    $changes = [];

                    foreach (self::fieldLimits() as $field => $max) {
                        $raw = $article->getRawOriginal($field);
                        if (! is_string($raw) || $raw === '') {
                            continue;
                        }

                        $clamped = self::clamp($raw, $max);
                        if ($clamped !== $raw) {
                            $changes[$field] = $clamped;
                            $fields[$field]++;
                        }
                    }

                    if ($changes !== []) {
                        $article->updateQuietly($changes);
                        $updated++;
                    }
                }
            });

        return [
            'scanned' => $scanned,
            'updated' => $updated,
            'fields' => $fields,
        ];
    }
}
