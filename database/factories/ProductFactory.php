<?php

namespace Database\Factories;

use App\Models\FlowerVariety;
use App\Models\Product;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    use AssignsCompany;

    protected $model = Product::class;

    /** @var list<int> */
    public const LENGTHS = [40, 50, 60, 70, 80, 90];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => $this->companyId(),
            'flower_variety_id' => FlowerVariety::factory(),
            'stem_length_cm' => fake()->randomElement(self::LENGTHS),
            'stems_per_bunch' => 25,
            'is_active' => true,
        ];
    }
}
