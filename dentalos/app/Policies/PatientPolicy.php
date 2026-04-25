<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Patient;
use App\Enums\Permission;
use App\Services\PermissionService;

class PatientPolicy
{
    public function viewAny(User $user) { return $this->hasPermission($user, Permission::PATIENTS_READ); }
    public function view(User $user, Patient $patient) { return $this->hasPermission($user, Permission::PATIENTS_READ) && $user->tenant_id === $patient->tenant_id; }
    public function create(User $user) { return $this->hasPermission($user, Permission::PATIENTS_WRITE); }
    public function update(User $user, Patient $patient) { return $this->hasPermission($user, Permission::PATIENTS_WRITE) && $user->tenant_id === $patient->tenant_id; }
    public function delete(User $user, Patient $patient) { return $this->hasPermission($user, Permission::PATIENTS_DELETE) && $user->tenant_id === $patient->tenant_id; }

    private function hasPermission(User $user, Permission $permission) {
        return in_array($permission->value, PermissionService::getRolePermissions($user->role), true);
    }
}
