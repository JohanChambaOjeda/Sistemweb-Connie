<?php

namespace App\Actions\Customers;

use App\Models\Customer;

class UpdateCustomer
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Customer $customer, array $data): Customer
    {
        $customer->update($data);

        return $customer->refresh();
    }
}
