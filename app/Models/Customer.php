<?php

namespace App\Models;

use App\Enums\Incoterm;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsBusinessActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * An importer: the company that buys the flowers.
 */
class Customer extends Model
{
    /** @use HasFactory<\Database\Factories\CustomerFactory> */
    use BelongsToCompany, HasFactory, LogsBusinessActivity, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'country',
        'city',
        'locale',
        'invoice_email',
        'phone',
        'whatsapp',
        'incoterm',
        'credit_days',
        'credit_limit_cents',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'incoterm' => Incoterm::class,
            'credit_days' => 'integer',
            'credit_limit_cents' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<CustomerContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(CustomerContact::class);
    }

    /**
     * @return HasMany<Mark, $this>
     */
    public function marks(): HasMany
    {
        return $this->hasMany(Mark::class);
    }

    /**
     * @return HasMany<ProductPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
