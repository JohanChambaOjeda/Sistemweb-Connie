<?php

namespace App\Actions\BoxTypes;

use App\Models\BoxType;

class UpdateBoxType
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(BoxType $boxType, array $data): BoxType
    {
        $boxType->update($data);

        return $boxType->refresh();
    }
}
