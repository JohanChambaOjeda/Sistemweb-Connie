<?php

namespace Database\Factories;

use App\Enums\VarietyType;
use App\Models\FlowerVariety;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FlowerVariety>
 */
class FlowerVarietyFactory extends Factory
{
    use AssignsCompany;

    protected $model = FlowerVariety::class;

    /**
     * Varieties really grown at Cielos de América EC.
     *
     * @var array<string, string>
     */
    public const VARIETIES = [
        'Explorer' => 'Rojo',
        'Hot Spot' => 'Bicolor',
        'Gotcha' => 'Rosado',
        'Sweet Spot' => 'Bicolor',
        'Be Sweet' => 'Rosado',
        'Fatima' => 'Durazno',
        'Vicky' => 'Blanco',
        'Mondial' => 'Blanco',
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->randomElement(array_keys(self::VARIETIES));

        return [
            'company_id' => $this->companyId(),
            'name' => $name,
            'color' => self::VARIETIES[$name],
            'photo_path' => null,
            'type' => VarietyType::Standard->value,
            'is_active' => true,
        ];
    }

    public function garden(): static
    {
        return $this->state(fn (array $attributes) => ['type' => VarietyType::Garden->value]);
    }
}
