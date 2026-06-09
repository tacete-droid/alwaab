<?php

namespace App\Http\Controllers\Web\HR;

use App\Support\DatabaseHelper;

use App\Domain\HR\Models\EmployeeProfile;
use App\Enums\HrDocumentType;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\UpdateEmployeeRequest;
use App\Services\HrDocumentService;
use App\Services\HrScopeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EmployeeController extends Controller
{
    public function __construct(
        private HrScopeService $scope,
        private HrDocumentService $documents,
    ) {}

    public function index(Request $request): Response
    {
        $employees = EmployeeProfile::query()
            ->with('user:id,name_ar,name_en,email,phone,is_active')
            ->when(true, fn ($q) => $this->scope->scopeEmployees($q, $request->user()))
            ->when($request->department, fn ($q, $dept) => $q->where('department', $dept))
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('employee_code', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhere('emirates_id', DatabaseHelper::likeOperator(), "%{$search}%")
                    ->orWhereHas('user', fn ($u) => $u->where('name_ar', DatabaseHelper::likeOperator(), "%{$search}%")
                        ->orWhere('name_en', DatabaseHelper::likeOperator(), "%{$search}%"));
            }))
            ->orderBy('employee_code')
            ->paginate(20)
            ->withQueryString();

        $departments = EmployeeProfile::query()
            ->when(! $this->scope->canManageAll($request->user()), fn ($q) => $q->where('user_id', $request->user()->id))
            ->distinct()
            ->pluck('department')
            ->filter()
            ->values();

        return Inertia::render('HR/Employees/Index', [
            'employees' => $employees,
            'filters' => $request->only(['search', 'department']),
            'departments' => $departments,
            'canManage' => $this->scope->canManageAll($request->user()),
        ]);
    }

    public function show(Request $request, EmployeeProfile $employee): Response
    {
        abort_unless($this->scope->canViewEmployee($request->user(), $employee), 403);

        $employee->load('user:id,name_ar,name_en,email,phone,is_active');

        return Inertia::render('HR/Employees/Show', [
            'employee' => $employee,
            'documents' => $this->documents->list($employee),
            'documentTypes' => collect(HrDocumentType::cases())->map(fn ($type) => [
                'value' => $type->value,
                'label' => __('hr.document_types.'.$type->value),
            ])->values(),
            'canManage' => $this->scope->canUpdateEmployee($request->user(), $employee),
            'canUpload' => $this->scope->canUploadDocuments($request->user(), $employee),
        ]);
    }

    public function update(UpdateEmployeeRequest $request, EmployeeProfile $employee): RedirectResponse
    {
        if (! $this->scope->canUpdateEmployee($request->user(), $employee)) {
            throw new AccessDeniedHttpException;
        }

        $employee->update($request->validated());

        return redirect()
            ->route('hr.employees.show', $employee)
            ->with('success', __('messages.updated'));
    }
}
