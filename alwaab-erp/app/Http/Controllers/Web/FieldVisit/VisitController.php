<?php

namespace App\Http\Controllers\Web\FieldVisit;

use App\Domain\FieldVisit\Models\FieldVisit;
use App\Domain\FieldVisit\Services\FieldVisitService;
use App\Domain\Projects\Models\Project;
use App\Enums\FieldVisitStatus;
use App\Http\Controllers\Controller;
use App\Services\AccessScopeService;
use App\Http\Requests\FieldVisit\CompleteVisitRequest;
use App\Http\Requests\FieldVisit\StartVisitRequest;
use App\Http\Requests\FieldVisit\UpdateVisitLocationRequest;
use App\Http\Requests\FieldVisit\UploadPhotoRequest;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VisitController extends Controller
{
    public function __construct(
        private FieldVisitService $fieldVisitService,
        private AccessScopeService $scope,
    ) {}

    public function index(Request $request): Response
    {
        $visitsQuery = FieldVisit::query()->with([
                'project:id,name_ar,name_en,location',
                'employee:id,name_ar,name_en',
                'photos',
            ])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->project_id, fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->employee_id, fn ($q, $id) => $q->where('employee_id', $id));

        if (! $this->scope->isAdmin($request->user()) && ! $request->user()->can('visits.manage')) {
            $visitsQuery->where('employee_id', $request->user()->id);
        }

        $visits = $visitsQuery->latest('visited_at')->paginate(20)->withQueryString();

        return Inertia::render('FieldVisits/Index', [
            'visits' => $visits,
            'filters' => $request->only(['status', 'project_id', 'employee_id']),
            'statuses' => collect(FieldVisitStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("quotations.visit_statuses.{$s->value}"),
            ]),
            'projects' => Project::orderBy('name_ar')->get(['id', 'name_ar', 'name_en']),
            'employees' => User::where('is_active', true)->get(['id', 'name_ar', 'name_en']),
            'canManage' => $request->user()->can('visits.manage'),
        ]);
    }

    public function create(): Response|RedirectResponse
    {
        $activeVisit = FieldVisit::query()
            ->where('employee_id', auth()->id())
            ->where('status', FieldVisitStatus::InProgress)
            ->with('project:id,name_ar,name_en,location')
            ->first();

        if ($activeVisit) {
            return redirect()->route('field-visits.show', $activeVisit);
        }

        return Inertia::render('FieldVisits/Create', [
            'projects' => Project::orderBy('name_ar')->get(['id', 'name_ar', 'name_en', 'location']),
        ]);
    }

    public function store(StartVisitRequest $request): RedirectResponse
    {
        $visit = $this->fieldVisitService->startVisit($request->validated(), $request->user());

        return redirect()->route('field-visits.show', $visit)
            ->with('success', __('messages.created'));
    }

    public function show(Request $request, FieldVisit $visit): Response
    {
        $visit->load(['project', 'employee', 'photos']);

        return Inertia::render('FieldVisits/Show', [
            'visit' => $visit,
            'canManage' => $request->user()->can('visits.manage'),
            'isOwner' => $visit->employee_id === $request->user()->id,
        ]);
    }

    public function liveGps(Request $request): JsonResponse
    {
        abort_unless($request->user()->can('visits.manage'), 403);

        $visits = FieldVisit::query()
            ->where('status', FieldVisitStatus::InProgress)
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with(['project:id,name_ar,name_en', 'employee:id,name_ar,name_en'])
            ->latest('updated_at')
            ->get()
            ->map(fn (FieldVisit $v) => [
                'id' => $v->id,
                'lat' => (float) $v->lat,
                'lng' => (float) $v->lng,
                'status' => $v->status->value,
                'project' => $v->project?->name_ar,
                'project_en' => $v->project?->name_en,
                'employee' => $v->employee?->name_ar,
                'employee_en' => $v->employee?->name_en,
                'visited_at' => $v->visited_at?->toIso8601String(),
                'updated_at' => $v->updated_at?->toIso8601String(),
            ]);

        return response()->json(['visits' => $visits]);
    }

    public function updateLocation(UpdateVisitLocationRequest $request, FieldVisit $visit): JsonResponse
    {
        $visit = $this->fieldVisitService->updateLocation(
            $visit,
            $request->validated('lat'),
            $request->validated('lng'),
            $request->user(),
        );

        return response()->json([
            'lat' => (float) $visit->lat,
            'lng' => (float) $visit->lng,
            'updated_at' => $visit->updated_at?->toIso8601String(),
        ]);
    }

    public function uploadPhoto(UploadPhotoRequest $request, FieldVisit $visit): RedirectResponse
    {
        $this->fieldVisitService->uploadPhoto(
            $visit,
            $request->file('photo'),
            $request->only(['lat', 'lng']),
            $request->user()
        );

        return redirect()->back()->with('success', __('messages.created'));
    }

    public function complete(CompleteVisitRequest $request, FieldVisit $visit): RedirectResponse
    {
        $this->fieldVisitService->completeVisit($visit, $request->validated('notes'));

        return redirect()->route('field-visits.index')
            ->with('success', __('messages.updated'));
    }

    public function map(Request $request): Response
    {
        $markers = FieldVisit::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with('project:id,name_ar,name_en', 'employee:id,name_ar,name_en')
            ->latest('visited_at')
            ->limit(200)
            ->get()
            ->map(fn (FieldVisit $v) => [
                'id' => $v->id,
                'lat' => (float) $v->lat,
                'lng' => (float) $v->lng,
                'status' => $v->status->value,
                'project' => $v->project?->name_ar,
                'employee' => $v->employee?->name_ar,
                'visited_at' => $v->visited_at?->toIso8601String(),
            ]);

        return Inertia::render('FieldVisits/Map', [
            'markers' => $markers,
            'canManage' => $request->user()->can('visits.manage'),
        ]);
    }
}
