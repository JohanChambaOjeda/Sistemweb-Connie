<?php

namespace App\Actions\FlowerVarieties;

use App\Models\FlowerVariety;

/**
 * Borrado suave. La foto se conserva porque el registro se puede restaurar.
 */
class DeleteFlowerVariety
{
    public function handle(FlowerVariety $variety): void
    {
        $variety->delete();
    }
}
