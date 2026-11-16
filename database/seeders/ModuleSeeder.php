<?php

namespace Database\Seeders;

use App\Enums\ModuleKey;
use App\Models\Module;
use Illuminate\Database\Seeder;

class ModuleSeeder extends Seeder
{
    public function run(): void
    {
        foreach (ModuleKey::cases() as $index => $key) {
            Module::query()->updateOrCreate(
                ['key' => $key->value],
                ['sort_order' => ($index + 1) * 10],
            );
        }
    }
}
