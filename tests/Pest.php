<?php

use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Testing\RefreshDatabase;

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
