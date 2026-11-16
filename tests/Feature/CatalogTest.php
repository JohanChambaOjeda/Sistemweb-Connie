<?php

use App\Models\BoxType;
use App\Models\FlowerVariety;
use App\Models\Product;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

it('crea una variedad con foto', function () {
    Storage::fake('public');
    actingAsRole('admin');

    $this->post(route('varieties.store'), [
        'name' => 'Explorer',
        'color' => 'Rojo',
        'type' => 'standard',
        'photo' => UploadedFile::fake()->image('explorer.jpg'),
        'is_active' => true,
    ])->assertRedirect(route('varieties.index'));

    $variety = FlowerVariety::query()->firstWhere('name', 'Explorer');

    expect($variety)->not->toBeNull()
        ->and($variety->photo_path)->not->toBeNull();

    Storage::disk('public')->assertExists($variety->photo_path);
});

it('no repite el nombre de la variedad en la misma empresa', function () {
    actingAsRole('admin');
    FlowerVariety::factory()->create(['name' => 'Mondial']);

    $this->post(route('varieties.store'), [
        'name' => 'Mondial',
        'type' => 'standard',
        'is_active' => true,
    ])->assertSessionHasErrors('name');
});

it('crea un producto con variedad, largo y tallos', function () {
    actingAsRole('admin');
    $variety = FlowerVariety::factory()->create(['name' => 'Gotcha']);

    $this->post(route('products.store'), [
        'flower_variety_id' => $variety->id,
        'stem_length_cm' => 60,
        'stems_per_bunch' => 25,
        'is_active' => true,
    ])->assertRedirect(route('products.index'));

    $product = Product::query()->with('flowerVariety')->firstWhere('stem_length_cm', 60);

    expect($product)->not->toBeNull()
        ->and($product->label)->toBe('Gotcha 60 cm x25');
});

it('no repite variedad, largo y tallos', function () {
    actingAsRole('admin');
    $variety = FlowerVariety::factory()->create();
    Product::factory()->create([
        'flower_variety_id' => $variety->id,
        'stem_length_cm' => 70,
        'stems_per_bunch' => 25,
    ]);

    $this->post(route('products.store'), [
        'flower_variety_id' => $variety->id,
        'stem_length_cm' => 70,
        'stems_per_bunch' => 25,
        'is_active' => true,
    ])->assertSessionHasErrors('stem_length_cm');
});

it('crea tipos de caja con su factor', function () {
    actingAsRole('admin');

    $this->post(route('box-types.store'), [
        'code' => 'qb',
        'name' => 'Quarter box',
        'full_box_factor' => 0.25,
        'default_bunches_per_box' => 5,
        'is_active' => true,
    ])->assertRedirect(route('box-types.index'));

    $boxType = BoxType::query()->firstWhere('code', 'QB');

    expect($boxType)->not->toBeNull()
        ->and((float) $boxType->full_box_factor)->toBe(0.25);
});

it('aisla el catálogo entre empresas', function () {
    assertCompanyScoped(FlowerVariety::class);
    assertCompanyScoped(Product::class);
    assertCompanyScoped(BoxType::class);
});

it('deja al jefe de poscosecha administrar el catálogo pero no los precios', function () {
    $company = companyWithModules();
    actingAsRole('postharvest_lead', $company);

    $this->get(route('varieties.create'))->assertOk();
    $this->get(route('prices.index'))->assertForbidden();
});
