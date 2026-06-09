<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = ($request->hasSession() ? $request->session()->get('locale') : null)
            ?? $request->user()?->locale?->value
            ?? (str_starts_with((string) $request->header('Accept-Language'), 'en') ? 'en' : null)
            ?? config('app.locale');

        if (in_array($locale, ['ar', 'en'], true)) {
            app()->setLocale($locale);
        }

        return $next($request);
    }
}
