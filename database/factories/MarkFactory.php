<?php

namespace Database\Factories;

use App\Models\CargoAgency;
use App\Models\Customer;
use App\Models\Mark;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Mark>
 */
class MarkFactory extends Factory
{
    use AssignsCompany;

    protected $model = Mark::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => $this->companyId(),
            'customer_id' => Customer::factory(),
            'cargo_agency_id' => CargoAgency::factory(),
            'code' => strtoupper(fake()->unique()->bothify('??-###')),
            'consignee' => fake()->company(),
            'destination_country' => fake()->randomElement(['United States', 'Russia', 'Netherlands']),
            'destination_city' => fake()->randomElement(['Miami', 'Moscow', 'Amsterdam', 'Toronto']),
            'is_active' => true,
            'notes' => null,
        ];
    }
}
