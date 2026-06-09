<?php

namespace App\Services;

use App\Domain\HR\Models\EmployeeProfile;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class HrDocumentExpiryService
{
    private const WARNING_DAYS = 30;

    public function __construct(
        private AppNotificationService $notifications,
        private HrDocumentService $documents,
    ) {}

    public function checkAndNotify(): int
    {
        $expiring = $this->collectExpiringItems();

        if ($expiring->isEmpty()) {
            return 0;
        }

        $managers = User::permission('hr.manage')->where('is_active', true)->get();

        if ($managers->isEmpty()) {
            return 0;
        }

        $sent = 0;

        foreach ($expiring as $item) {
            $this->notifications->notifyUsers($managers, [
                'category' => 'hr',
                'sound' => 'alert',
                'priority' => 'urgent',
                'icon' => '⚠️',
                'title' => __('hr.expiry_alert_title'),
                'body' => __('hr.expiry_alert_body', [
                    'employee' => $item['employee_name'],
                    'document' => $item['label'],
                    'date' => $item['expires_at'],
                    'days' => $item['days_left'],
                ]),
                'url' => '/hr/employees/'.$item['employee_id'],
            ]);
            $sent++;
        }

        return $sent;
    }

    public function collectExpiringItems(): Collection
    {
        $threshold = now()->addDays(self::WARNING_DAYS)->endOfDay();
        $items = collect();

        EmployeeProfile::query()
            ->with('user:id,name_ar,name_en')
            ->get()
            ->each(function (EmployeeProfile $employee) use ($threshold, $items) {
                $name = $employee->user?->name_en ?: $employee->user?->name_ar ?: $employee->employee_code;

                foreach ($this->profileExpiryFields() as $field => $labelKey) {
                    $date = $employee->{$field};

                    if (! $date || ! $this->isExpiringSoon($date, $threshold)) {
                        continue;
                    }

                    $items->push($this->buildItem($employee->id, $name, __($labelKey), $date));
                }

                foreach ($employee->getMedia(HrDocumentService::COLLECTION) as $media) {
                    $expiresAt = $media->getCustomProperty('expires_at');

                    if (! $expiresAt || ! $this->isExpiringSoon($expiresAt, $threshold)) {
                        continue;
                    }

                    $title = $media->getCustomProperty('title', $media->file_name);
                    $type = $media->getCustomProperty('document_type', 'other');

                    $items->push($this->buildItem(
                        $employee->id,
                        $name,
                        $title.' ('.__('hr.document_types.'.$type).')',
                        $expiresAt,
                    ));
                }
            });

        return $items;
    }

    private function profileExpiryFields(): array
    {
        return [
            'emirates_id_expiry' => 'hr.emirates_id_expiry',
            'passport_expiry' => 'hr.passport_expiry',
            'residency_expiry' => 'hr.residency_expiry',
        ];
    }

    private function isExpiringSoon(mixed $date, Carbon $threshold): bool
    {
        $expiry = Carbon::parse($date);

        return $expiry->lte($threshold) && $expiry->gte(now()->startOfDay());
    }

    private function buildItem(string $employeeId, string $employeeName, string $label, mixed $expiresAt): array
    {
        $expiry = Carbon::parse($expiresAt);

        return [
            'employee_id' => $employeeId,
            'employee_name' => $employeeName,
            'label' => $label,
            'expires_at' => $expiry->format('Y-m-d'),
            'days_left' => (int) now()->startOfDay()->diffInDays($expiry, false),
        ];
    }
}
