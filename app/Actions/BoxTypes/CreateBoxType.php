<?php

namespace App\Actions\BoxTypes;

use App\Models\BoxType;

/**
 * Crea un registro de BoxType. company_id lo pone el trait BelongsToCompany.
 */
class CreateBoxType
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data): BoxType
    {
        return BoxType::create($data);
    }
}
