<?php

use App\Enums\ModuleKey;
use App\Models\Company;
use App\Models\Module;
use App\Models\User;
use App\Support\CompanyContext;
use Database\Seeders\RoleSeeder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\PermissionRegistrar;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
*/

pest()->extend(Tests\TestCase::class)
    ->use(RefreshDatabase::class)
    ->in('Feature');

/*
|--------------------------------------------------------------------------
| Helpers compartidos
|--------------------------------------------------------------------------
*/

/**
 * Demuestra que un modelo de negocio nunca deja ver datos de otra empresa.
 *
 * Se usa en las pruebas de cada módulo: crea un registro en la empresa A y otro
 * en la empresa B y comprueba que, con la empresa A activa, el de B no existe.
 *
 * @param  class-string<Model>  $modelClass
 * @param  array<string, mixed>  $attributes
 */
function assertCompanyScoped(string $modelClass, array $attributes = []): void
{
    $companyA = Company::factory()->create(['name' => 'Empresa A']);
    $companyB = Company::factory()->create(['name' => 'Empresa B']);

    /** @var Model $recordA */
    $recordA = CompanyContext::runFor($companyA, fn () => $modelClass::factory()->create($attributes));
    /** @var Model $recordB */
    $recordB = CompanyContext::runFor($companyB, fn () => $modelClass::factory()->create($attributes));

    CompanyContext::runFor($companyA, function () use ($modelClass, $recordA, $recordB) {
        $visible = $modelClass::query()->pluck('id')->all();

        expect($visible)->toContain($recordA->getKey())
            ->and($visible)->not->toContain($recordB->getKey())
            ->and($modelClass::query()->find($recordB->getKey()))->toBeNull();
    });
}

/**
 * Empresa con los módulos indicados activos (todos, si no se dice otra cosa).
 *
 * @param  list<string>|null  $moduleKeys
 */
function companyWithModules(?array $moduleKeys = null): Company
{
    $company = Company::factory()->create();

    foreach ($moduleKeys ?? ModuleKey::values() as $key) {
        $module = Module::query()->firstOrCreate(['key' => $key], ['sort_order' => 0]);
        $company->modules()->syncWithoutDetaching([$module->getKey() => ['is_active' => true]]);
    }

    return $company->fresh() ?? $company;
}

/**
 * Crea un usuario con el rol indicado, lo autentica y deja su empresa activa.
 */
function actingAsRole(string $role = 'admin', ?Company $company = null): User
{
    $company = $company ?? companyWithModules();

    (new RoleSeeder)->run();
    app(PermissionRegistrar::class)->forgetCachedPermissions();

    $user = User::factory()->forCompany($company)->create();
    $user->assignRole($role);

    test()->actingAs($user);
    CompanyContext::set($company);

    return $user;
}
