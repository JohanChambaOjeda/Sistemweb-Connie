<?php

namespace App\Actions\FlowerVarieties;

use App\Models\FlowerVariety;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UpdateFlowerVariety
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(FlowerVariety $variety, array $data, ?UploadedFile $photo = null): FlowerVariety
    {
        if ($photo instanceof UploadedFile) {
            $previous = $variety->photo_path;
            $data['photo_path'] = $photo->store('varieties', 'public');

            if (is_string($previous) && $previous !== '') {
                Storage::disk('public')->delete($previous);
            }
        }

        $variety->update($data);

        return $variety->refresh();
    }
}
