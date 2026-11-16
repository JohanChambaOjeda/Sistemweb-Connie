<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\CustomerContact;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CustomerContact>
 */
class CustomerContactFactory extends Factory
{
    use AssignsCompany;

    protected $model = CustomerContact::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => $this->companyId(),
            'customer_id' => Customer::factory(),
            'name' => fake()->name(),
            'position' => fake()->randomElement(['Buyer', 'Accounting', 'Logistics', 'Owner']),
            'email' => fake()->unique()->safeEmail(),
            'whatsapp' => fake()->phoneNumber(),
            'receives_invoices' => fake()->boolean(),
            'receives_statements' => fake()->boolean(),
        ];
    }
}
