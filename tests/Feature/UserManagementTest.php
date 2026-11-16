<?php

use App\Models\User;

it('el admin crea un usuario con rol y empresa', function () {
    $admin = actingAsRole('admin');
    $company = $admin->resolveCurrentCompany();

    $this->post(route('users.store'), [
        'name' => 'María Quishpe',
        'email' => 'maria@cielosdeamerica.com',
        'password' => 'clave-segura-2026',
        'password_confirmation' => 'clave-segura-2026',
        'locale' => 'es',
        'is_active' => true,
        'role' => 'sales',
        'company_ids' => [$company->id],
    ])->assertRedirect(route('users.index'));

    $user = User::query()->firstWhere('email', 'maria@cielosdeamerica.com');

    expect($user)->not->toBeNull()
        ->and($user->hasRole('sales'))->toBeTrue()
        ->and($user->current_company_id)->toBe($company->id)
        ->and($user->belongsToCompany($company))->toBeTrue();
});

it('pide contraseña al crear y la deja opcional al editar', function () {
    $admin = actingAsRole('admin');
    $company = $admin->resolveCurrentCompany();

    $this->post(route('users.store'), [
        'name' => 'Sin clave',
        'email' => 'sinclave@finca.com',
        'locale' => 'es',
        'is_active' => true,
        'role' => 'sales',
        'company_ids' => [$company->id],
    ])->assertSessionHasErrors('password');

    $user = User::factory()->forCompany($company)->create(['name' => 'Antes']);
    $user->assignRole('sales');

    $this->put(route('users.update', $user), [
        'name' => 'Después',
        'email' => $user->email,
        'locale' => 'en',
        'is_active' => true,
        'role' => 'billing',
        'company_ids' => [$company->id],
    ])->assertRedirect(route('users.index'));

    $user->refresh();

    expect($user->name)->toBe('Después')
        ->and($user->locale)->toBe('en')
        ->and($user->hasRole('billing'))->toBeTrue();
});

it('desactiva un usuario y ese usuario ya no puede entrar', function () {
    $admin = actingAsRole('admin');
    $company = $admin->resolveCurrentCompany();

    $user = User::factory()->forCompany($company)->create(['password' => 'clave-segura-2026']);

    $this->put(route('users.status', $user), ['is_active' => false])->assertRedirect(route('users.index'));

    expect($user->refresh()->is_active)->toBeFalse();

    auth()->logout();

    $this->post(route('login'), [
        'email' => $user->email,
        'password' => 'clave-segura-2026',
    ])->assertSessionHasErrors('email');

    expect(auth()->check())->toBeFalse();
});

it('nadie se puede desactivar a sí mismo', function () {
    $admin = actingAsRole('admin');

    $this->put(route('users.status', $admin), ['is_active' => false])->assertForbidden();

    expect($admin->refresh()->is_active)->toBeTrue();
});

it('solo lista usuarios de la empresa activa', function () {
    $admin = actingAsRole('admin');
    $company = $admin->resolveCurrentCompany();

    User::factory()->forCompany($company)->create(['name' => 'De mi finca']);
    User::factory()->forCompany(companyWithModules())->create(['name' => 'De otra finca']);

    $this->get(route('users.index'))
        ->assertOk()
        ->assertSee('De mi finca')
        ->assertDontSee('De otra finca');
});

it('un rol sin permiso no entra a usuarios', function () {
    actingAsRole('sales');

    $this->get(route('users.index'))->assertForbidden();
});

it('cambia la contraseña de un usuario', function () {
    $admin = actingAsRole('admin');
    $user = User::factory()->forCompany($admin->resolveCurrentCompany())->create();

    $this->put(route('users.password', $user), [
        'password' => 'otra-clave-2026',
        'password_confirmation' => 'otra-clave-2026',
    ])->assertRedirect(route('users.index'));

    expect(Illuminate\Support\Facades\Hash::check('otra-clave-2026', $user->refresh()->password))->toBeTrue();
});
