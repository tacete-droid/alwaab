<?php

namespace App\Http\Controllers\Web\Access;

use App\Domain\HR\Models\EmployeeProfile;
use App\Enums\Locale;
use App\Http\Controllers\Controller;
use App\Http\Requests\Access\StoreAccessUserRequest;
use App\Http\Requests\Access\UpdateAccessUserRequest;
use App\Http\Requests\Access\UpdateRolePermissionsRequest;
use App\Models\User;
use App\Support\PermissionGroups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AccessController extends Controller
{
    public function index(Request $request): Response
    {
        abort_unless($request->user()?->can('users.view'), 403);

        $users = User::query()
            ->with(['roles', 'permissions', 'employeeProfile'])
            ->when($request->search, fn ($q, $search) => $q->where(function ($query) use ($search) {
                $query->where('name_ar', 'ilike', "%{$search}%")
                    ->orWhere('name_en', 'ilike', "%{$search}%")
                    ->orWhere('email', 'ilike', "%{$search}%");
            }))
            ->orderBy('name_ar')
            ->paginate(15)
            ->withQueryString();

        $roles = Role::with('permissions')->orderBy('name')->get()->map(fn (Role $role) => [
            'id' => $role->id,
            'name' => $role->name,
            'label' => __("access.roles.{$role->name}", [], app()->getLocale()) !== "access.roles.{$role->name}"
                ? __("access.roles.{$role->name}")
                : str_replace('_', ' ', ucfirst($role->name)),
            'permissions' => $role->permissions->pluck('name'),
        ]);

        return Inertia::render('Access/Index', [
            'users' => $users,
            'roles' => $roles,
            'filters' => $request->only(['search', 'tab']),
            'permissionGroups' => $this->permissionGroupsPayload(),
            'canCreate' => $request->user()->can('users.create'),
            'canUpdate' => $request->user()->can('users.update'),
            'canManageRoles' => $request->user()->can('roles.manage'),
        ]);
    }

    public function show(Request $request, User $user): Response
    {
        abort_unless($request->user()?->can('users.view'), 403);

        $user->load(['roles', 'permissions', 'employeeProfile']);

        return Inertia::render('Access/Show', [
            'accessUser' => [
                'id' => $user->id,
                'name_ar' => $user->name_ar,
                'name_en' => $user->name_en,
                'email' => $user->email,
                'phone' => $user->phone,
                'locale' => $user->locale?->value ?? 'ar',
                'is_active' => $user->is_active,
                'last_login_at' => $user->last_login_at,
                'roles' => $user->roles->pluck('name'),
                'permissions' => $user->getDirectPermissions()->pluck('name'),
                'role_permissions' => $user->getPermissionsViaRoles()->pluck('name'),
                'employee_profile' => $user->employeeProfile,
            ],
            'permissionGroups' => $this->permissionGroupsPayload(),
            'canUpdate' => $request->user()->can('users.update'),
        ]);
    }

    public function store(StoreAccessUserRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request) {
            $data = $request->validated();

            $user = User::create([
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => Hash::make($data['password']),
                'locale' => Locale::from($data['locale']),
                'is_active' => true,
            ]);

            $user->syncRoles([$data['role']]);
            $user->syncPermissions($data['permissions'] ?? []);

            if (! empty($data['employee_code']) || ! empty($data['job_title']) || ! empty($data['department'])) {
                EmployeeProfile::create([
                    'user_id' => $user->id,
                    'employee_code' => $data['employee_code'] ?? 'EMP-'.strtoupper(substr($user->id, 0, 6)),
                    'job_title' => $data['job_title'] ?? null,
                    'department' => $data['department'] ?? null,
                ]);
            }
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->route('access.index')->with('success', __('access.user_created'));
    }

    public function update(UpdateAccessUserRequest $request, User $user): RedirectResponse
    {
        if ($user->id === $request->user()->id && $request->boolean('is_active') === false) {
            return redirect()->back()->with('error', __('settings.cannot_deactivate_self'));
        }

        DB::transaction(function () use ($request, $user) {
            $data = $request->validated();

            $user->update([
                'name_ar' => $data['name_ar'],
                'name_en' => $data['name_en'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'locale' => Locale::from($data['locale']),
                'is_active' => $data['is_active'] ?? $user->is_active,
            ]);

            if (! empty($data['password'])) {
                $user->update(['password' => Hash::make($data['password'])]);
            }

            $user->syncRoles([$data['role']]);
            $user->syncPermissions($data['permissions'] ?? []);

            $profile = EmployeeProfile::firstOrNew(['user_id' => $user->id]);
            $profile->fill([
                'employee_code' => $data['employee_code'] ?? $profile->employee_code ?? 'EMP-'.strtoupper(substr($user->id, 0, 6)),
                'job_title' => $data['job_title'] ?? null,
                'department' => $data['department'] ?? null,
                'salary_aed' => $data['salary_aed'] ?? $profile->salary_aed,
                'hire_date' => $data['hire_date'] ?? $profile->hire_date,
                'emergency_contact' => $data['emergency_contact'] ?? $profile->emergency_contact,
            ]);
            $profile->save();
        });

        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->back()->with('success', __('messages.updated'));
    }

    public function updateRolePermissions(UpdateRolePermissionsRequest $request, Role $role): RedirectResponse
    {
        if ($role->name === 'super_admin') {
            return redirect()->back()->with('error', __('access.cannot_edit_super_admin'));
        }

        $role->syncPermissions($request->validated('permissions'));
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        return redirect()->back()->with('success', __('access.role_updated'));
    }

    private function permissionGroupsPayload(): array
    {
        $all = Permission::orderBy('name')->pluck('name');

        return collect(PermissionGroups::all())->map(function (array $group) use ($all) {
            $permissions = collect($group['permissions'])
                ->intersect($all)
                ->values()
                ->map(fn (string $name) => [
                    'name' => $name,
                    'label' => __("access.permissions.{$name}", [], app()->getLocale()) !== "access.permissions.{$name}"
                        ? __("access.permissions.{$name}")
                        : $name,
                ]);

            return [
                'key' => $group['key'],
                'label' => __($group['label_key']),
                'permissions' => $permissions,
            ];
        })->filter(fn ($g) => $g['permissions']->isNotEmpty())->values()->all();
    }
}
