<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Crea un usuario del back office, le asigna su rol y las empresas a las que
 * puede entrar.
 */
class CreateUser
{
    /**
     * @param  array<string, mixed>  $data
     * @param  list<int>  $companyIds
     */
    public function handle(array $data, string $role, array $companyIds): User
    {
        return DB::transaction(function () use ($data, $role, $companyIds) {
            $user = User::create($data);

            $user->companies()->sync($companyIds);
            $user->syncRoles([$role]);

            $user->forceFill(['current_company_id' => $companyIds[0] ?? null])->save();

            return $user->refresh();
        });
    }
}
