<?php

namespace App\Http\Controllers\Web\Invoices;

use App\Support\DatabaseHelper;

use App\Domain\CRM\Models\Contact;
use App\Domain\Inventory\Models\Product;
use App\Domain\Invoices\Models\Invoice;
use App\Domain\Projects\Models\Project;
use App\Enums\InvoiceStatus;
use App\Enums\InvoiceType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Invoices\StoreInvoiceRequest;
use App\Services\AccessScopeService;
use App\Services\InvoiceService;
use App\Services\PdfService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InvoiceController extends Controller
{
    public function __construct(
        private InvoiceService $invoiceService,
        private PdfService $pdfService,
        private AccessScopeService $scope,
    ) {}

    public function index(Request $request): InertiaResponse
    {
        $invoices = $this->scope->scopeCreatedBy(
            Invoice::query()->with([
                'contact:id,name_ar,name_en,company',
                'project:id,name_ar,name_en',
                'creator:id,name_ar,name_en',
            ]),
            $request->user(),
        )
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('number', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhere('client_name', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhereHas('contact', fn ($c) => $c->where('name_ar', DatabaseHelper::likeOperator(), "%{$search}%")
                        ->orWhere('name_en', DatabaseHelper::likeOperator(), "%{$search}%"));
            }))
            ->whereIn('status', [InvoiceStatus::Saved, InvoiceStatus::Issued, InvoiceStatus::Paid])
            ->latest('document_date')
            ->paginate(15)
            ->withQueryString();

        $defaultType = $request->query('type', InvoiceType::Quotation->value);
        $typeEnum = InvoiceType::tryFrom($defaultType) ?? InvoiceType::Quotation;

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => $request->only(['search', 'type']),
            'types' => collect(InvoiceType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label_ar' => $t->labelAr(),
                'label_en' => $t->labelEn(),
                'prefix' => $t->prefix(),
            ]),
            'statuses' => collect(InvoiceStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("invoices.statuses.{$s->value}"),
            ]),
            'contacts' => Contact::orderBy('name_ar')->get(['id', 'name_ar', 'name_en', 'company', 'email', 'phone']),
            'projects' => Project::with(['consultant:id,name_ar,name_en', 'contractor:id,name_ar,name_en'])
                ->orderBy('name_ar')
                ->get(['id', 'name_ar', 'name_en', 'consultant_id', 'contractor_id', 'location']),
            'products' => Product::where('is_active', true)->orderBy('sku')
                ->limit(200)
                ->get(['id', 'sku', 'name_ar', 'name_en', 'price_aed', 'unit']),
            'previewNumber' => $this->invoiceService->previewNumber($typeEnum),
            'defaultType' => $typeEnum->value,
            'today' => now()->format('Y-m-d'),
        ]);
    }

    public function show(Request $request, Invoice $invoice): InertiaResponse
    {
        abort_unless($this->scope->canAccessRecord($request->user(), $invoice->created_by, 'created_by'), 403);

        $invoice->load([
            'contact',
            'project',
            'creator:id,name_ar,name_en',
            'items.product:id,sku,name_ar,name_en,unit',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
            'types' => collect(InvoiceType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label_ar' => $t->labelAr(),
                'label_en' => $t->labelEn(),
            ]),
            'statuses' => collect(InvoiceStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("invoices.statuses.{$s->value}"),
            ]),
        ]);
    }

    public function store(StoreInvoiceRequest $request): RedirectResponse
    {
        $invoice = $this->invoiceService->createInvoice(
            $request->validated(),
            $request->user(),
        );

        return redirect()->route('invoices.show', $invoice)->with('success', __('messages.created'));
    }

    public function update(StoreInvoiceRequest $request, Invoice $invoice): RedirectResponse
    {
        $this->invoiceService->updateInvoice($invoice, $request->validated());

        return redirect()->route('invoices.show', $invoice)->with('success', __('messages.updated'));
    }

    public function pdf(Request $request, Invoice $invoice): HttpResponse
    {
        $pdf = $this->pdfService->invoicePdf($invoice);
        $filename = str_replace(['/', '\\'], '-', $invoice->number).'.pdf';

        if ($request->boolean('download')) {
            return $pdf->download($filename);
        }

        return $pdf->stream($filename);
    }

    public function excel(Invoice $invoice): StreamedResponse
    {
        $invoice->load('items');

        $filename = str_replace('/', '-', $invoice->number).'.csv';

        return response()->streamDownload(function () use ($invoice) {
            $out = fopen('php://output', 'w');
            fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($out, ['#', 'Description', 'Qty', 'Unit', 'Unit Price (AED)', 'Total (AED)']);
            foreach ($invoice->items as $i => $item) {
                fputcsv($out, [
                    $i + 1,
                    $item->description,
                    $item->quantity,
                    $item->unit,
                    $item->unit_price_aed,
                    $item->total_aed,
                ]);
            }

            fputcsv($out, []);
            fputcsv($out, ['Subtotal', $invoice->subtotal_aed]);
            fputcsv($out, ['Discount', $invoice->discount_aed]);
            fputcsv($out, ['VAT 5%', $invoice->vat_aed]);
            fputcsv($out, ['Total', $invoice->total_aed]);

            fclose($out);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function previewNumber(Request $request): \Illuminate\Http\JsonResponse
    {
        $type = InvoiceType::from($request->query('type', InvoiceType::Quotation->value));

        return response()->json([
            'number' => $this->invoiceService->previewNumber($type),
        ]);
    }

    public function send(Request $request, Invoice $invoice): RedirectResponse
    {
        abort_unless($request->user()?->can('invoices.create'), 403);

        if ($invoice->status !== InvoiceStatus::Saved) {
            return redirect()->back()->with('error', __('invoices.cannot_send'));
        }

        $this->invoiceService->sendToClient($invoice);

        return redirect()->back()->with('success', __('invoices.sent'));
    }
}
