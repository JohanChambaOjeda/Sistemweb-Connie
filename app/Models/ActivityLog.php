<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCompany;
use Spatie\Activitylog\Models\Activity as SpatieActivity;

/**
 * Bitácora del sistema. Hereda el modelo de spatie y le añade el filtro por
 * empresa, de modo que cada finca solo ve su propia auditoría.
 */
class ActivityLog extends SpatieActivity
{
    use BelongsToCompany;

    protected $table = 'activity_log';
}
