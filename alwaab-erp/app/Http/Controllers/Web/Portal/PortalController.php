<?php

namespace App\Http\Controllers\Web\Portal;

use App\Domain\CRM\Models\Contact;
use App\Domain\Inventory\Models\Product;
use App\Domain\Quotations\Models\Quotation;
use App\Domain\Quotations\Models\Rfq;
use App\Enums\RfqStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Portal\PortalStoreRfqRequest;
use App\Services\QuotationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortalController extends Controller
{
    public function __construct(private QuotationService $quotationService) {}

    private function contactFor(Request $request): ?Contact
    {
        return Contact::where('email', $request->user()->email)->first();
    }

    public function dashboard(Request $request): Response
    {
        $contact = $this->contactFor($request);

        return Inertia::render('Portal/Dashboard', [
            'contact' => $contact,
            'stats' => [
                'rfqs' => $contact ? Rfq::where('contact_id', $contact->id)->count() : 0,
                'quotations' => $contact ? Quotation::where('contact_id', $contact->id)->count() : 0,
                'pending_rfqs' => $contact ? Rfq::where('contact_id', $contact->id)->where('status', RfqStatus::Pending)->count() : 0,
            ],
        ]);
    }

    public function catalog(Request $request): Response
    {
        $products = Product::where('is_active', true)
            ->with('category:id,name_ar,name_en')
            ->orderBy('source_sno')
            ->paginate(25);

        return Inertia::render('Portal/Catalog', [
            'products' => $products,
        ]);
    }

    public function rfqs(Request $request): Response
    {
        $contact = $this->contactFor($request);

        return Inertia::render('Portal/Rfqs', [
            'contact' => $contact,
            'rfqs' => $contact
                ? Rfq::where('contact_id', $contact->id)->latest()->paginate(15)
                : collect(),
        ]);
    }

    public function storeRfq(PortalStoreRfqRequest $request): RedirectResponse
    {
        $contact = $this->contactFor($request);

        if (! $contact) {
            return redirect()->back()->with('error', __('portal.no_contact'));
        }

        Rfq::create([
            'number' => $this->quotationService->generateNumber('RFQ'),
            'contact_id' => $contact->id,
            'project_id' => $request->validated('project_id'),
            'description' => $request->validated('description'),
            'status' => RfqStatus::Pending,
        ]);

        return redirect()->route('portal.rfqs')->with('success', __('messages.created'));
    }

    public function quotations(Request $request): Response
    {
        $contact = $this->contactFor($request);

        return Inertia::render('Portal/Quotations', [
            'quotations' => $contact
                ? Quotation::where('contact_id', $contact->id)
                    ->with('project:id,name_ar,name_en')
                    ->latest()
                    ->paginate(15)
                : collect(),
        ]);
    }
}
