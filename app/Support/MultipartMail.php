<?php

namespace App\Support;

use Illuminate\Support\Facades\Mail;

/**
 * Send HTML mail with a text/plain alternative (P5-01).
 */
class MultipartMail
{
    public static function send(string $view, array $data, callable $callback): void
    {
        $html = view($view, $data)->render();
        $plain = self::htmlToPlain($html);

        Mail::html($html, function ($message) use ($callback, $plain) {
            $callback($message);
            $message->text($plain);
        });
    }

    public static function htmlToPlain(string $html): string
    {
        $text = preg_replace('/<(br|\/p|\/div|\/h[1-6]|\/li|\/tr)\s*\/?>/i', "\n", $html) ?? $html;
        $text = preg_replace('/<li[^>]*>/i', '- ', $text) ?? $text;
        $text = strip_tags($text);
        $text = html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = preg_replace("/[ \t]+\n/", "\n", $text) ?? $text;
        $text = preg_replace("/\n{3,}/", "\n\n", $text) ?? $text;

        return trim($text);
    }
}
