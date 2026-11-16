<?php

namespace Database\Factories\Concerns;

use App\Models\Company;
use App\Support\CompanyContext;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factories reuse the active company when there is one (tests, importers) and
 * create a new company otherwise (seeders).
 */
trait AssignsCompany
{
    /**
     * @return int|Factory<Company>
     */
    protected function companyId(): int|Factory
    {
        return CompanyContext::id() ?? Company::factory();
    }
}
