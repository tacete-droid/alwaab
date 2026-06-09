<?php

namespace App\Support;

class PermissionGroups
{
    public static function all(): array
    {
        return [
            [
                'key' => 'dashboard',
                'label_key' => 'access.groups.dashboard',
                'permissions' => ['dashboard.view'],
            ],
            [
                'key' => 'crm',
                'label_key' => 'access.groups.crm',
                'permissions' => [
                    'contacts.view', 'contacts.create', 'contacts.update', 'contacts.delete',
                    'leads.view', 'leads.create', 'leads.update', 'leads.delete',
                ],
            ],
            [
                'key' => 'projects',
                'label_key' => 'access.groups.projects',
                'permissions' => [
                    'projects.view', 'projects.create', 'projects.update', 'projects.delete',
                ],
            ],
            [
                'key' => 'quotations',
                'label_key' => 'access.groups.quotations',
                'permissions' => [
                    'quotations.view', 'quotations.create', 'quotations.approve',
                    'invoices.view', 'invoices.create',
                ],
            ],
            [
                'key' => 'inventory',
                'label_key' => 'access.groups.inventory',
                'permissions' => [
                    'products.view', 'products.create', 'products.update', 'products.delete',
                    'inventory.view', 'inventory.move', 'inventory.manage',
                ],
            ],
            [
                'key' => 'field',
                'label_key' => 'access.groups.field',
                'permissions' => ['visits.view', 'visits.create', 'visits.manage'],
            ],
            [
                'key' => 'hr',
                'label_key' => 'access.groups.hr',
                'permissions' => ['hr.view', 'hr.manage'],
            ],
            [
                'key' => 'communication',
                'label_key' => 'access.groups.communication',
                'permissions' => ['chat.view', 'chat.send'],
            ],
            [
                'key' => 'ai_studio',
                'label_key' => 'access.groups.ai_studio',
                'permissions' => ['access-ai-studio'],
            ],
            [
                'key' => 'admin',
                'label_key' => 'access.groups.admin',
                'permissions' => [
                    'users.view', 'users.create', 'users.update', 'users.manage',
                    'roles.manage', 'settings.manage', 'audit.view',
                ],
            ],
        ];
    }

    public static function flatPermissions(): array
    {
        return collect(self::all())->pluck('permissions')->flatten()->unique()->values()->all();
    }
}
