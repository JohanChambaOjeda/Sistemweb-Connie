<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Bitácora de solo lectura: quién cambió qué y cuándo, dentro de la empresa
 * activa.
 */
class ActivityLogController extends Controller
{
    public function index(Request $request): Response
    {
        Gate::authorize('activity_log.view');

        $filters = [
            'user_id' => $request->integer('user_id') ?: null,
            'log_name' => $request->string('log_name')->toString() ?: null,
            'from' => $request->date('from')?->toDateString(),
            'to' => $request->date('to')?->toDateString(),
        ];

        $activities = ActivityLog::query()
            ->with('causer:id,name')
            ->when($filters['user_id'], fn ($query, $userId) => $query->where('causer_id', $userId))
            ->when($filters['log_name'], fn ($query, $logName) => $query->where('log_name', $logName))
            ->when($filters['from'], fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($filters['to'], fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return Inertia::render('activity/index', [
            'activities' => $activities,
            'filters' => $filters,
            'users' => User::query()
                ->whereHas('companies', fn ($query) => $query->whereKey($request->user()?->current_company_id))
                ->orderBy('name')
                ->get(['id', 'name']),
            'logNames' => ActivityLog::query()
                ->select('log_name')
                ->distinct()
                ->orderBy('log_name')
                ->pluck('log_name')
                ->filter()
                ->values(),
        ]);
    }
}
