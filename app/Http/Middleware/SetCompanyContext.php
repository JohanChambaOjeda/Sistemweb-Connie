<?php

namespace App\Http\Middleware;

use App\Support\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Puts the company of the authenticated user into the request context so the
 * BelongsToCompany scope can filter every query.
 */
class SetCompanyContext
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null) {
            CompanyContext::set($user->resolveCurrentCompany());
        }

        return $next($request);
    }
}
