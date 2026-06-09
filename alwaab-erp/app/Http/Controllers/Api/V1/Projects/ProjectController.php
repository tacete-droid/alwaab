<?php

namespace App\Http\Controllers\Api\V1\Projects;

use App\Domain\Projects\Models\Project;
use App\Enums\ProjectStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Projects\StoreProjectRequest;
use App\Http\Requests\Projects\UpdateProjectStatusRequest;
use App\Http\Resources\ProjectResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = Project::query()
            ->with(['consultant', 'contractor', 'assignee'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::success(ProjectResource::collection($projects));
    }

    public function store(StoreProjectRequest $request): JsonResponse
    {
        $project = Project::create($request->validated());

        return ApiResponse::success(
            new ProjectResource($project->load(['consultant', 'contractor'])),
            __('messages.created'),
            201
        );
    }

    public function show(Project $project): JsonResponse
    {
        $project->load(['consultant', 'contractor', 'assignee', 'fieldVisits.employee']);

        return ApiResponse::success(new ProjectResource($project));
    }

    public function map(): JsonResponse
    {
        $features = Project::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->get()
            ->map(fn (Project $p) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $p->lng, (float) $p->lat],
                ],
                'properties' => [
                    'id' => $p->id,
                    'name' => $p->name,
                    'status' => $p->status->value,
                    'value_aed' => $p->value_aed,
                ],
            ]);

        return ApiResponse::success([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }

    public function updateStatus(UpdateProjectStatusRequest $request, Project $project): JsonResponse
    {
        $project->update(['status' => ProjectStatus::from($request->validated('status'))]);

        return ApiResponse::success(new ProjectResource($project->fresh()), __('messages.updated'));
    }
}
