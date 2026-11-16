<?php

namespace App\Actions\CargoAgencies;

use App\Models\CargoAgency;

/**
 * Borrado suave: el registro queda en la base con deleted_at y en la bitácora.
 */
class DeleteCargoAgency
{
    public function handle(CargoAgency $agency): void
    {
        $agency->delete();
    }
}
