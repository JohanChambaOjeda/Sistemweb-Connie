<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

/**
 * Cambia la empresa activa cuando el usuario trabaja en más de una finca.
 */
class CurrentCompanyController extends Controller
{
    public function update(Request $request, Company $company): RedirectResponse
    {
        $user = $request->user();

        if ($user === null || ! $user->belongsToCompany($company)) {
            throw ValidationException::withMessages([
                'company' => __('companies.messages.not_allowed'),
            ]);
        }

        $user->forceFill(['current_company_id' => $company->getKey()])->save();

        return to_route('dashboard')->with('success', __('companies.messages.switched'));
    }
}
