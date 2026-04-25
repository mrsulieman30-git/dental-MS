<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $tenant = Tenant::where('slug', 'demo')->first();

        User::create([
            'tenant_id' => $tenant->id,
            'email' => 'admin@demo.com',
            'password' => Hash::make('password'),
            'first_name' => 'System',
            'last_name' => 'Admin',
            'role' => 'admin',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'email' => 'doctor1@demo.com',
            'password' => Hash::make('password'),
            'first_name' => 'John',
            'last_name' => 'Doe',
            'role' => 'doctor',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'email' => 'doctor2@demo.com',
            'password' => Hash::make('password'),
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => 'doctor',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'email' => 'hygienist@demo.com',
            'password' => Hash::make('password'),
            'first_name' => 'Amy',
            'last_name' => 'White',
            'role' => 'hygienist',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'email' => 'frontdesk@demo.com',
            'password' => Hash::make('password'),
            'first_name' => 'Sarah',
            'last_name' => 'Jones',
            'role' => 'front_desk',
        ]);

        User::create([
            'tenant_id' => $tenant->id,
            'email' => 'billing@demo.com',
            'password' => Hash::make('password'),
            'first_name' => 'Mike',
            'last_name' => 'Brown',
            'role' => 'billing',
        ]);
    }
}
