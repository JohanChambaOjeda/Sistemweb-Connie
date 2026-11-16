<?php

use App\Models\Company;

it('cambia el idioma de la interfaz', function () {
    $user = actingAsRole('admin');

    $this->from(route('dashboard'))
        ->post(route('locale.store'), ['locale' => 'en'])
        ->assertRedirect(route('dashboard'));

    expect(session('locale'))->toBe('en')
        ->and($user->refresh()->locale)->toBe('en');

    $this->get(route('dashboard'))
        ->assertInertia(fn ($page) => $page
            ->where('locale', 'en')
            ->where('translations.nav.dashboard', 'Dashboard'));
});

it('rechaza un idioma que no existe', function () {
    actingAsRole('admin');

    $this->from(route('dashboard'))
        ->post(route('locale.store'), ['locale' => 'fr'])
        ->assertSessionHasErrors('locale');
});

it('comparte la empresa activa, sus módulos y los permisos', function () {
    actingAsRole('admin');

    $this->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->has('company')
            ->has('modules')
            ->where('auth.user.roles', ['admin'])
            ->has('auth.user.permissions'));
});

it('cambia de empresa cuando el usuario tiene varias', function () {
    $first = companyWithModules();
    $second = companyWithModules();

    $user = actingAsRole('admin', $first);
    $user->companies()->syncWithoutDetaching([$second->getKey()]);

    $this->put(route('current-company.update', $second))->assertRedirect(route('dashboard'));

    expect($user->refresh()->current_company_id)->toBe($second->id);
});

it('no deja entrar a una empresa ajena', function () {
    $user = actingAsRole('admin');
    $foreign = Company::factory()->create();

    $this->from(route('dashboard'))
        ->put(route('current-company.update', $foreign))
        ->assertSessionHasErrors('company');

    expect($user->refresh()->current_company_id)->not->toBe($foreign->id);
});
