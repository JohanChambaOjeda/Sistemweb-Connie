<?php

namespace App\Policies;

class MarkPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return 'marks';
    }
}
