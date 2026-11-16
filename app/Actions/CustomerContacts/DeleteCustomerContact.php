<?php

namespace App\Actions\CustomerContacts;

use App\Models\CustomerContact;

/**
 * Borrado suave: el registro queda en la base con deleted_at y en la bitácora.
 */
class DeleteCustomerContact
{
    public function handle(CustomerContact $contact): void
    {
        $contact->delete();
    }
}
