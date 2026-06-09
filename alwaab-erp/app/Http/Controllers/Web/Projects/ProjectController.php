<?php

namespace App\Http\Controllers\Web\Projects;

use App\Domain\CRM\Models\Contact;
use App\Domain\Projects\Models\Project;
use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Models\User;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProjectController extends Controller
{
    public function __construct(private AccessScopeService $scope) {}

    public function index(Request $request): Response
    {
        $projects = $this->scope->scopeAssignedTo(
            Project::query()->with(['consultant:id,name_ar,name_en', 'contractor:id,name_ar,name_en', 'assignee:id,name_ar,name_en']),
            $request->user(),
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('name_ar', 'ilike', "%{$search}%")
                    ->orWhere('name_en', 'ilike', "%{$search}%")
                    ->orWhere('location', 'ilike', "%{$search}%");
            }))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('Projects/Index', [
            'projects' => $projects,
            'filters' => $request->only(['search', 'status']),
            'statuses' => collect(ProjectStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("crm.project_statuses.{$s->value}"),
            ]),
            'contacts' => Contact::whereIn('type', ['consultant', 'contractor', 'developer'])
                ->orderBy('name_ar')
                ->get(['id', 'name_ar', 'name_en', 'type']),
            'users' => User::where('is_active', true)->get(['id', 'name_ar', 'name_en']),
        ]);
    }

    public function map(): Response
    {
        $markers = Project::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get(['id', 'name_ar', 'name_en', 'lat', 'lng', 'status', 'value_aed', 'location'])
            ->map(fn (Project $p) => [
                'id' => $p->id,
                'lat' => (float) $p->lat,
                'lng' => (float) $p->lng,
                'name' => $p->name_ar,
                'status' => $p->status->value,
                'value' => (float) $p->value_aed,
                'location' => $p->location,
            ]);

        return Inertia::render('Projects/Map', ['markers' => $markers]);
    }

    public function show(Project $project): Response
    {
        $project->load([
            'consultant', 'contractor', 'assignee:id,name_ar,name_en',
            'fieldVisits' => fn ($q) => $q->with('employee:id,name_ar,name_en')->latest('visited_at')->limit(5),
        ]);

        return Inertia::render('Projects/Show', [
            'project' => $project,
        ]);
    }

    public function store(StoreProjectRequest $request): RedirectResponse
    {
        Project::create([
            ...$request->validated(),
            'assigned_to' => $request->validated('assigned_to') ?? $request->user()->id,
        ]);

        return redirect()->route('projects.index')->with('success', __('messages.created'));
    }
}
