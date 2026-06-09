<?php

namespace Database\Seeders;

use App\Enums\Locale;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name_ar' => 'مدير النظام',
                'name_en' => 'System Admin',
                'email' => 'admin@alwaab.ae',
                'role' => 'super_admin',
            ],
            [
                'name_ar' => 'أحمد المدير',
                'name_en' => 'Ahmed Manager',
                'email' => 'manager@alwaab.ae',
                'role' => 'manager',
            ],
            [
                'name_ar' => 'سارة مبيعات',
                'name_en' => 'Sara Sales',
                'email' => 'sales@alwaab.ae',
                'role' => 'sales_rep',
            ],
            [
                'name_ar' => 'خالد ميداني',
                'name_en' => 'Khalid Field',
                'email' => 'field@alwaab.ae',
                'role' => 'field_officer',
            ],
            [
                'name_ar' => 'محمد مخزون',
                'name_en' => 'Mohammed Warehouse',
                'email' => 'warehouse@alwaab.ae',
                'role' => 'warehouse_staff',
            ],
            [
                'name_ar' => 'فاطمة الموارد البشرية',
                'name_en' => 'Fatima HR Manager',
                'email' => 'hr@alwaab.ae',
                'role' => 'hr_manager',
            ],
            [
                'name_ar' => 'مطور العقارات الذهبي',
                'name_en' => 'Golden Developer',
                'email' => 'procurement@golden.ae',
                'role' => 'customer',
            ],
        ];

        foreach ($users as $data) {
            $user = User::firstOrCreate(
                ['email' => $data['email']],
                [
                    'name_ar' => $data['name_ar'],
                    'name_en' => $data['name_en'],
                    'phone' => '+971500000000',
                    'password' => Hash::make('password'),
                    'locale' => Locale::Arabic,
                    'is_active' => true,
                ]
            );

            $user->assignRole($data['role']);
        }
    }
}
