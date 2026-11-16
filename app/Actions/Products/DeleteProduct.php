<?php

namespace App\Actions\Products;

use App\Models\Product;

/**
 * Borrado suave: el registro queda en la base con deleted_at y en la bitácora.
 */
class DeleteProduct
{
    public function handle(Product $product): void
    {
        $product->delete();
    }
}
