<?php

namespace App\Policies;

class ProductPricePolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return 'product_prices';
    }
}
