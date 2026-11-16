<?php

use App\Models\ActivityLog;
use App\Models\Customer;
use App\Support\CompanyContext;

it('registra la creación y el cambio de un cliente', function () {
    $admin = actingAsRole('admin');

    $this->post(route('customers.store'), [
        'name' => 'Bitácora Flores',
        'locale' => 'en',
        'incoterm' => 'FOB',
        'credit_days' => 0,
        'credit_limit' => '0',
        'is_active' => true,
    ])->assertRedirect(route('customers.index'));

    $customer = Customer::query()->firstWhere('name', 'Bitácora Flores');

    $activity = ActivityLog::query()->where('log_name', 'customers')->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and($activity->event)->toBe('created')
        ->and($activity->causer_id)->toBe($admin->id)
        ->and($activity->company_id)->toBe($admin->current_company_id)
        ->and($activity->subject_id)->toBe($customer->id);

    $this->put(route('customers.update', $customer), [
        'name' => 'Bitácora Flores S.A.',
        'locale' => 'en',
        'incoterm' => 'FOB',
        'credit_days' => 15,
        'credit_limit' => '0',
        'is_active' => true,
    ]);

    $updated = ActivityLog::query()->where('event', 'updated')->latest('id')->first();

    expect($updated)->not->toBeNull()
        ->and($updated->properties['attributes']['credit_days'])->toBe(15)
        ->and($updated->properties['old']['credit_days'])->toBe(0);
});

it('no guarda la contraseña del usuario en la bitácora', function () {
    $admin = actingAsRole('admin');
    $company = $admin->resolveCurrentCompany();

    $this->post(route('users.store'), [
        'name' => 'Nuevo',
        'email' => 'nuevo@finca.com',
        'password' => 'clave-segura-2026',
        'password_confirmation' => 'clave-segura-2026',
        'locale' => 'es',
        'is_active' => true,
        'role' => 'sales',
        'company_ids' => [$company->id],
    ]);

    $activity = ActivityLog::query()->where('log_name', 'users')->latest('id')->first();

    expect($activity)->not->toBeNull()
        ->and(array_keys($activity->properties['attributes'] ?? []))->not->toContain('password');
});

it('muestra la bitácora solo a quien tiene el permiso', function () {
    $company = companyWithModules();

    actingAsRole('admin', $company);
    $this->get(route('activity.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('activity/index'));

    actingAsRole('sales', $company);
    $this->get(route('activity.index'))->assertForbidden();
});

it('filtra la bitácora por tabla', function () {
    actingAsRole('admin');
    Customer::factory()->create();

    $this->get(route('activity.index', ['log_name' => 'customers']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('filters.log_name', 'customers'));

    $this->get(route('activity.index', ['log_name' => 'products']))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->where('activities.total', 0));
});

it('no muestra la bitácora de otra empresa', function () {
    $otherCompany = companyWithModules();

    CompanyContext::runFor($otherCompany, function () {
        Customer::factory()->create(['name' => 'De otra finca']);
    });

    actingAsRole('admin');

    expect(ActivityLog::query()->count())->toBe(0)
        ->and(ActivityLog::acrossCompanies()->count())->toBeGreaterThan(0);
});
