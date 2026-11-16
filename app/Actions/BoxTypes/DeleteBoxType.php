<?php

namespace App\Actions\BoxTypes;

use App\Models\BoxType;

/**
 * Borrado suave: el registro queda en la base con deleted_at y en la bitácora.
 */
class DeleteBoxType
{
    public function handle(BoxType $boxType): void
    {
        $boxType->delete();
    }
}
