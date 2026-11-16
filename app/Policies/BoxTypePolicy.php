<?php

namespace App\Policies;

class BoxTypePolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return 'box_types';
    }
}
