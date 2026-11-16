<?php

use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Tests\Support\Models\ScopedTestModel;

beforeEach(function () {
    Schema::create('scoped_test_models', function (Blueprint $table) {
        $table->id();
        $table->foreignId('company_id');
        $table->string('name');
        $table->timestamps();
    });
});

it('rellena company_id con la empresa activa', function () {
    $company = Company::factory()->create();

    $record = CompanyContext::runFor($company, fn () => ScopedTestModel::create(['name' => 'Explorer']));

    expect($record->company_id)->toBe($company->id);
});

it('no muestra registros de otra empresa', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    $recordA = CompanyContext::runFor($companyA, fn () => ScopedTestModel::create(['name' => 'De A']));
    $recordB = CompanyContext::runFor($companyB, fn () => ScopedTestModel::create(['name' => 'De B']));

    CompanyContext::runFor($companyA, function () use ($recordA, $recordB) {
        $visible = ScopedTestModel::pluck('id')->all();

        expect($visible)->toContain($recordA->id)
            ->and($visible)->not->toContain($recordB->id)
            ->and(ScopedTestModel::find($recordB->id))->toBeNull();
    });
});

it('permite consultar todas las empresas solo de forma explícita', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    CompanyContext::runFor($companyA, fn () => ScopedTestModel::create(['name' => 'De A']));
    CompanyContext::runFor($companyB, fn () => ScopedTestModel::create(['name' => 'De B']));

    CompanyContext::runFor($companyA, function () {
        expect(ScopedTestModel::count())->toBe(1)
            ->and(ScopedTestModel::acrossCompanies()->count())->toBe(2);
    });
});

it('restaura la empresa anterior al terminar el callback', function () {
    $companyA = Company::factory()->create();
    $companyB = Company::factory()->create();

    CompanyContext::set($companyA);

    CompanyContext::runFor($companyB, function () use ($companyB) {
        expect(CompanyContext::id())->toBe($companyB->id);
    });

    expect(CompanyContext::id())->toBe($companyA->id);
});
