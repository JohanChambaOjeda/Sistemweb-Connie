<?php

namespace App\Actions\ProductPrices;

use App\Models\ProductPrice;

/**
 * Borrado suave: el registro queda en la base con deleted_at y en la bitácora.
 */
class DeleteProductPrice
{
    public function handle(ProductPrice $price): void
    {
        $price->delete();
    }
}
