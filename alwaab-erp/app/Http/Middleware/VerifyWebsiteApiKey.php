<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyWebsiteApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $configured = (string) config('website.api_key', '');

        if ($configured === '' || ($this->isProduction() && $this->isWeakKey($configured))) {
            return response()->json(['message' => 'Website integration is not configured.'], 503);
        }

        $key = (string) ($request->header('X-Website-Key') ?? $request->input('api_key', ''));

        if ($key === '' || ! hash_equals($configured, $key)) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }

    private function isProduction(): bool
    {
        return app()->environment('production');
    }

    private function isWeakKey(string $key): bool
    {
        return in_array($key, ['alwaab-website-key-change-me', 'your-secret-key-here', 'changeme'], true);
    }
}
