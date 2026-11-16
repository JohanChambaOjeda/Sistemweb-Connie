<?php

namespace Database\Factories;

use App\Models\BoxType;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<BoxType>
 */
class BoxTypeFactory extends Factory
{
    use AssignsCompany;

    protected $model = BoxType::class;

    /**
     * Standard formats: quarter, half and full box.
     *
     * @var array<string, array{name: string, factor: float, bunches: int}>
     */
    public const TYPES = [
        'QB' => ['name' => 'Quarter box', 'factor' => 0.25, 'bunches' => 5],
        'HB' => ['name' => 'Half box', 'factor' => 0.5, 'bunches' => 10],
        'FB' => ['name' => 'Full box', 'factor' => 1.0, 'bunches' => 20],
    ];

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $code = fake()->unique()->randomElement(array_keys(self::TYPES));

        return [
            'company_id' => $this->companyId(),
            'code' => $code,
            'name' => self::TYPES[$code]['name'],
            'full_box_factor' => self::TYPES[$code]['factor'],
            'default_bunches_per_box' => self::TYPES[$code]['bunches'],
            'is_active' => true,
        ];
    }
}
