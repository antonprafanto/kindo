<?php

namespace App\Support;

class Contrast
{
    /**
     * Return #000 or #fff for readable text on a hex background (WCAG relative luminance).
     */
    public static function textOn(?string $hex, string $fallback = '#ffffff'): string
    {
        $hex = ltrim((string) $hex, '#');

        if (strlen($hex) === 3) {
            $hex = $hex[0].$hex[0].$hex[1].$hex[1].$hex[2].$hex[2];
        }

        if (! preg_match('/^[0-9a-fA-F]{6}$/', $hex)) {
            return $fallback;
        }

        $channel = static function (string $c): float {
            $v = hexdec($c) / 255;

            return $v <= 0.03928
                ? $v / 12.92
                : (($v + 0.055) / 1.055) ** 2.4;
        };

        $r = $channel(substr($hex, 0, 2));
        $g = $channel(substr($hex, 2, 2));
        $b = $channel(substr($hex, 4, 2));
        $luminance = (0.2126 * $r) + (0.7152 * $g) + (0.0722 * $b);

        return $luminance > 0.179 ? '#000000' : '#ffffff';
    }
}
