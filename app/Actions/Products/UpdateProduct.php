<?php

namespace App\Actions\Products;

use App\Models\Product;

class UpdateProduct
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->refresh();
    }
}
