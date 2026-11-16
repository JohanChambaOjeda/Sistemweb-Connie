<?php

namespace App\Actions\CargoAgencies;

use App\Models\CargoAgency;

/**
 * Crea un registro de CargoAgency. company_id lo pone el trait BelongsToCompany.
 */
class CreateCargoAgency
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): CargoAgency
    {
        return CargoAgency::create($data);
    }
}
