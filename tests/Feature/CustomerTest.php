<?php

use App\Models\Customer;
use App\Models\CustomerContact;

it('lista los clientes de la empresa activa', function () {
    actingAsRole('admin');
    $customer = Customer::factory()->create(['name' => 'Flores del Norte']);

    $this->get(route('customers.index'))
        ->assertOk()
        ->assertInertia(fn ($page) => $page
            ->component('customers/index')
            ->where('customers.data.0.name', $customer->name));
});

it('crea un cliente', function () {
    actingAsRole('admin');

    $this->post(route('customers.store'), [
        'name' => 'Miami Roses',
        'country' => 'United States',
        'city' => 'Miami',
        'locale' => 'en',
        'invoice_email' => 'pagos@miamiroses.com',
        'phone' => '+1 305 555 1234',
        'whatsapp' => '',
        'incoterm' => 'FOB',
        'credit_days' => 30,
        'credit_limit' => '15000.50',
        'is_active' => true,
        'notes' => '',
    ])->assertRedirect(route('customers.index'));

    $customer = Customer::query()->firstWhere('name', 'Miami Roses');

    expect($customer)->not->toBeNull()
        ->and($customer->credit_limit_cents)->toBe(1500050)
        ->and($customer->incoterm->value)->toBe('FOB');
});

it('no acepta dos clientes con el mismo nombre en la empresa', function () {
    actingAsRole('admin');
    Customer::factory()->create(['name' => 'Miami Roses']);

    $this->post(route('customers.store'), [
        'name' => 'Miami Roses',
        'locale' => 'en',
        'incoterm' => 'FOB',
        'credit_days' => 0,
        'credit_limit' => '0',
        'is_active' => true,
    ])->assertSessionHasErrors('name');
});

it('actualiza un cliente', function () {
    actingAsRole('admin');
    $customer = Customer::factory()->create(['name' => 'Antes', 'credit_days' => 0]);

    $this->put(route('customers.update', $customer), [
        'name' => 'Después',
        'country' => 'Russia',
        'city' => 'Moscow',
        'locale' => 'en',
        'invoice_email' => null,
        'phone' => null,
        'whatsapp' => null,
        'incoterm' => 'CIF',
        'credit_days' => 45,
        'credit_limit' => '2000',
        'is_active' => false,
        'notes' => null,
    ])->assertRedirect(route('customers.index'));

    $customer->refresh();

    expect($customer->name)->toBe('Después')
        ->and($customer->credit_days)->toBe(45)
        ->and($customer->is_active)->toBeFalse();
});

it('elimina un cliente de forma suave', function () {
    actingAsRole('admin');
    $customer = Customer::factory()->create();

    $this->delete(route('customers.destroy', $customer))->assertRedirect(route('customers.index'));

    expect(Customer::query()->find($customer->id))->toBeNull()
        ->and(Customer::withTrashed()->find($customer->id))->not->toBeNull();
});

it('no deja ver el cliente de otra empresa', function () {
    $otherCompany = companyWithModules();
    $otherCustomer = App\Support\CompanyContext::runFor(
        $otherCompany,
        fn () => Customer::factory()->create(['name' => 'De otra finca']),
    );

    actingAsRole('admin');

    $this->get(route('customers.show', $otherCustomer))->assertNotFound();
    $this->put(route('customers.update', $otherCustomer), ['name' => 'Hackeado'])->assertNotFound();
});

it('aisla los clientes entre empresas', function () {
    assertCompanyScoped(Customer::class);
});

it('aisla los contactos entre empresas', function () {
    assertCompanyScoped(CustomerContact::class);
});

it('deja a ventas crear clientes pero no a poscosecha', function () {
    $company = companyWithModules();

    actingAsRole('sales', $company);
    $this->get(route('customers.create'))->assertOk();

    actingAsRole('postharvest_lead', $company);
    $this->get(route('customers.create'))->assertForbidden();
});

it('administra los contactos desde la ficha del cliente', function () {
    actingAsRole('admin');
    $customer = Customer::factory()->create();

    $this->post(route('customer-contacts.store'), [
        'customer_id' => $customer->id,
        'name' => 'Ana Pérez',
        'position' => 'Compras',
        'email' => 'ana@cliente.com',
        'whatsapp' => '',
        'receives_invoices' => true,
        'receives_statements' => false,
    ])->assertRedirect(route('customers.show', $customer->id));

    $contact = CustomerContact::query()->firstWhere('name', 'Ana Pérez');

    expect($contact)->not->toBeNull()
        ->and($contact->customer_id)->toBe($customer->id)
        ->and($contact->receives_invoices)->toBeTrue();

    $this->delete(route('customer-contacts.destroy', $contact))
        ->assertRedirect(route('customers.show', $customer->id));

    expect(CustomerContact::query()->find($contact->id))->toBeNull();
});
