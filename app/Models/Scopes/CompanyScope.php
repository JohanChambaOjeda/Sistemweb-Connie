<?php

namespace App\Models\Scopes;

use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/**
 * Restricts every query to the company held in the current context.
 */
class CompanyScope implements Scope
{
    /**
     * @param  Builder<Model>  $builder
     */
    public function apply(Builder $builder, Model $model): void
    {
        $companyId = CompanyContext::id();

        if ($companyId === null) {
            return;
        }

        $builder->where($model->qualifyColumn('company_id'), $companyId);
    }
}
