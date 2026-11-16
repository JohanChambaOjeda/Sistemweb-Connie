<?php

use App\Models\CargoAgency;
use App\Models\Customer;
use App\Models\Mark;

it('crea una marcación para un cliente', function () {
    actingAsRole('admin');
    $customer = Customer::factory()->create();
    $agency = CargoAgency::factory()->create();

    $this->post(route('marks.store'), [
        'customer_id' => $customer->id,
        'cargo_agency_id' => $agency->id,
        'code' => 'MR-001',
        'consignee' => 'Miami Roses LLC',
        'destination_country' => 'United States',
        'destination_city' => 'Miami',
        'is_active' => true,
        'notes' => null,
    ])->assertRedirect(route('marks.index'));

    $mark = Mark::query()->firstWhere('code', 'MR-001');

    expect($mark)->not->toBeNull()
        ->and($mark->customer_id)->toBe($customer->id)
        ->and($mark->cargo_agency_id)->toBe($agency->id);
});

it('permite el mismo código en clientes distintos pero no en el mismo cliente', function () {
    actingAsRole('admin');
    $first = Customer::factory()->create();
    $second = Customer::factory()->create();

    Mark::factory()->create(['customer_id' => $first->id, 'code' => 'AB-001']);

    $this->post(route('marks.store'), [
        'customer_id' => $second->id,
        'code' => 'AB-001',
        'is_active' => true,
    ])->assertRedirect(route('marks.index'));

    $this->post(route('marks.store'), [
        'customer_id' => $first->id,
        'code' => 'AB-001',
        'is_active' => true,
    ])->assertSessionHasErrors('code');
});

it('un cliente puede tener varias marcaciones', function () {
    actingAsRole('admin');
    $customer = Customer::factory()->create();
    Mark::factory()->count(3)->create(['customer_id' => $customer->id]);

    expect($customer->marks()->count())->toBe(3);
});

it('aisla las marcaciones entre empresas', function () {
    assertCompanyScoped(Mark::class);
});
