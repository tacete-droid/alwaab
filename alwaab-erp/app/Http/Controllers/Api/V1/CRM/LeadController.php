<?php

namespace App\Http\Controllers\Api\V1\CRM;

use App\Domain\CRM\Models\Lead;
use App\Domain\CRM\Services\LeadService;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreLeadRequest;
use App\Http\Requests\CRM\UpdateLeadStageRequest;
use App\Http\Resources\LeadResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeadController extends Controller
{
    public function __construct(private LeadService $leadService) {}

    public function index(Request $request): JsonResponse
    {
        $leads = Lead::query()
            ->with(['contact', 'assignee'])
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::success(LeadResource::collection($leads));
    }

    public function store(StoreLeadRequest $request): JsonResponse
    {
        $lead = Lead::create($request->validated());

        return ApiResponse::success(
            new LeadResource($lead->load(['contact', 'assignee'])),
            __('messages.created'),
            201
        );
    }

    public function show(Lead $lead): JsonResponse
    {
        $lead->load(['contact', 'assignee', 'activities.user']);

        return ApiResponse::success(new LeadResource($lead));
    }

    public function updateStage(UpdateLeadStageRequest $request, Lead $lead): JsonResponse
    {
        $lead = $this->leadService->updateStage(
            $lead,
            LeadStatus::from($request->validated('status'))
        );

        return ApiResponse::success(new LeadResource($lead), __('messages.updated'));
    }
}
