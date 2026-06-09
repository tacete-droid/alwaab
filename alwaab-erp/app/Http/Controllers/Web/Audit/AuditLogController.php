<?php

namespace App\Http\Controllers\Web\Audit;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Activitylog\Models\Activity;

class AuditLogController extends Controller
{
    public function index(Request $request): Response
    {
        $logs = Activity::query()
            ->with('causer:id,name_ar,name_en,email')
            ->when($request->search, fn ($q, $search) => $q->where('description', 'ilike', "%{$search}%"))
            ->when($request->log_name, fn ($q, $name) => $q->where('log_name', $name))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return Inertia::render('Audit/Index', [
            'logs' => $logs,
            'filters' => $request->only(['search', 'log_name']),
        ]);
    }
}
