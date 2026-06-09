<?php

namespace App\Services;

use App\Domain\HR\Models\EmployeeProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class HrScopeService
{
    public function canManageAll(User $user): bool
    {
        return $user->can('hr.manage');
    }

    public function canViewEmployee(User $user, EmployeeProfile $employee): bool
    {
        if (! $user->can('hr.view')) {
            return false;
        }

        return $this->canManageAll($user) || $employee->user_id === $user->id;
    }

    public function canUpdateEmployee(User $user, EmployeeProfile $employee): bool
    {
        return $this->canManageAll($user);
    }

    public function canUploadDocuments(User $user, EmployeeProfile $employee): bool
    {
        if (! $user->can('hr.view')) {
            return false;
        }

        return $this->canManageAll($user) || $employee->user_id === $user->id;
    }

    public function scopeEmployees(Builder $query, User $user): Builder
    {
        if ($this->canManageAll($user)) {
            return $query;
        }

        return $query->where('user_id', $user->id);
    }
}
