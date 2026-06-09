<?php

namespace App\Http\Controllers\Web\HR;

use App\Domain\HR\Models\EmployeeProfile;
use App\Enums\HrDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreEmployeeDocumentRequest;
use App\Services\HrDocumentService;
use App\Services\HrScopeService;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class EmployeeDocumentController extends Controller
{
    public function __construct(
        private HrScopeService $scope,
        private HrDocumentService $documents,
    ) {}

    public function store(StoreEmployeeDocumentRequest $request, EmployeeProfile $employee): RedirectResponse
    {
        abort_unless($this->scope->canUploadDocuments($request->user(), $employee), 403);

        $this->documents->upload(
            $employee,
            $request->file('file'),
            HrDocumentType::from($request->validated('document_type')),
            $request->validated('title'),
            $request->validated('expires_at'),
        );

        return redirect()
            ->route('hr.employees.show', $employee)
            ->with('success', __('hr.document_uploaded'));
    }

    public function destroy(EmployeeProfile $employee, string $document): RedirectResponse
    {
        abort_unless($this->scope->canUploadDocuments(request()->user(), $employee), 403);

        $this->documents->delete($employee, $document);

        return redirect()
            ->route('hr.employees.show', $employee)
            ->with('success', __('hr.document_deleted'));
    }
}
