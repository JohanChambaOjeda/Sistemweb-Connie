<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsBusinessActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * A customer's brand code, with its consignee and freight agency. One customer
 * may have several marks.
 */
class Mark extends Model
{
    /** @use HasFactory<\Database\Factories\MarkFactory> */
    use BelongsToCompany, HasFactory, LogsBusinessActivity, SoftDeletes;

    /** @var list<string> */
    protected $fillable = [
        'customer_id',
        'cargo_agency_id',
        'code',
        'consignee',
        'destination_country',
        'destination_city',
        'is_active',
        'notes',
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
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return BelongsTo<CargoAgency, $this>
     */
    public function cargoAgency(): BelongsTo
    {
        return $this->belongsTo(CargoAgency::class);
    }
}
