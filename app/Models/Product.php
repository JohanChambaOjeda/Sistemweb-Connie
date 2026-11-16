<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsBusinessActivity;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * What the farm sells: a variety at a given stem length, bunched in a given
 * number of stems (25 by default).
 */
class Product extends Model
{
    /** @use HasFactory<\Database\Factories\ProductFactory> */
    use BelongsToCompany, HasFactory, LogsBusinessActivity, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'flower_variety_id',
        'stem_length_cm',
        'stems_per_bunch',
        'is_active',
    ];

    /** @var list<string> */
    protected $appends = ['label'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stem_length_cm' => 'integer',
            'stems_per_bunch' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * How the product reads on screens and documents: "Explorer 60 cm x25".
     *
     * @return Attribute<string, never>
     */
    protected function label(): Attribute
    {
        return Attribute::get(function (): string {
            // getRelationValue loads the variety when needed and returns null
            // when it was soft deleted.
            $variety = $this->getRelationValue('flowerVariety');

            return trim(sprintf(
                '%s %d cm x%d',
                $variety instanceof FlowerVariety ? $variety->name : '',
                $this->stem_length_cm,
                $this->stems_per_bunch,
            ));
        });
    }

    /**
     * @return BelongsTo<FlowerVariety, $this>
     */
    public function flowerVariety(): BelongsTo
    {
        return $this->belongsTo(FlowerVariety::class);
    }

    /**
     * @return HasMany<ProductPrice, $this>
     */
    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class);
    }
}
