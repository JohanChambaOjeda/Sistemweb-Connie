<?php

namespace App\Models;

use App\Enums\Season;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsBusinessActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Price per stem in USD. A row with no customer is the base price, and a row
 * with a season only applies inside that season.
 */
class ProductPrice extends Model
{
    /** @use HasFactory<\Database\Factories\ProductPriceFactory> */
    use BelongsToCompany, HasFactory, LogsBusinessActivity, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'product_id',
        'customer_id',
        'season',
        'price_per_stem',
        'valid_from',
        'valid_to',
        'is_active',
        'notes',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'season' => Season::class,
            'price_per_stem' => 'decimal:4',
            'valid_from' => 'date',
            'valid_to' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * Rows in force on the given date.
     *
     * @param  Builder<ProductPrice>  $query
     * @return Builder<ProductPrice>
     */
    public function scopeInForceOn(Builder $query, Carbon $date): Builder
    {
        return $query->where('is_active', true)
            ->whereDate('valid_from', '<=', $date)
            ->where(function (Builder $builder) use ($date) {
                $builder->whereNull('valid_to')->orWhereDate('valid_to', '>=', $date);
            });
    }
}
