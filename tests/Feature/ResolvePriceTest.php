<?php

use App\Actions\ProductPrices\ResolvePrice;
use App\Enums\Season;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Carbon;

beforeEach(function () {
    actingAsRole('admin');

    $this->product = Product::factory()->create();
    $this->customer = Customer::factory()->create();
    $this->resolve = app(ResolvePrice::class);
});

it('devuelve el precio base cuando no hay nada más', function () {
    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.4000',
        'valid_from' => '2026-01-01',
    ]);

    $price = $this->resolve->handle($this->product, null, Carbon::parse('2026-08-15'));

    expect($price)->not->toBeNull()
        ->and((float) $price->price_per_stem)->toBe(0.4);
});

it('la temporada gana al precio base', function () {
    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.4000',
        'valid_from' => '2026-01-01',
    ]);
    ProductPrice::factory()->forSeason(Season::Valentines)->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.9000',
        'valid_from' => '2026-01-01',
    ]);

    $inSeason = $this->resolve->handle($this->product, null, Carbon::parse('2026-02-10'));
    $outOfSeason = $this->resolve->handle($this->product, null, Carbon::parse('2026-08-15'));

    expect((float) $inSeason->price_per_stem)->toBe(0.9)
        ->and((float) $outOfSeason->price_per_stem)->toBe(0.4);
});

it('el precio del cliente gana a la temporada', function () {
    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.4000',
        'valid_from' => '2026-01-01',
    ]);
    ProductPrice::factory()->forSeason(Season::Valentines)->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.9000',
        'valid_from' => '2026-01-01',
    ]);
    ProductPrice::factory()->forCustomer($this->customer)->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.5500',
        'valid_from' => '2026-01-01',
    ]);

    $price = $this->resolve->handle($this->product, $this->customer, Carbon::parse('2026-02-10'));

    expect((float) $price->price_per_stem)->toBe(0.55);
});

it('el precio del cliente en temporada gana a todo', function () {
    ProductPrice::factory()->forCustomer($this->customer)->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.5500',
        'valid_from' => '2026-01-01',
    ]);
    ProductPrice::factory()->forCustomer($this->customer)->forSeason(Season::Valentines)->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '1.1000',
        'valid_from' => '2026-01-01',
    ]);

    $price = $this->resolve->handle($this->product, $this->customer, Carbon::parse('2026-02-10'));

    expect((float) $price->price_per_stem)->toBe(1.1);
});

it('ignora los precios fuera de vigencia', function () {
    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.3000',
        'valid_from' => '2025-01-01',
        'valid_to' => '2025-12-31',
    ]);

    expect($this->resolve->handle($this->product, null, Carbon::parse('2026-08-15')))->toBeNull();
});

it('ignora los precios desactivados', function () {
    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.3000',
        'valid_from' => '2026-01-01',
        'is_active' => false,
    ]);

    expect($this->resolve->handle($this->product, null, Carbon::parse('2026-08-15')))->toBeNull();
});

it('dentro del mismo nivel gana la vigencia más reciente', function () {
    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.4000',
        'valid_from' => '2026-01-01',
    ]);
    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.4500',
        'valid_from' => '2026-06-01',
    ]);

    $price = $this->resolve->handle($this->product, null, Carbon::parse('2026-08-15'));

    expect((float) $price->price_per_stem)->toBe(0.45);
});

it('no usa el precio especial de otro cliente', function () {
    $otherCustomer = Customer::factory()->create();

    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.4000',
        'valid_from' => '2026-01-01',
    ]);
    ProductPrice::factory()->forCustomer($otherCustomer)->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.2000',
        'valid_from' => '2026-01-01',
    ]);

    $price = $this->resolve->handle($this->product, $this->customer, Carbon::parse('2026-08-15'));

    expect((float) $price->price_per_stem)->toBe(0.4);
});

it('reconoce las temporadas por fecha', function () {
    expect(Season::forDate(Carbon::parse('2026-02-10')))->toBe(Season::Valentines)
        ->and(Season::forDate(Carbon::parse('2026-05-05')))->toBe(Season::MothersDay)
        ->and(Season::forDate(Carbon::parse('2026-08-15')))->toBeNull();
});

it('devuelve el precio como texto con priceFor', function () {
    ProductPrice::factory()->create([
        'product_id' => $this->product->id,
        'price_per_stem' => '0.4200',
        'valid_from' => '2026-01-01',
    ]);

    expect($this->resolve->priceFor($this->product, null, Carbon::parse('2026-08-15')))->toBe('0.4200');
});
