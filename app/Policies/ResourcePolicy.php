<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Base policy for master data: every action maps to a spatie permission named
 * <resource>.<action>, and nothing from another company is ever visible.
 */
abstract class ResourcePolicy
{
    /**
     * Permission prefix, e.g. 'customers'.
     */
    abstract protected function resource(): string;

    public function viewAny(User $user): bool
    {
        return $user->can($this->resource().'.view');
    }

    public function view(User $user, Model $model): bool
    {
        return $user->can($this->resource().'.view') && $this->sameCompany($user, $model);
    }

    public function create(User $user): bool
    {
        return $user->can($this->resource().'.create');
    }

    public function update(User $user, Model $model): bool
    {
        return $user->can($this->resource().'.update') && $this->sameCompany($user, $model);
    }

    public function delete(User $user, Model $model): bool
    {
        return $user->can($this->resource().'.delete') && $this->sameCompany($user, $model);
    }

    protected function sameCompany(User $user, Model $model): bool
    {
        $companyId = $model->getAttribute('company_id');

        if ($companyId === null) {
            return true;
        }

        return $user->belongsToCompany((int) $companyId);
    }
}
