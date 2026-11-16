<?php

namespace App\Actions\CustomerContacts;

use App\Models\CustomerContact;

/**
 * Crea un registro de CustomerContact. company_id lo pone el trait BelongsToCompany.
 */
class CreateCustomerContact
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): CustomerContact
    {
        return CustomerContact::create($data);
    }
}
