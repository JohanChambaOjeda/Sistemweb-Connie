<?php

namespace Database\Factories;

use App\Enums\Incoterm;
use App\Models\Customer;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    use AssignsCompany;

    protected $model = Customer::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => $this->companyId(),
            'name' => fake()->unique()->company().' Flowers',
            'country' => fake()->randomElement(['United States', 'Russia', 'Netherlands', 'Canada', 'Spain']),
            'city' => fake()->city(),
            'locale' => 'en',
            'invoice_email' => fake()->unique()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'whatsapp' => fake()->phoneNumber(),
            'incoterm' => fake()->randomElement(Incoterm::values()),
            'credit_days' => fake()->randomElement([0, 15, 30, 45]),
            'credit_limit_cents' => fake()->numberBetween(0, 5_000_000),
            'is_active' => true,
            'notes' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => ['is_active' => false]);
    }
}
