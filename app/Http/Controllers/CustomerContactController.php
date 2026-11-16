<?php

namespace App\Http\Controllers;

use App\Actions\CustomerContacts\CreateCustomerContact;
use App\Actions\CustomerContacts\DeleteCustomerContact;
use App\Actions\CustomerContacts\UpdateCustomerContact;
use App\Http\Requests\CustomerContactRequest;
use App\Models\CustomerContact;
use Illuminate\Http\RedirectResponse;

/**
 * Los contactos se administran desde la ficha del cliente, por eso no tienen
 * pantallas propias de listado.
 */
class CustomerContactController extends Controller
{
    public function store(CustomerContactRequest $request, CreateCustomerContact $action): RedirectResponse
    {
        $this->authorize('create', CustomerContact::class);

        $contact = $action->handle($request->validated());

        return to_route('customers.show', $contact->customer_id)
            ->with('success', __('contacts.messages.created'));
    }

    public function update(
        CustomerContactRequest $request,
        CustomerContact $contact,
        UpdateCustomerContact $action,
    ): RedirectResponse {
        $this->authorize('update', $contact);

        $action->handle($contact, $request->validated());

        return to_route('customers.show', $contact->customer_id)
            ->with('success', __('contacts.messages.updated'));
    }

    public function destroy(CustomerContact $contact, DeleteCustomerContact $action): RedirectResponse
    {
        $this->authorize('delete', $contact);

        $customerId = $contact->customer_id;
        $action->handle($contact);

        return to_route('customers.show', $customerId)
            ->with('success', __('contacts.messages.deleted'));
    }
}
