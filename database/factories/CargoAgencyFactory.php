<?php

namespace Database\Factories;

use App\Models\CargoAgency;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CargoAgency>
 */
class CargoAgencyFactory extends Factory
{
    use AssignsCompany;

    protected $model = CargoAgency::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => $this->companyId(),
            'name' => fake()->unique()->randomElement([
                'Panatlantic Logistics', 'Aerovic Cargo', 'Frutierrez Cargo', 'Siatipack', 'Logistic Flowers',
            ]).' '.fake()->unique()->numerify('##'),
            'tax_id' => fake()->numerify('#############001'),
            'contact_name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'email' => fake()->unique()->companyEmail(),
            'cutoff_time' => fake()->randomElement(['14:00', '16:30', '18:00']),
            'is_active' => true,
            'notes' => null,
        ];
    }
}
