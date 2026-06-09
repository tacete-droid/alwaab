<?php

namespace App\Http\Controllers\Api\V1\FieldVisit;

use App\Domain\FieldVisit\Models\FieldVisit;
use App\Domain\FieldVisit\Services\FieldVisitService;
use App\Http\Controllers\Controller;
use App\Enums\FieldVisitStatus;
use App\Http\Requests\FieldVisit\CompleteVisitRequest;
use App\Http\Requests\FieldVisit\StartVisitRequest;
use App\Http\Requests\FieldVisit\UpdateVisitLocationRequest;
use App\Http\Requests\FieldVisit\UploadPhotoRequest;
use App\Http\Resources\FieldVisitResource;
use App\Http\Resources\VisitPhotoResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VisitController extends Controller
{
    public function __construct(private FieldVisitService $fieldVisitService) {}

    public function store(StartVisitRequest $request): JsonResponse
    {
        $visit = $this->fieldVisitService->startVisit(
            $request->validated(),
            $request->user()
        );

        return ApiResponse::success(
            new FieldVisitResource($visit->load(['project', 'employee'])),
            __('messages.created'),
            201
        );
    }

    public function uploadPhoto(UploadPhotoRequest $request, FieldVisit $visit): JsonResponse
    {
        $photo = $this->fieldVisitService->uploadPhoto(
            $visit,
            $request->file('photo'),
            $request->only(['lat', 'lng']),
            $request->user()
        );

        return ApiResponse::success(
            new VisitPhotoResource($photo),
            __('messages.created'),
            201
        );
    }

    public function complete(CompleteVisitRequest $request, FieldVisit $visit): JsonResponse
    {
        $visit = $this->fieldVisitService->completeVisit($visit, $request->validated('notes'));

        return ApiResponse::success(new FieldVisitResource($visit), __('messages.updated'));
    }

    public function show(FieldVisit $visit): JsonResponse
    {
        $visit->load(['project', 'employee', 'photos']);

        return ApiResponse::success(new FieldVisitResource($visit));
    }

    public function active(Request $request): JsonResponse
    {
        $visit = FieldVisit::query()
            ->where('employee_id', $request->user()->id)
            ->where('status', FieldVisitStatus::InProgress)
            ->with(['project', 'employee', 'photos'])
            ->first();

        return ApiResponse::success($visit ? new FieldVisitResource($visit) : null);
    }

    public function updateLocation(UpdateVisitLocationRequest $request, FieldVisit $visit): JsonResponse
    {
        $visit = $this->fieldVisitService->updateLocation(
            $visit,
            $request->validated('lat'),
            $request->validated('lng'),
            $request->user(),
        );

        return ApiResponse::success([
            'lat' => (float) $visit->lat,
            'lng' => (float) $visit->lng,
            'updated_at' => $visit->updated_at?->toIso8601String(),
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
                'employee' => $v->employee?->name_ar,
                'visited_at' => $v->visited_at?->toIso8601String(),
                'updated_at' => $v->updated_at?->toIso8601String(),
            ]);

        return ApiResponse::success(['visits' => $visits]);
    }

    public function history(Request $request): JsonResponse
    {
        $visits = FieldVisit::query()
            ->with(['project', 'employee', 'photos'])
            ->when($request->project_id, fn ($q, $id) => $q->where('project_id', $id))
            ->when($request->employee_id, fn ($q, $id) => $q->where('employee_id', $id))
            ->latest('visited_at')
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::success(FieldVisitResource::collection($visits));
    }

    public function map(): JsonResponse
    {
        $features = FieldVisit::query()
            ->whereNotNull('lat')
            ->whereNotNull('lng')
            ->with('project')
            ->latest('visited_at')
            ->limit(500)
            ->get()
            ->map(fn (FieldVisit $v) => [
                'type' => 'Feature',
                'geometry' => [
                    'type' => 'Point',
                    'coordinates' => [(float) $v->lng, (float) $v->lat],
                ],
                'properties' => [
                    'id' => $v->id,
                    'project' => $v->project?->name,
                    'status' => $v->status->value,
                    'visited_at' => $v->visited_at?->toIso8601String(),
                ],
            ]);

        return ApiResponse::success([
            'type' => 'FeatureCollection',
            'features' => $features,
        ]);
    }
}
