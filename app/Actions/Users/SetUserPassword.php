<?php

namespace App\Actions\Users;

use App\Models\User;

class SetUserPassword
{
    public function handle(User $user, string $password): void
    {
        $user->forceFill(['password' => $password])->save();
    }
}
