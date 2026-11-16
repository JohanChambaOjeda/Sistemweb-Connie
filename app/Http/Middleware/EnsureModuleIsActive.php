<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Support\CompanyContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks routes of modules the company has not bought: module:<key>.
 * Several keys may be listed and all of them must be active.
 */
class EnsureModuleIsActive
{
    /**
     * @param  Closure(Request): Response  $next
     */
    public function handle(Request $request, Closure $next, string ...$keys): Response
    {
        $companyId = CompanyContext::id();
        $company = $companyId === null ? null : Company::find($companyId);

        if (! $company instanceof Company) {
            abort(403, __('modules.no_company'));
        }

        foreach ($keys as $key) {
            if (! $company->hasModule($key)) {
                abort(403, __('modules.inactive', ['module' => __('modules.'.$key)]));
            }
        }

        return $next($request);
    }
}
