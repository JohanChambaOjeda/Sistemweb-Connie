<?php

namespace App\Models;

use App\Enums\VarietyType;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsBusinessActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Rose variety grown by the farm: Explorer, Hot Spot, Mondial...
 */
class FlowerVariety extends Model
{
    /** @use HasFactory<\Database\Factories\FlowerVarietyFactory> */
    use BelongsToCompany, HasFactory, LogsBusinessActivity, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'color',
        'photo_path',
        'type',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => VarietyType::class,
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
