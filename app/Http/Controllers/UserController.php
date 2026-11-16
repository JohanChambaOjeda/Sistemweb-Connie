<?php

namespace App\Http\Controllers;

use App\Actions\Users\CreateUser;
use App\Actions\Users\SetUserPassword;
use App\Actions\Users\ToggleUserActive;
use App\Actions\Users\UpdateUser;
use App\Enums\RoleName;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Support\TableQuery;
use App\Support\Translations;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Alta de usuarios del back office. No hay registro público: el admin crea las
 * cuentas, les pone rol y decide a qué empresas entran.
 */
class UserController extends Controller
{
    /** @var list<string> */
    private const SEARCHABLE = ['name', 'email'];

    /** @var list<string> */
    private const SORTABLE = ['name', 'email', 'created_at'];

    public function index(Request $request): Response
    {
        $this->authorize('viewAny', User::class);

        $users = TableQuery::paginate(
            $this->companyUsers($request)->with('roles:id,name'),
            $request,
            self::SEARCHABLE,
            self::SORTABLE,
            'name',
        );

        $users->getCollection()->each(function (User $user) {
            $user->setAttribute('role', $user->getRoleNames()->first());
            $user->unsetRelation('roles');
        });

        return Inertia::render('users/index', [
            'users' => $users,
            'table' => TableQuery::state($request, self::SORTABLE, 'name'),
        ]);
    }

    public function create(Request $request): Response
    {
        $this->authorize('create', User::class);

        return Inertia::render('users/form', [
            'user' => null,
            ...$this->formOptions($request),
        ]);
    }

    public function store(UserRequest $request, CreateUser $action): RedirectResponse
    {
        $this->authorize('create', User::class);

        $action->handle(
            $this->attributes($request),
            (string) $request->string('role'),
            $this->companyIds($request),
        );

        return to_route('users.index')->with('success', __('users.messages.created'));
    }

    public function edit(Request $request, User $user): Response
    {
        $this->authorize('update', $user);

        return Inertia::render('users/form', [
            'user' => [
                'id' => $user->getKey(),
                'name' => $user->name,
                'email' => $user->email,
                'locale' => $user->locale,
                'is_active' => $user->is_active,
                'role' => $user->getRoleNames()->first(),
                'company_ids' => $user->companies()->pluck('companies.id')->all(),
            ],
            ...$this->formOptions($request),
        ]);
    }

    public function update(UserRequest $request, User $user, UpdateUser $action): RedirectResponse
    {
        $this->authorize('update', $user);

        $action->handle(
            $user,
            $this->attributes($request),
            (string) $request->string('role'),
            $this->companyIds($request),
        );

        return to_route('users.index')->with('success', __('users.messages.updated'));
    }

    /**
     * Activa o desactiva la cuenta. Nunca se borra un usuario.
     */
    public function updateStatus(Request $request, User $user, ToggleUserActive $action): RedirectResponse
    {
        $this->authorize('delete', $user);

        $action->handle($user, $request->boolean('is_active'));

        return to_route('users.index')->with('success', __('users.messages.status_changed'));
    }

    public function updatePassword(Request $request, User $user, SetUserPassword $action): RedirectResponse
    {
        $this->authorize('update', $user);

        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::min(8)],
        ]);

        $action->handle($user, $validated['password']);

        return to_route('users.index')->with('success', __('users.messages.password_changed'));
    }

    /**
     * @return array<string, mixed>
     */
    private function attributes(UserRequest $request): array
    {
        $data = $request->safe()->only(['name', 'email', 'locale', 'is_active']);

        $password = $request->string('password')->toString();

        if ($password !== '') {
            $data['password'] = $password;
        }

        return $data;
    }

    /**
     * @return list<int>
     */
    private function companyIds(UserRequest $request): array
    {
        /** @var list<int> $ids */
        $ids = array_values(array_map('intval', (array) $request->input('company_ids', [])));

        return $ids;
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(Request $request): array
    {
        return [
            'roles' => array_map(fn (RoleName $role) => [
                'value' => $role->value,
                'label' => $role->label(),
            ], RoleName::backOffice()),
            'companies' => $request->user()?->companies()->orderBy('name')->get(['companies.id', 'companies.name']) ?? [],
            'locales' => Translations::available(),
        ];
    }

    /**
     * Solo los usuarios que comparten la empresa activa.
     *
     * @return \Illuminate\Database\Eloquent\Builder<User>
     */
    private function companyUsers(Request $request): \Illuminate\Database\Eloquent\Builder
    {
        $companyId = $request->user()?->current_company_id;

        return User::query()->whereHas(
            'companies',
            fn ($query) => $query->whereKey($companyId),
        );
    }
}
