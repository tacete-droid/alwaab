<?php

namespace App\Http\Controllers\Api\V1\CRM;

use App\Support\DatabaseHelper;

use App\Domain\CRM\Models\Contact;
use App\Http\Controllers\Controller;
use App\Http\Requests\CRM\StoreContactRequest;
use App\Http\Requests\CRM\UpdateContactRequest;
use App\Http\Resources\ContactResource;
use App\Http\Responses\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ContactController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $contacts = Contact::query()
            ->with('assignee')
            ->when($request->type, fn ($q, $type) => $q->where('type', $type))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('name_ar', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhere('name_en', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhere('company', DatabaseHelper::likeOperator(), "%{$search}%");
            }))
            ->latest()
            ->paginate($request->integer('per_page', 25));

        return ApiResponse::success(ContactResource::collection($contacts));
    }

    public function store(StoreContactRequest $request): JsonResponse
    {
        $contact = Contact::create($request->validated());

        return ApiResponse::success(
            new ContactResource($contact->load('assignee')),
            __('messages.created'),
            201
        );
    }

    public function show(Contact $contact): JsonResponse
    {
        $contact->load(['assignee', 'leads', 'activities.user']);

        return ApiResponse::success(new ContactResource($contact));
    }

    public function update(UpdateContactRequest $request, Contact $contact): JsonResponse
    {
        $contact->update($request->validated());

        return ApiResponse::success(
            new ContactResource($contact->fresh('assignee')),
            __('messages.updated')
        );
    }

    public function destroy(Contact $contact): JsonResponse
    {
        $contact->delete();

        return ApiResponse::success(message: __('messages.deleted'));
    }
}
