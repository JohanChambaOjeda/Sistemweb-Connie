<?php

namespace App\Actions\CustomerContacts;

use App\Models\CustomerContact;

class UpdateCustomerContact
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CustomerContact $contact, array $data): CustomerContact
    {
        $contact->update($data);

        return $contact->refresh();
    }
}
