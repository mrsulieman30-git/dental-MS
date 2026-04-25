<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Appointment;
use App\Enums\Permission;
use App\Services\PermissionService;

class AppointmentPolicy
{
    public function viewAny(User $user) { return $this->hasPermission($user, Permission::SCHEDULE_READ); }
    public function view(User $user, Appointment $appointment) { return $this->hasPermission($user, Permission::SCHEDULE_READ) && $user->tenant_id === $appointment->tenant_id; }
    public function create(User $user) { return $this->hasPermission($user, Permission::SCHEDULE_WRITE); }
    public function update(User $user, Appointment $appointment) { return $this->hasPermission($user, Permission::SCHEDULE_WRITE) && $user->tenant_id === $appointment->tenant_id; }
    public function delete(User $user, Appointment $appointment) { return $this->hasPermission($user, Permission::SCHEDULE_WRITE) && $user->tenant_id === $appointment->tenant_id; }

    private function hasPermission(User $user, Permission $permission) {
        return in_array($permission->value, PermissionService::getRolePermissions($user->role), true);
    }
}
