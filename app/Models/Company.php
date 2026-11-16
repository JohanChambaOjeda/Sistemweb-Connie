<?php

namespace App\Models;

use App\Enums\ModuleKey;
use App\Models\Concerns\LogsBusinessActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    /** @use HasFactory<\Database\Factories\CompanyFactory> */
    use HasFactory, LogsBusinessActivity, SoftDeletes;

    /**
     * Cached module keys so the menu does not query on every check.
     *
     * @var list<string>|null
     */
    private ?array $activeModuleKeys = null;

    /** @var list<string> */
    protected $fillable = [
        'name',
        'legal_name',
        'tax_id',
        'address',
        'phone',
        'email',
        'logo_path',
        'default_locale',
        'timezone',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return BelongsToMany<User, $this>
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }

    /**
     * @return BelongsToMany<Module, $this>
     */
    public function modules(): BelongsToMany
    {
        return $this->belongsToMany(Module::class, 'company_modules')
            ->withPivot('is_active')
            ->withTimestamps();
    }

    /**
     * Keys of the modules this company pays for, in menu order.
     *
     * @return list<string>
     */
    public function activeModuleKeys(): array
    {
        if ($this->activeModuleKeys !== null) {
            return $this->activeModuleKeys;
        }

        /** @var list<string> $keys */
        $keys = $this->modules()
            ->wherePivot('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('key')
            ->pluck('key')
            ->map(fn (ModuleKey|string $key) => $key instanceof ModuleKey ? $key->value : $key)
            ->all();

        return $this->activeModuleKeys = $keys;
    }

    public function hasModule(ModuleKey|string $key): bool
    {
        $value = $key instanceof ModuleKey ? $key->value : $key;

        return in_array($value, $this->activeModuleKeys(), true);
    }

    public function forgetActiveModuleKeys(): void
    {
        $this->activeModuleKeys = null;
    }
}
