<?php

namespace App\Http\Middleware;

use App\Support\Translations;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Chooses the interface language: what the user picked in this session, then
 * the language stored on the user, then the company default.
 */
class SetLocale
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        App::setLocale($locale);

        return $next($request);
    }

    private function resolveLocale(Request $request): string
    {
        $candidates = [
            $request->session()->get('locale'),
            $request->user()?->locale,
            $request->user()?->resolveCurrentCompany()?->default_locale,
            config('app.locale'),
        ];

        foreach ($candidates as $candidate) {
            if (is_string($candidate) && in_array($candidate, Translations::available(), true)) {
                return $candidate;
            }
        }

        return 'es';
    }
}
