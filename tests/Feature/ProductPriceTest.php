<?php

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;

it('crea un precio base y uno por cliente', function () {
    actingAsRole('admin');
    $product = Product::factory()->create();
    $customer = Customer::factory()->create();

    $this->post(route('prices.store'), [
        'product_id' => $product->id,
        'customer_id' => null,
        'season' => null,
        'price_per_stem' => '0.4500',
        'valid_from' => '2026-01-01',
        'valid_to' => null,
        'is_active' => true,
        'notes' => null,
    ])->assertRedirect(route('prices.index'));

    $this->post(route('prices.store'), [
        'product_id' => $product->id,
        'customer_id' => $customer->id,
        'season' => 'valentines',
        'price_per_stem' => '1.2000',
        'valid_from' => '2026-01-01',
        'valid_to' => '2026-02-28',
        'is_active' => true,
        'notes' => 'San Valentín',
    ])->assertRedirect(route('prices.index'));

    expect(ProductPrice::query()->count())->toBe(2)
        ->and(ProductPrice::query()->whereNotNull('customer_id')->first()->season->value)->toBe('valentines');
});

it('no acepta que la vigencia termine antes de empezar', function () {
    actingAsRole('admin');
    $product = Product::factory()->create();

    $this->post(route('prices.store'), [
        'product_id' => $product->id,
        'price_per_stem' => '0.4500',
        'valid_from' => '2026-06-01',
        'valid_to' => '2026-01-01',
        'is_active' => true,
    ])->assertSessionHasErrors('valid_to');
});

it('aisla los precios entre empresas', function () {
    assertCompanyScoped(ProductPrice::class);
});

it('deja a facturación administrar precios', function () {
    $company = companyWithModules();
    actingAsRole('billing', $company);

    $this->get(route('prices.create'))->assertOk();
});
