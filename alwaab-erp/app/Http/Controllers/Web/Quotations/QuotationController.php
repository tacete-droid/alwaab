<?php

namespace App\Http\Controllers\Web\Quotations;

use App\Domain\CRM\Models\Contact;
use App\Domain\Inventory\Models\Product;
use App\Domain\Projects\Models\Project;
use App\Domain\Quotations\Models\Quotation;
use App\Domain\Quotations\Models\Rfq;
use App\Enums\QuotationStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Quotations\StoreQuotationRequest;
use App\Services\AccessScopeService;
use App\Services\PdfService;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Inertia\Inertia;
use Inertia\Response;

class QuotationController extends Controller
{
    public function __construct(
        private QuotationService $quotationService,
        private PdfService $pdfService,
        private AccessScopeService $scope,
    ) {}

    public function index(Request $request): Response
    {
        $quotations = $this->scope->scopeCreatedBy(
            Quotation::query()->with([
                'contact:id,name_ar,name_en,company',
                'project:id,name_ar,name_en',
                'creator:id,name_ar,name_en',
            ]),
            $request->user(),
        )
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('number', 'ilike', "%{$search}%")
                    ->orWhereHas('contact', fn ($c) => $c->where('name_ar', 'ilike', "%{$search}%")
                        ->orWhere('name_en', 'ilike', "%{$search}%"));
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
            'filters' => $request->only(['search', 'status']),
            'statuses' => collect(QuotationStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("quotations.statuses.{$s->value}"),
            ]),
            'contacts' => Contact::orderBy('name_ar')->get(['id', 'name_ar', 'name_en', 'company']),
            'projects' => Project::orderBy('name_ar')->get(['id', 'name_ar', 'name_en']),
            'rfqs' => Rfq::whereIn('status', ['pending', 'processing'])->orderByDesc('created_at')
                ->get(['id', 'number', 'contact_id']),
            'products' => Product::where('is_active', true)->orderBy('sku')
                ->limit(50)
                ->get(['id', 'sku', 'name_ar', 'name_en', 'price_aed', 'unit']),
        ]);
    }

    public function show(Request $request, Quotation $quotation): Response
    {
        abort_unless($this->scope->canAccessRecord($request->user(), $quotation->created_by, 'created_by'), 403);

        $quotation->load([
            'contact',
            'project',
            'rfq',
            'creator:id,name_ar,name_en',
            'approver:id,name_ar,name_en',
            'items.product:id,sku,name_ar,name_en,unit',
        ]);

        return Inertia::render('Quotations/Show', [
            'quotation' => $quotation,
            'statuses' => collect(QuotationStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("quotations.statuses.{$s->value}"),
            ]),
        ]);
    }

    public function store(StoreQuotationRequest $request): RedirectResponse
    {
        $quotation = $this->quotationService->createQuotation(
            $request->validated(),
            $request->user(),
        );

        return redirect()->route('quotations.show', $quotation)->with('success', __('messages.created'));
    }

    public function approve(Request $request, Quotation $quotation): RedirectResponse
    {
        abort_unless($request->user()?->can('quotations.approve'), 403);

        if ($quotation->status !== QuotationStatus::Draft) {
            return redirect()->back()->with('error', __('quotations.cannot_approve'));
        }

        $this->quotationService->approve($quotation, $request->user());

        return redirect()->back()->with('success', __('quotations.approved'));
    }

    public function send(Request $request, Quotation $quotation): RedirectResponse
    {
        abort_unless($request->user()?->can('quotations.create'), 403);

        if ($quotation->status !== QuotationStatus::Approved) {
            return redirect()->back()->with('error', __('quotations.cannot_send'));
        }

        $this->quotationService->send($quotation);

        return redirect()->back()->with('success', __('quotations.sent'));
    }

    public function pdf(Request $request, Quotation $quotation): HttpResponse
    {
        $pdf = $this->pdfService->quotationPdf($quotation);
        $filename = str_replace(['/', '\\'], '-', $quotation->number).'.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }
}
