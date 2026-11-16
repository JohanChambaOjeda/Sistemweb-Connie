<?php

namespace App\Actions\Marks;

use App\Models\Mark;

/**
 * Borrado suave: el registro queda en la base con deleted_at y en la bitácora.
 */
class DeleteMark
{
    public function handle(Mark $mark): void
    {
        $mark->delete();
    }
}
