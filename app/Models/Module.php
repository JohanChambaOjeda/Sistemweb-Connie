<?php

namespace App\Models;

use App\Enums\ModuleKey;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property ModuleKey $key
 * @property int $sort_order
 */
class Module extends Model
{
    /** @use HasFactory<\Database\Factories\ModuleFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $fillable = [
        'key',
        'sort_order',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'key' => ModuleKey::class,
            'sort_order' => 'integer',
        ];
    }

    /**
     * @return BelongsToMany<Company, $this>
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_modules')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    public function label(): string
    {
        return $this->key->label();
    }
}
