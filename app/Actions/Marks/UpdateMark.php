<?php

namespace App\Actions\Marks;

use App\Models\Mark;

class UpdateMark
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(Mark $mark, array $data): Mark
    {
        $mark->update($data);

        return $mark->refresh();
    }
}
