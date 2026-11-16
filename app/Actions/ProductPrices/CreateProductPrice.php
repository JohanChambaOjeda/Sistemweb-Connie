<?php

namespace App\Actions\ProductPrices;

use App\Models\ProductPrice;

/**
 * Crea un registro de ProductPrice. company_id lo pone el trait BelongsToCompany.
 */
class CreateProductPrice
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): ProductPrice
    {
        return ProductPrice::create($data);
    }
}
