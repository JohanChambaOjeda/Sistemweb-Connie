<?php

use App\Models\CargoAgency;

it('lista y crea agencias de carga', function () {
    actingAsRole('admin');
    CargoAgency::factory()->create(['name' => 'Panatlantic']);

    $this->get(route('cargo-agencies.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page->component('cargo-agencies/index'));

    $this->post(route('cargo-agencies.store'), [
        'name' => 'Aerovic Cargo',
        'tax_id' => '1792146739001',
        'contact_name' => 'Luis Vega',
        'phone' => '022345678',
        'email' => 'ops@aerovic.com',
        'cutoff_time' => '16:30',
        'is_active' => true,
        'notes' => null,
    ])->assertRedirect(route('cargo-agencies.index'));

    $agency = CargoAgency::query()->firstWhere('name', 'Aerovic Cargo');

    expect($agency)->not->toBeNull()
        ->and($agency->cutoff_time)->toContain('16:30');
});

it('rechaza una hora de corte inválida', function () {
    actingAsRole('admin');

    $this->post(route('cargo-agencies.store'), [
        'name' => 'Agencia X',
        'cutoff_time' => 'tarde',
        'is_active' => true,
    ])->assertSessionHasErrors('cutoff_time');
});

it('actualiza y elimina una agencia', function () {
    actingAsRole('admin');
    $agency = CargoAgency::factory()->create(['name' => 'Antes']);

    $this->put(route('cargo-agencies.update', $agency), [
        'name' => 'Después',
        'tax_id' => null,
        'contact_name' => null,
        'phone' => null,
        'email' => null,
        'cutoff_time' => null,
        'is_active' => true,
        'notes' => null,
    ])->assertRedirect(route('cargo-agencies.index'));

    expect($agency->refresh()->name)->toBe('Después');

    $this->delete(route('cargo-agencies.destroy', $agency))->assertRedirect(route('cargo-agencies.index'));

    expect(CargoAgency::query()->find($agency->id))->toBeNull();
});

it('aisla las agencias entre empresas', function () {
    assertCompanyScoped(CargoAgency::class);
});

it('solo deja ver agencias a quien tiene el permiso', function () {
    $company = companyWithModules();

    actingAsRole('billing', $company);
    $this->get(route('cargo-agencies.index'))->assertOk();

    actingAsRole('warehouse_lead', $company);
    $this->get(route('cargo-agencies.index'))->assertForbidden();
});
