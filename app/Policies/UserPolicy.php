<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class UserPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return 'users';
    }

    /**
     * Nadie se puede desactivar a sí mismo.
     */
    public function delete(User $user, Model $model): bool
    {
        return parent::delete($user, $model) && $user->getKey() !== $model->getKey();
    }

    /**
     * Un admin solo administra usuarios de su propia empresa.
     */
    protected function sameCompany(User $user, Model $model): bool
    {
        $companyId = $user->current_company_id;

        if (! $model instanceof User || $companyId === null) {
            return false;
        }

        return $model->belongsToCompany((int) $companyId);
    }
}
