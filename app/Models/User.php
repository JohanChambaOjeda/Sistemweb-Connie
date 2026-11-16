<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'email',
        'password',
        'locale',
        'is_active',
        'current_company_id',
    ];

    /** @var list<string> */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<Company, $this>
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class);
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function currentCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'current_company_id');
    }

    /**
     * The company this user works on right now: the one stored on the user when
     * they still belong to it, otherwise the first company assigned to them.
     */
    public function resolveCurrentCompany(): ?Company
    {
        $companies = $this->companies()->get();

        $current = $companies->firstWhere('id', $this->current_company_id);

        if ($current instanceof Company) {
            return $current;
        }

        $fallback = $companies->first();

        if ($fallback instanceof Company) {
            $this->forceFill(['current_company_id' => $fallback->getKey()])->save();
        }

        return $fallback;
    }

    public function belongsToCompany(Company|int $company): bool
    {
        $companyId = $company instanceof Company ? $company->getKey() : $company;

        return $this->companies()->whereKey($companyId)->exists();
    }
}
