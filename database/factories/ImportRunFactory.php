<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\ImportRun;
use Database\Factories\Concerns\AssignsCompany;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImportRun>
 */
class ImportRunFactory extends Factory
{
    use AssignsCompany;

    protected $model = ImportRun::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'company_id' => $this->companyId(),
            'user_id' => null,
            'type' => 'customers',
            'original_filename' => 'clientes.xlsx',
            'stored_path' => 'imports/'.fake()->uuid().'.xlsx',
            'header_row' => 1,
            'column_map' => [],
            'status' => ImportStatus::Uploaded->value,
        ];
    }
}
