<?php

namespace App\Actions\ProductPrices;

use App\Enums\Season;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;
use Illuminate\Support\Carbon;

/**
 * Devuelve el precio por tallo vigente para un producto, un cliente y una fecha.
 *
 * Prioridad: cliente + temporada > cliente > temporada > base. Dentro del mismo
 * nivel gana la fila con valid_from más reciente.
 */
class ResolvePrice
{
    public function handle(Product $product, ?Customer $customer = null, ?Carbon $date = null): ?ProductPrice
    {
        $date = $date ?? Carbon::today();
        $season = Season::forDate($date);

        $candidates = ProductPrice::query()
            ->where('product_id', $product->getKey())
            ->inForceOn($date)
            ->where(function ($query) use ($customer) {
                $query->whereNull('customer_id');

                if ($customer instanceof Customer) {
                    $query->orWhere('customer_id', $customer->getKey());
                }
            })
            ->where(function ($query) use ($season) {
                $query->whereNull('season');

                if ($season instanceof Season) {
                    $query->orWhere('season', $season->value);
                }
            })
            ->orderByDesc('valid_from')
            ->orderByDesc('id')
            ->get();

        $levels = [
            [true, true],
            [true, false],
            [false, true],
            [false, false],
        ];

        foreach ($levels as [$withCustomer, $withSeason]) {
            $match = $candidates->first(
                fn (ProductPrice $price) => ($price->customer_id !== null) === $withCustomer
                    && ($price->season !== null) === $withSeason
            );

            if ($match instanceof ProductPrice) {
                return $match;
            }
        }

        return null;
    }

    /**
     * El precio como número, o null si no hay tarifa vigente.
     */
    public function priceFor(Product $product, ?Customer $customer = null, ?Carbon $date = null): ?string
    {
        $price = $this->handle($product, $customer, $date);

        return $price === null ? null : (string) $price->price_per_stem;
    }
}
