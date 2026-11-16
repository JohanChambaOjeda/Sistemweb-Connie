<?php

namespace App\Http\Controllers;

use App\Support\Translations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/**
 * Cambia el idioma de la interfaz para la sesión y lo recuerda en el usuario.
 */
class LocaleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'locale' => ['required', Rule::in(Translations::available())],
        ]);

        $request->session()->put('locale', $validated['locale']);

        $user = $request->user();

        if ($user !== null) {
            $user->forceFill(['locale' => $validated['locale']])->save();
        }

        return back();
    }
}
