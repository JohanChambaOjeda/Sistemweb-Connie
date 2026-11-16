<?php

namespace App\Actions\Marks;

use App\Models\Mark;

/**
 * Crea un registro de Mark. company_id lo pone el trait BelongsToCompany.
 */
class CreateMark
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): Mark
    {
        return Mark::create($data);
    }
}
