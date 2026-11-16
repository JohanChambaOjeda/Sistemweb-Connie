<?php

namespace App\Models;

use App\Enums\ImportStatus;
use App\Models\Concerns\BelongsToCompany;
use App\Models\Concerns\LogsBusinessActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Una corrida de importación: el archivo subido, el mapeo de columnas que se
 * eligió y el resultado (filas válidas, rechazadas e importadas).
 */
class ImportRun extends Model
{
    /** @use HasFactory<\Database\Factories\ImportRunFactory> */
    use BelongsToCompany, HasFactory, LogsBusinessActivity;

    /** @var list<string> */
    protected $fillable = [
        'user_id',
        'type',
        'original_filename',
        'stored_path',
        'header_row',
        'column_map',
        'status',
        'total_rows',
        'valid_rows',
        'invalid_rows',
        'committed_rows',
        'error_report_path',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'column_map' => 'array',
            'status' => ImportStatus::class,
            'header_row' => 'integer',
            'total_rows' => 'integer',
            'valid_rows' => 'integer',
            'invalid_rows' => 'integer',
            'committed_rows' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
