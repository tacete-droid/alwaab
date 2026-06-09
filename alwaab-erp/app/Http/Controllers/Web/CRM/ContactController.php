<?php

namespace App\Http\Controllers\Web\CRM;

use App\Domain\CRM\Models\Contact;
use App\Enums\ContactStatus;
use App\Enums\ContactType;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreContactRequest;
use App\Http\Requests\CRM\UpdateContactRequest;
use App\Models\User;
use App\Services\AccessScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContactController extends Controller
{
    public function __construct(private AccessScopeService $scope) {}

    public function index(Request $request): Response
    {
        $contacts = $this->scope->scopeAssignedTo(
            Contact::query()->with('assignee:id,name_ar,name_en,email'),
            $request->user(),
        )
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('name_ar', 'ilike', "%{$search}%")
                    ->orWhere('name_en', 'ilike', "%{$search}%")
                    ->orWhere('company', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('CRM/Contacts/Index', [
            'contacts' => $contacts,
            'filters' => $request->only(['search', 'type', 'status']),
            'types' => collect(ContactType::cases())->map(fn ($t) => ['value' => $t->value, 'label' => __("crm.types.{$t->value}")]),
            'statuses' => collect(ContactStatus::cases())->map(fn ($s) => ['value' => $s->value, 'label' => __("crm.statuses.{$s->value}")]),
            'users' => User::where('is_active', true)->get(['id', 'name_ar', 'name_en']),
        ]);
    }

    public function show(Request $request, Contact $contact): Response
    {
        abort_unless($this->scope->canAccessRecord($request->user(), $contact->assigned_to), 403);

        $contact->load(['assignee', 'leads', 'activities.user']);

        return Inertia::render('CRM/Contacts/Show', [
            'contact' => $contact,
        ]);
    }

    public function store(StoreContactRequest $request): RedirectResponse
    {
        Contact::create([
            ...$request->validated(),
            'assigned_to' => $request->validated('assigned_to') ?? $request->user()->id,
        ]);

        return redirect()->route('contacts.index')->with('success', __('messages.created'));
    }

    public function update(UpdateContactRequest $request, Contact $contact): RedirectResponse
    {
        $contact->update($request->validated());

        return redirect()->back()->with('success', __('messages.updated'));
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return redirect()->route('contacts.index')->with('success', __('messages.deleted'));
    }
}
