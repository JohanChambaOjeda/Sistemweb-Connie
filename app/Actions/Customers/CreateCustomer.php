<?php

namespace App\Actions\Customers;

use App\Models\Customer;

/**
 * Crea un registro de Customer. company_id lo pone el trait BelongsToCompany.
 */
class CreateCustomer
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Customer
    {
        return Customer::create($data);
    }
}
