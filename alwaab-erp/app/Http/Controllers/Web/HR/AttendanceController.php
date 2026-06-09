<?php

namespace App\Http\Controllers\Web\HR;

use App\Domain\HR\Models\Attendance;
use App\Http\Controllers\Controller;
use App\Services\HrService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class AttendanceController extends Controller
{
    public function __construct(private HrService $hrService) {}

    public function index(Request $request): Response
    {
        $todayOpen = Attendance::where('user_id', $request->user()->id)
            ->whereNull('check_out_at')
            ->whereDate('check_in_at', today())
            ->exists();

        $records = Attendance::query()
            ->with('user:id,name_ar,name_en')
            ->when(! $request->user()->can('hr.manage'), fn ($q) => $q->where('user_id', $request->user()->id))
            ->when($request->date, fn ($q, $date) => $q->whereDate('check_in_at', $date))
            ->when($request->user_id && $request->user()->can('hr.manage'), fn ($q, $id) => $q->where('user_id', $id))
            ->latest('check_in_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('HR/Attendance/Index', [
            'records' => $records,
            'filters' => $request->only(['date', 'user_id']),
            'todayOpen' => $todayOpen,
            'canManage' => $request->user()->can('hr.manage'),
        ]);
    }

    public function checkIn(Request $request): RedirectResponse
    {
        try {
            $this->hrService->checkIn(
                $request->user(),
                $request->float('lat'),
                $request->float('lng'),
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('hr.checked_in'));
    }

    public function checkOut(Request $request): RedirectResponse
    {
        try {
            $this->hrService->checkOut(
                $request->user(),
                $request->float('lat'),
                $request->float('lng'),
            );
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }

        return redirect()->back()->with('success', __('hr.checked_out'));
    }
}
