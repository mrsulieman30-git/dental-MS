<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $tenantId = Str::uuid();
        DB::table('tenants')->insert([
            'id' => $tenantId,
            'name' => 'DentalOS Demo Group',
            'slug' => 'demo',
            'plan_type' => 'group',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $locations = [];
        for ($i = 1; $i <= 2; $i++) {
            $locId = Str::uuid();
            $locations[] = $locId;
            DB::table('locations')->insert([
                'id' => $locId,
                'tenant_id' => $tenantId,
                'name' => "Demo Location {$i}",
                'address_line1' => "{$i}00 Dental St",
                'city' => 'Smilesville',
                'state' => 'CA',
                'zip' => '90210',
                'is_active' => true,
                'created_at' => now(),
            ]);
        }

        $adminId = Str::uuid();
        DB::table('users')->insert([
            'id' => $adminId,
            'tenant_id' => $tenantId,
            'email' => 'admin@dentalos.com',
            'password_hash' => Hash::make('password'),
            'first_name' => 'John',
            'last_name' => 'Admin',
            'role' => 'admin',
            'is_active' => true,
            'created_at' => now(),
        ]);

        $providerId = Str::uuid();
        DB::table('users')->insert([
            'id' => $providerId,
            'tenant_id' => $tenantId,
            'email' => 'doctor@dentalos.com',
            'password_hash' => Hash::make('password'),
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'role' => 'doctor',
            'is_active' => true,
            'created_at' => now(),
        ]);

        DB::table('provider_profiles')->insert([
            'id' => Str::uuid(),
            'user_id' => $providerId,
            'specialty' => 'General Dentistry',
            'created_at' => now(),
        ]);

        // Link staff to locations
        foreach ($locations as $locId) {
            DB::table('user_locations')->insert([
                'id' => Str::uuid(),
                'user_id' => $adminId,
                'location_id' => $locId,
                'is_primary' => true,
                'created_at' => now(),
            ]);
            DB::table('user_locations')->insert([
                'id' => Str::uuid(),
                'user_id' => $providerId,
                'location_id' => $locId,
                'is_primary' => true,
                'created_at' => now(),
            ]);
        }

        // Generate 50 patients
        for ($i = 1; $i <= 50; $i++) {
            $patientId = Str::uuid();
            DB::table('patients')->insert([
                'id' => $patientId,
                'tenant_id' => $tenantId,
                'patient_number' => "P-1000{$i}",
                'first_name' => "Patient{$i}",
                'last_name' => 'Demo',
                'date_of_birth' => '1990-01-01',
                'status' => 'active',
                'primary_location_id' => $locations[0],
                'primary_provider_id' => $providerId,
                'created_at' => now(),
            ]);
        }

        // Seed defaults for this tenant
        DefaultSettingsSeeder::seedForTenant($tenantId, $adminId);
    }
}
