<?php

namespace Database\Seeders;

use App\Domain\HR\Models\Attendance;
use App\Domain\HR\Models\EmployeeProfile;
use App\Domain\HR\Models\LeaveRequest;
use App\Enums\AttendanceStatus;
use App\Enums\LeaveStatus;
use App\Enums\LeaveType;
use App\Models\User;
use Illuminate\Database\Seeder;

class HrDemoSeeder extends Seeder
{
    public function run(): void
    {
        $staff = [
            ['email' => 'admin@alwaab.ae', 'code' => 'EMP-001', 'title' => 'مدير النظام', 'dept' => 'الإدارة'],
            ['email' => 'manager@alwaab.ae', 'code' => 'EMP-002', 'title' => 'مدير المبيعات', 'dept' => 'المبيعات'],
            ['email' => 'sales@alwaab.ae', 'code' => 'EMP-003', 'title' => 'مندوب مبيعات', 'dept' => 'المبيعات'],
            ['email' => 'field@alwaab.ae', 'code' => 'EMP-004', 'title' => 'موظف ميداني', 'dept' => 'الميدان'],
            ['email' => 'warehouse@alwaab.ae', 'code' => 'EMP-005', 'title' => 'أمين مخزن', 'dept' => 'المخزون'],
        ];

        foreach ($staff as $s) {
            $user = User::where('email', $s['email'])->first();
            if (! $user) {
                continue;
            }

            EmployeeProfile::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'employee_code' => $s['code'],
                    'job_title' => $s['title'],
                    'department' => $s['dept'],
                    'hire_date' => now()->subYears(rand(1, 5)),
                    'salary_aed' => rand(8000, 25000),
                    'emirates_id' => '784-'.rand(1980, 2000).'-'.rand(1000000, 9999999).'-'.rand(1, 9),
                    'emirates_id_expiry' => now()->addDays(rand(5, 400)),
                    'passport_number' => 'P'.rand(100000, 999999),
                    'passport_expiry' => now()->addDays(rand(10, 500)),
                    'residency_number' => 'RES-'.rand(100000, 999999),
                    'residency_expiry' => now()->addDays(rand(15, 365)),
                    'basic_salary' => rand(6000, 18000),
                    'housing_allowance' => rand(2000, 8000),
                    'iban' => 'AE070331234567890123456',
                ]
            );
        }

        $field = User::where('email', 'field@alwaab.ae')->first();
        if ($field && ! Attendance::exists()) {
            Attendance::create([
                'user_id' => $field->id,
                'check_in_at' => now()->subDays(1)->setTime(8, 15),
                'check_out_at' => now()->subDays(1)->setTime(17, 30),
                'check_in_lat' => 25.0805,
                'check_in_lng' => 55.1403,
                'status' => AttendanceStatus::Present,
            ]);

            Attendance::create([
                'user_id' => $field->id,
                'check_in_at' => now()->setTime(9, 10),
                'check_in_lat' => 25.0805,
                'check_in_lng' => 55.1403,
                'status' => AttendanceStatus::Late,
            ]);
        }

        $sales = User::where('email', 'sales@alwaab.ae')->first();
        $manager = User::where('email', 'manager@alwaab.ae')->first();

        if ($sales && ! LeaveRequest::exists()) {
            LeaveRequest::create([
                'user_id' => $sales->id,
                'type' => LeaveType::Annual,
                'start_date' => now()->addDays(10),
                'end_date' => now()->addDays(14),
                'days' => 5,
                'reason' => 'إجازة سنوية مخططة',
                'status' => LeaveStatus::Pending,
            ]);

            LeaveRequest::create([
                'user_id' => $field->id,
                'type' => LeaveType::Sick,
                'start_date' => now()->subDays(5),
                'end_date' => now()->subDays(3),
                'days' => 3,
                'reason' => 'إجازة مرضية',
                'status' => LeaveStatus::Approved,
                'approved_by' => $manager?->id,
                'approved_at' => now()->subDays(6),
            ]);
        }
    }
}
