<?php

namespace App\Http\Controllers;

use App\Http\Middleware\SetLocale;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LocaleController extends Controller
{
    public function switch(Request $request, string $locale): RedirectResponse
    {
        abort_unless(in_array($locale, SetLocale::SUPPORTED, true), 404);

        session(['locale' => $locale]);

        $target = route('home');
        $previous = url()->previous();
        $previousHost = is_string($previous) ? parse_url($previous, PHP_URL_HOST) : null;

        // Only redirect back to same-host URLs (block open redirects via Referer).
        if ($previousHost && $previousHost === $request->getHost()) {
            $path = parse_url($previous, PHP_URL_PATH) ?: '/';
            if (! str_starts_with(ltrim($path, '/'), 'locale/')) {
                $target = $previous;
            }
        }

        return redirect()
            ->to($target)
            ->withCookie(cookie(SetLocale::COOKIE, $locale, 60 * 24 * 365));
    }
}
