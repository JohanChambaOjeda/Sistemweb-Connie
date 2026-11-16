<?php

namespace App\Policies;

class CargoAgencyPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return 'cargo_agencies';
    }
}
