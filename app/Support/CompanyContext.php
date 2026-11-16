<?php

namespace App\Support;

use App\Models\Company;
use Closure;

/**
 * Holds the company the current request (or job) is working on.
 *
 * Every business model scoped with the BelongsToCompany trait reads this
 * context, so nothing but the active company is ever visible.
 */
class CompanyContext
{
    private ?int $companyId = null;

    public static function instance(): self
    {
        return app(self::class);
    }

    public static function id(): ?int
    {
        return self::instance()->getId();
    }

    public static function set(Company|int|null $company): void
    {
        self::instance()->setId($company instanceof Company ? $company->getKey() : $company);
    }

    public static function forget(): void
    {
        self::instance()->setId(null);
    }

    /**
     * Run a callback with the given company as the active one and restore
     * the previous company afterwards.
     *
     * @template TReturn
     *
     * @param  Closure(): TReturn  $callback
     * @return TReturn
     */
    public static function runFor(Company|int|null $company, Closure $callback): mixed
    {
        $previous = self::id();
        self::set($company);

        try {
            return $callback();
        } finally {
            self::set($previous);
        }
    }

    public function getId(): ?int
    {
        return $this->companyId;
    }

    public function setId(?int $companyId): void
    {
        $this->companyId = $companyId;
    }
}
