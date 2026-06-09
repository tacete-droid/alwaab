<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'dashboard.view',
            'contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete',
            'leads.view', 'leads.create', 'leads.update', 'leads.delete',
            'projects.view', 'projects.create', 'projects.update', 'projects.delete',
            'products.view', 'products.create', 'products.update', 'products.delete',
            'inventory.view', 'inventory.move', 'inventory.manage',
            'quotations.view', 'quotations.create', 'quotations.approve',
            'invoices.view', 'invoices.create',
            'visits.view', 'visits.create', 'visits.manage',
            'chat.view', 'chat.send',
            'hr.view', 'hr.manage',
            'settings.manage', 'audit.view',
            'users.view', 'users.create', 'users.update', 'users.manage',
            'roles.manage',
            'access-ai-studio',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'super_admin' => $permissions,
            'manager' => array_diff($permissions, [
                'settings.manage', 'contacts.delete', 'projects.delete', 'products.delete',
                'hr.manage', 'visits.create', 'users.view', 'users.create', 'users.update',
                'users.manage', 'roles.manage', 'audit.view', 'access-ai-studio',
            ]),
            'sales_rep' => [
                'dashboard.view', 'contacts.view', 'contacts.create', 'contacts.update',
                'leads.view', 'leads.create', 'leads.update',
                'projects.view', 'projects.create', 'projects.update',
                'products.view', 'quotations.view', 'quotations.create',
                'invoices.view', 'invoices.create',
                'visits.view', 'chat.view', 'chat.send', 'hr.view',
            ],
            'field_officer' => [
                'dashboard.view', 'projects.view', 'products.view',
                'visits.view', 'visits.create', 'chat.view', 'chat.send', 'hr.view', 'access-ai-studio',
            ],
            'warehouse_staff' => [
                'dashboard.view', 'products.view', 'inventory.view', 'inventory.move', 'inventory.manage',
                'chat.view', 'chat.send', 'hr.view',
            ],
            'customer' => [
                'products.view', 'quotations.view', 'quotations.create',
            ],
            'hr_manager' => [
                'dashboard.view', 'hr.view', 'hr.manage', 'chat.view', 'chat.send', 'access-ai-studio',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
