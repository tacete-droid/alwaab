<?php

namespace App\Http\Controllers\Web\Quotations;

use App\Support\DatabaseHelper;

use App\Domain\CRM\Models\Contact;
use App\Domain\Projects\Models\Project;
use App\Domain\Quotations\Models\Rfq;
use App\Enums\RfqStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotations\StoreRfqRequest;
use App\Http\Requests\Quotations\UploadBoqRequest;
use App\Models\User;
use App\Services\AccessScopeService;
use App\Services\BoqParserService;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RfqController extends Controller
{
    public function __construct(
        private QuotationService $quotationService,
        private BoqParserService $boqParser,
        private AccessScopeService $scope,
    ) {}

    public function index(Request $request): Response
    {
        $rfqs = $this->scope->scopeAssignedTo(
            Rfq::query()->with([
                'contact:id,name_ar,name_en,company',
                'project:id,name_ar,name_en',
                'assignee:id,name_ar,name_en',
            ]),
            $request->user(),
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('number', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhereHas('contact', fn ($c) => $c->where('name_ar', DatabaseHelper::likeOperator(), "%{$search}%")
                        ->orWhere('name_en', DatabaseHelper::likeOperator(), "%{$search}%"));
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('RFQ/Index', [
            'rfqs' => $rfqs,
            'filters' => $request->only(['search', 'status']),
            'statuses' => collect(RfqStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("quotations.rfq_statuses.{$s->value}"),
            ]),
            'contacts' => Contact::orderBy('name_ar')->get(['id', 'name_ar', 'name_en', 'company']),
            'projects' => Project::orderBy('name_ar')->get(['id', 'name_ar', 'name_en']),
            'users' => User::where('is_active', true)->get(['id', 'name_ar', 'name_en']),
        ]);
    }

    public function show(Request $request, Rfq $rfq): Response
    {
        abort_unless($this->scope->canAccessRecord($request->user(), $rfq->assigned_to), 403);

        $rfq->load([
            'contact',
            'project',
            'assignee:id,name_ar,name_en',
            'boqItems.product:id,sku,name_en,name_ar,price_aed,unit',
            'quotations:id,number,rfq_id,status,total_aed,created_at',
        ]);

        return Inertia::render('RFQ/Show', [
            'rfq' => $rfq,
            'statuses' => collect(RfqStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("quotations.rfq_statuses.{$s->value}"),
            ]),
        ]);
    }

    public function store(StoreRfqRequest $request): RedirectResponse
    {
        $rfq = Rfq::create([
            'number' => $this->quotationService->generateNumber('RFQ'),
            ...$request->validated(),
            'assigned_to' => $request->validated('assigned_to') ?? $request->user()->id,
            'status' => RfqStatus::Pending,
        ]);

        return redirect()->route('rfqs.show', $rfq)->with('success', __('messages.created'));
    }

    public function uploadBoq(UploadBoqRequest $request, Rfq $rfq): RedirectResponse
    {
        try {
            $result = $this->boqParser->parseAndStore($rfq, $request->file('boq_file'));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        $message = __('quotations.boq_parsed', [
            'count' => $result['items_count'],
            'matched' => $result['matched_count'],
        ]);

        if (! empty($result['warnings'])) {
            return redirect()->back()
                ->with('success', $message)
                ->with('warning', implode(' · ', $result['warnings']));
        }

        return redirect()->back()->with('success', $message);
    }

    public function createQuotation(Request $request, Rfq $rfq): RedirectResponse
    {
        abort_unless($request->user()?->can('quotations.create'), 403);

        $quotation = $this->quotationService->createQuotationFromRfq($rfq, $request->user());

        return redirect()->route('quotations.show', $quotation)->with('success', __('quotations.quotation_from_boq'));
    }
}
