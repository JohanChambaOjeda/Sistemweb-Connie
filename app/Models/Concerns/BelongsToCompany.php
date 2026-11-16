<?php

namespace App\Models\Concerns;

use App\Models\Company;
use App\Models\Scopes\CompanyScope;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Every business table carries a company_id. This trait filters all queries by
 * the active company and fills company_id automatically when creating records.
 */
trait BelongsToCompany
{
    public static function bootBelongsToCompany(): void
    {
        static::addGlobalScope(new CompanyScope);

        static::creating(function (Model $model): void {
            if ($model->getAttribute('company_id') === null) {
                $model->setAttribute('company_id', CompanyContext::id());
            }
        });
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * Query the table across every company. Reserved for maintenance commands
     * and never used from a controller.
     *
     * @return Builder<static>
     */
    public static function acrossCompanies(): Builder
    {
        return static::query()->withoutGlobalScope(CompanyScope::class);
    }
}
