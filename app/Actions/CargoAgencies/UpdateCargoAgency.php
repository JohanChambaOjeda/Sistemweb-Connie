<?php

namespace App\Actions\CargoAgencies;

use App\Models\CargoAgency;

class UpdateCargoAgency
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(CargoAgency $agency, array $data): CargoAgency
    {
        $agency->update($data);

        return $agency->refresh();
    }
}
