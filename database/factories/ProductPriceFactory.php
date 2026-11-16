<?php

namespace Database\Factories;

use App\Enums\Season;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductPrice;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductPrice>
 */
class ProductPriceFactory extends Factory
{
    use AssignsCompany;

    protected $model = ProductPrice::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => $this->companyId(),
            'product_id' => Product::factory(),
            'customer_id' => null,
            'season' => null,
            'price_per_stem' => fake()->randomFloat(4, 0.25, 1.2),
            'valid_from' => now()->startOfYear()->toDateString(),
            'valid_to' => null,
            'is_active' => true,
            'notes' => null,
        ];
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => ['customer_id' => $customer->getKey()]);
    }

    public function forSeason(Season $season): static
    {
        return $this->state(fn (array $attributes) => ['season' => $season->value]);
    }
}
