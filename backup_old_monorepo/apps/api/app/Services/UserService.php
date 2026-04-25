<?php

namespace App\Services;

use App\Models\User;
use App\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Create a new user for a tenant.
     *
     * @param Tenant $tenant
     * @param array $data
     * @return User
     */
    public function createUser(Tenant $tenant, array $data): User
    {
        $user = new User();
        $user->tenant_id = $tenant->id;
        $user->email = $data['email'];
        $user->password_hash = Hash::make($data['password']);
        $user->first_name = $data['first_name'];
        $user->last_name = $data['last_name'];
        $user->role = $data['role'] ?? 'read_only';
        $user->is_active = true;
        $user->must_change_password = $data['must_change_password'] ?? true;
        $user->save();

        if (isset($data['location_ids'])) {
            $user->locations()->sync($data['location_ids']);
        }

        return $user;
    }

    /**
     * Update user profile.
     *
     * @param User $user
     * @param array $data
     * @return User
     */
    public function updateProfile(User $user, array $data): User
    {
        $user->fill($data);
        
        if (isset($data['password'])) {
            $user->password_hash = Hash::make($data['password']);
        }

        $user->save();

        return $user;
    }
}
