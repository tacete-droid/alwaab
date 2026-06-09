<?php

namespace App\Services;

use App\Domain\HR\Models\Attendance;
use App\Domain\HR\Models\LeaveRequest;
use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Models\User;
use InvalidArgumentException;

class HrService
{
    public function checkIn(User $user, ?float $lat = null, ?float $lng = null): Attendance
    {
        $open = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_at')
            ->whereDate('check_in_at', today())
            ->exists();

        if ($open) {
            throw new InvalidArgumentException(__('hr.already_checked_in'));
        }

        $status = now()->hour >= 9 ? AttendanceStatus::Late : AttendanceStatus::Present;

        return Attendance::create([
            'user_id' => $user->id,
            'check_in_at' => now(),
            'check_in_lat' => $lat,
            'check_in_lng' => $lng,
            'status' => $status,
        ]);
    }

    public function checkOut(User $user, ?float $lat = null, ?float $lng = null): Attendance
    {
        $attendance = Attendance::where('user_id', $user->id)
            ->whereNull('check_out_at')
            ->whereDate('check_in_at', today())
            ->latest('check_in_at')
            ->first();

        if (! $attendance) {
            throw new InvalidArgumentException(__('hr.no_open_checkin'));
        }

        $attendance->update([
            'check_out_at' => now(),
            'check_out_lat' => $lat,
            'check_out_lng' => $lng,
        ]);

        return $attendance;
    }

    public function approveLeave(LeaveRequest $leave, User $approver): LeaveRequest
    {
        $leave->update([
            'status' => LeaveStatus::Approved,
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $leave->load('user');
        app(AppNotificationService::class)->notifyUser($leave->user, [
            'category' => 'system',
            'sound' => 'default',
            'priority' => 'normal',
            'icon' => '✅',
            'title' => __('notifications.leave_approved_title'),
            'body' => __('notifications.leave_approved_body', ['dates' => $leave->start_date->format('Y-m-d').' — '.$leave->end_date->format('Y-m-d')]),
            'url' => '/hr/leaves',
        ]);

        return $leave;
    }

    public function rejectLeave(LeaveRequest $leave, User $approver, ?string $reason = null): LeaveRequest
    {
        $leave->update([
            'status' => LeaveStatus::Rejected,
            'approved_by' => $approver->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);

        $leave->load('user');
        app(AppNotificationService::class)->notifyUser($leave->user, [
            'category' => 'system',
            'sound' => 'alert',
            'priority' => 'urgent',
            'icon' => '❌',
            'title' => __('notifications.leave_rejected_title'),
            'body' => $reason ?: __('notifications.leave_rejected_body'),
            'url' => '/hr/leaves',
        ]);

        return $leave;
    }
}
