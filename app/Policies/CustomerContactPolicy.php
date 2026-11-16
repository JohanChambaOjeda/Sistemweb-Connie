<?php

namespace App\Policies;

class CustomerContactPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return 'customer_contacts';
    }
}
