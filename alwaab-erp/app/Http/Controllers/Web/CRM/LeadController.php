<?php

namespace App\Http\Controllers\Web\CRM;

use App\Domain\CRM\Models\Contact;
use App\Domain\CRM\Models\Lead;
use App\Domain\CRM\Services\LeadService;
use App\Enums\LeadSource;
use App\Enums\LeadStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreLeadRequest;
use App\Http\Requests\CRM\UpdateLeadStageRequest;
use App\Models\User;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeadController extends Controller
{
    public function __construct(
        private LeadService $leadService,
        private AccessScopeService $scope,
    ) {}

    public function index(Request $request): Response
    {
        $leads = $this->scope->scopeAssignedTo(
            Lead::with(['contact:id,name_ar,name_en,company', 'assignee:id,name_ar,name_en']),
            $request->user(),
        )->latest()->get();

        $pipeline = collect(LeadStatus::cases())->mapWithKeys(function (LeadStatus $status) use ($leads) {
            return [$status->value => $leads->where('status', $status)->values()];
        });

        return Inertia::render('CRM/Leads/Index', [
            'pipeline' => $pipeline,
            'stages' => collect(LeadStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("crm.lead_stages.{$s->value}"),
            ]),
            'contacts' => Contact::orderBy('name_ar')->get(['id', 'name_ar', 'name_en', 'company']),
            'users' => User::where('is_active', true)->get(['id', 'name_ar', 'name_en']),
            'sources' => collect(LeadSource::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("crm.sources.{$s->value}"),
            ]),
        ]);
    }

    public function store(StoreLeadRequest $request): RedirectResponse
    {
        Lead::create([
            ...$request->validated(),
            'assigned_to' => $request->validated('assigned_to') ?? $request->user()->id,
        ]);

        return redirect()->route('leads.index')->with('success', __('messages.created'));
    }

    public function updateStage(UpdateLeadStageRequest $request, Lead $lead): RedirectResponse
    {
        $this->leadService->updateStage($lead, LeadStatus::from($request->validated('status')));

        return redirect()->back()->with('success', __('messages.updated'));
    }
}
