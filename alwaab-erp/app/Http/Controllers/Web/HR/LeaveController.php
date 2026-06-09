<?php

namespace App\Http\Controllers\Web\HR;

use App\Domain\HR\Models\LeaveRequest;
use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Http\Controllers\Controller;
use App\Http\Requests\HR\StoreLeaveRequest;
use App\Models\User;
use App\Services\AppNotificationService;
use App\Services\HrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LeaveController extends Controller
{
    public function __construct(
        private HrService $hrService,
        private AppNotificationService $notifications,
    ) {}

    public function index(Request $request): Response
    {
        $leaves = LeaveRequest::query()
            ->with(['user:id,name_ar,name_en', 'approver:id,name_ar,name_en'])
            ->when(! $request->user()->can('hr.manage'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->status, fn ($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return Inertia::render('HR/Leaves/Index', [
            'leaves' => $leaves,
            'filters' => $request->only(['status']),
            'types' => collect(LeaveType::cases())->map(fn ($t) => [
                'value' => $t->value,
                'label' => __("hr.leave_types.{$t->value}"),
            ]),
            'statuses' => collect(LeaveStatus::cases())->map(fn ($s) => [
                'value' => $s->value,
                'label' => __("hr.leave_statuses.{$s->value}"),
            ]),
            'canManage' => $request->user()->can('hr.manage'),
        ]);
    }

    public function store(StoreLeaveRequest $request): RedirectResponse
    {
        $start = $request->date('start_date');
        $end = $request->date('end_date');
        $days = $start->diffInDays($end) + 1;

        $leave = LeaveRequest::create([
            'user_id' => $request->user()->id,
            'type' => $request->validated('type'),
            'start_date' => $start,
            'end_date' => $end,
            'days' => $days,
            'reason' => $request->validated('reason'),
            'status' => LeaveStatus::Pending,
        ]);

        $managers = User::permission('hr.manage')->where('is_active', true)->get();
        $this->notifications->notifyUsers($managers, [
            'category' => 'system',
            'sound' => 'default',
            'priority' => 'normal',
            'icon' => '🏖️',
            'title' => __('notifications.leave_request_title'),
            'body' => __('notifications.leave_request_body', [
                'name' => $request->user()->name,
                'dates' => $start->format('Y-m-d').' — '.$end->format('Y-m-d'),
            ]),
            'url' => '/hr/leaves',
        ]);

        return redirect()->back()->with('success', __('messages.created'));
    }

    public function approve(Request $request, LeaveRequest $leave): RedirectResponse
    {
        abort_unless($request->user()->can('hr.manage'), 403);

        $this->hrService->approveLeave($leave, $request->user());

        return redirect()->back()->with('success', __('hr.leave_approved'));
    }

    public function reject(Request $request, LeaveRequest $leave): RedirectResponse
    {
        abort_unless($request->user()->can('hr.manage'), 403);

        $this->hrService->rejectLeave($leave, $request->user(), $request->input('reason'));

        return redirect()->back()->with('success', __('hr.leave_rejected'));
    }
}
