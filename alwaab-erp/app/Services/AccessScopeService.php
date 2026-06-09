<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class AccessScopeService
{
    public function isAdmin(User $user): bool
    {
        return $user->hasRole('super_admin') || $user->can('users.manage');
    }

    public function scopeAssignedTo(Builder $query, User $user, string $column = 'assigned_to'): Builder
    {
        if ($this->isAdmin($user)) {
            return $query;
        }

        return $query->where($column, $user->id);
    }

    public function scopeCreatedBy(Builder $query, User $user, string $column = 'created_by'): Builder
    {
        if ($this->isAdmin($user)) {
            return $query;
        }

        return $query->where($column, $user->id);
    }

    public function canAccessRecord(User $user, ?string $ownerId, string $ownerColumn = 'assigned_to'): bool
    {
        if ($this->isAdmin($user)) {
            return true;
        }

        return $ownerId === $user->id;
    }

    public function employeeUserIds(User $user): ?array
    {
        if ($this->isAdmin($user) || $user->can('hr.manage')) {
            return null;
        }

        return [$user->id];
    }
}
