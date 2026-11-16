<?php

use App\Enums\ModuleKey;
use App\Models\Company;
use App\Models\Module;
use App\Models\User;
use Illuminate\Support\Facades\Route;

beforeEach(function () {
    Route::middleware(['web', 'auth', 'module:sales'])
        ->get('/prueba-ventas', fn () => response('ok'));
});

function companyWithModule(ModuleKey $key, bool $active = true): Company
{
    $company = Company::factory()->create();
    $module = Module::factory()->key($key)->create();
    $company->modules()->attach($module, ['is_active' => $active]);

    return $company;
}

it('deja pasar cuando el módulo está activo', function () {
    $company = companyWithModule(ModuleKey::Sales);
    $user = User::factory()->forCompany($company)->create();

    $this->actingAs($user)->get('/prueba-ventas')->assertOk();
});

it('bloquea cuando el módulo está inactivo', function () {
    $company = companyWithModule(ModuleKey::Sales, active: false);
    $user = User::factory()->forCompany($company)->create();

    $this->actingAs($user)->get('/prueba-ventas')->assertForbidden();
});

it('bloquea cuando la empresa no tiene el módulo', function () {
    $company = companyWithModule(ModuleKey::Billing);
    $user = User::factory()->forCompany($company)->create();

    $this->actingAs($user)->get('/prueba-ventas')->assertForbidden();
});

it('bloquea cuando el usuario no tiene empresa', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/prueba-ventas')->assertForbidden();
});

it('expone las claves de módulos activos en orden', function () {
    $company = Company::factory()->create();
    $sales = Module::factory()->key(ModuleKey::Sales)->create(['sort_order' => 10]);
    $billing = Module::factory()->key(ModuleKey::Billing)->create(['sort_order' => 20]);
    $payroll = Module::factory()->key(ModuleKey::Payroll)->create(['sort_order' => 30]);

    $company->modules()->attach($sales, ['is_active' => true]);
    $company->modules()->attach($billing, ['is_active' => true]);
    $company->modules()->attach($payroll, ['is_active' => false]);

    expect($company->activeModuleKeys())->toBe(['sales', 'billing'])
        ->and($company->hasModule(ModuleKey::Payroll))->toBeFalse();
});
