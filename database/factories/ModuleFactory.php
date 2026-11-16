<?php

namespace Database\Factories;

use App\Enums\ModuleKey;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    protected $model = Module::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'key' => fake()->randomElement(ModuleKey::values()),
            'sort_order' => 0,
        ];
    }

    public function key(ModuleKey $key): static
    {
        return $this->state(fn (array $attributes) => [
            'key' => $key->value,
        ]);
    }
}
