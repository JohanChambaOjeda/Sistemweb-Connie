<?php

namespace App\Actions\ProductPrices;

use App\Models\ProductPrice;

class UpdateProductPrice
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(ProductPrice $price, array $data): ProductPrice
    {
        $price->update($data);

        return $price->refresh();
    }
}
