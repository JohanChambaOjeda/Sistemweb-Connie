<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\FlowerVariety;
use App\Models\Product;
use App\Models\User;
use App\Support\CompanyContext;
use Database\Seeders\DemoSeeder;
use Database\Seeders\ModuleSeeder;
use Database\Seeders\RoleSeeder;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed(ModuleSeeder::class);
    $this->seed(RoleSeeder::class);
    $this->seed(DemoSeeder::class);

    $this->farm = Company::query()->where('name', 'Cielos de América EC')->firstOrFail();
    $this->demo = Company::query()->where('name', 'Finca Demo')->firstOrFail();
});

it('deja las dos empresas con sus módulos', function () {
    expect($this->farm->activeModuleKeys())->toContain('sales', 'postharvest', 'billing', 'warehouse')
        ->and($this->demo->activeModuleKeys())->toBe(['sales', 'postharvest'])
        ->and($this->demo->hasModule('billing'))->toBeFalse();
});

it('crea un usuario por rol con la contraseña de desarrollo', function () {
    $admin = User::query()->where('email', 'admin@cielosdeamerica.com')->firstOrFail();

    expect($admin->hasRole('admin'))->toBeTrue()
        ->and(Hash::check('password', $admin->password))->toBeTrue()
        ->and($admin->current_company_id)->toBe($this->farm->id);

    foreach (['ventas', 'facturacion', 'poscosecha', 'bodega'] as $mailbox) {
        expect(User::query()->where('email', $mailbox.'@cielosdeamerica.com')->exists())->toBeTrue();
    }
});

it('carga las ocho variedades y productos de 40 a 90 cm', function () {
    CompanyContext::runFor($this->farm, function () {
        expect(FlowerVariety::query()->count())->toBe(8)
            ->and(FlowerVariety::query()->pluck('name')->all())
            ->toContain('Explorer', 'Hot Spot', 'Gotcha', 'Sweet Spot', 'Be Sweet', 'Fatima', 'Vicky', 'Mondial')
            ->and(Product::query()->min('stem_length_cm'))->toBe(40)
            ->and(Product::query()->max('stem_length_cm'))->toBe(90);
    });
});

it('la finca demo no ve los datos de Cielos de América', function () {
    CompanyContext::runFor($this->demo, function () {
        expect(Customer::query()->pluck('name')->all())->toBe(['Cliente de la Finca Demo'])
            ->and(FlowerVariety::query()->pluck('name')->all())->toBe(['Freedom']);
    });

    CompanyContext::runFor($this->farm, function () {
        expect(Customer::query()->where('name', 'Cliente de la Finca Demo')->exists())->toBeFalse();
    });
});

it('se puede correr dos veces sin duplicar nada', function () {
    $customers = CompanyContext::runFor($this->farm, fn () => Customer::query()->count());

    $this->seed(DemoSeeder::class);

    expect(Company::query()->count())->toBe(2)
        ->and(CompanyContext::runFor($this->farm, fn () => Customer::query()->count()))->toBe($customers);
});
