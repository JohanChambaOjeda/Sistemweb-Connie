<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Support\Translations;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Props every page receives: user with roles and permissions, active
     * company, modules of its plan, language and translations.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        /** @var User|null $user */
        $user = $request->user();
        $company = $user?->resolveCurrentCompany();

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'locale' => app()->getLocale(),
            'locales' => Translations::available(),
            'translations' => Translations::forLocale(app()->getLocale()),
            'auth' => [
                'user' => $user === null ? null : [
                    'id' => $user->getKey(),
                    'name' => $user->name,
                    'email' => $user->email,
                    'locale' => $user->locale,
                    'roles' => $user->getRoleNames()->values()->all(),
                    'permissions' => $user->getAllPermissions()->pluck('name')->values()->all(),
                ],
            ],
            'company' => $company === null ? null : [
                'id' => $company->getKey(),
                'name' => $company->name,
                'logo_url' => $company->logo_path === null ? null : Storage::url($company->logo_path),
            ],
            'companies' => $user === null ? [] : $user->companies()
                ->orderBy('name')
                ->get(['companies.id', 'companies.name'])
                ->map(fn ($item) => ['id' => $item->getKey(), 'name' => $item->name])
                ->all(),
            'modules' => $company?->activeModuleKeys() ?? [],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
