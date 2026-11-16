<?php

namespace App\Actions\Customers;

use App\Models\Customer;

/**
 * Borrado suave: el registro queda en la base con deleted_at y en la bitácora.
 */
class DeleteCustomer
{
    public function handle(Customer $customer): void
    {
        $customer->delete();
    }
}
