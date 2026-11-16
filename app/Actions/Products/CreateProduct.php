<?php

namespace App\Actions\Products;

use App\Models\Product;

/**
 * Crea un registro de Product. company_id lo pone el trait BelongsToCompany.
 */
class CreateProduct
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Product
    {
        return Product::create($data);
    }
}
