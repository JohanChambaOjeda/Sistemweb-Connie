<?php

namespace App\Actions\Users;

use App\Models\User;

/**
 * Los usuarios no se borran: se desactivan y dejan de poder entrar.
 */
class ToggleUserActive
{
    public function handle(User $user, bool $isActive): User
    {
        $user->forceFill(['is_active' => $isActive])->save();

        return $user->refresh();
    }
}
