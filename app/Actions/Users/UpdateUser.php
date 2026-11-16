<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class UpdateUser
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $companyIds
     */
    public function handle(User $user, array $data, string $role, array $companyIds): User
    {
        return DB::transaction(function () use ($user, $data, $role, $companyIds) {
            $user->update($data);

            $user->companies()->sync($companyIds);
            $user->syncRoles([$role]);

            // Si le quitaron la empresa activa, se mueve a la primera que le quede.
            if (! in_array($user->current_company_id, $companyIds, true)) {
                $user->forceFill(['current_company_id' => $companyIds[0] ?? null])->save();
            }

            return $user->refresh();
        });
    }
}
