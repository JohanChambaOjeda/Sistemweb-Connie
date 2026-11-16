<?php

namespace App\Policies;

class FlowerVarietyPolicy extends ResourcePolicy
{
    protected function resource(): string
    {
        return 'flower_varieties';
    }
}
