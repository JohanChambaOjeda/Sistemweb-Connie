<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsBusinessActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Box format: QB counts as 0.25 of a full box, HB 0.5 and FB 1.
 */
class BoxType extends Model
{
    /** @use HasFactory<\Database\Factories\BoxTypeFactory> */
    use BelongsToCompany, HasFactory, LogsBusinessActivity, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'code',
        'name',
        'full_box_factor',
        'default_bunches_per_box',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'full_box_factor' => 'decimal:3',
            'default_bunches_per_box' => 'integer',
            'is_active' => 'boolean',
        ];
    }
}
