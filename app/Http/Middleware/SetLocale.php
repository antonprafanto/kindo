<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public const SUPPORTED = ['id', 'en'];

    public const COOKIE = 'kindo_locale';

    public function handle(Request $request, Closure $next): Response
    {
        $fromQuery = $request->query('lang');
        $locale = null;

        if (is_string($fromQuery) && in_array($fromQuery, self::SUPPORTED, true)) {
            $locale = $fromQuery;
            session(['locale' => $locale]);
        } elseif (session()->has('locale') && in_array(session('locale'), self::SUPPORTED, true)) {
            $locale = session('locale');
        } elseif (in_array((string) $request->cookie(self::COOKIE), self::SUPPORTED, true)) {
            $locale = (string) $request->cookie(self::COOKIE);
            session(['locale' => $locale]);
        } else {
            $locale = config('app.locale', 'id');
        }

        App::setLocale($locale);
        \Illuminate\Support\Carbon::setLocale($locale);

        /** @var Response $response */
        $response = $next($request);

        if (is_string($fromQuery) && in_array($fromQuery, self::SUPPORTED, true)) {
            $response->headers->setCookie(
                cookie(self::COOKIE, $locale, 60 * 24 * 365)
            );
        }

        return $response;
    }
}
