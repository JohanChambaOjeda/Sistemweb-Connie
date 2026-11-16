<?php

namespace App\Actions\FlowerVarieties;

use App\Models\FlowerVariety;
use Illuminate\Http\UploadedFile;

class CreateFlowerVariety
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(array $data, ?UploadedFile $photo = null): FlowerVariety
    {
        if ($photo instanceof UploadedFile) {
            $data['photo_path'] = $photo->store('varieties', 'public');
        }

        return FlowerVariety::create($data);
    }
}
