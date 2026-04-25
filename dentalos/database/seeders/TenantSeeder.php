<?php
namespace Database\Seeders;
use App\Models\Tenant;
use Illuminate\Database\Seeder;

class TenantSeeder extends Seeder {
    public function run(): void {
        Tenant::create([
            'name' => 'Demo Dental Practice',
            'slug' => 'demo',
            'plan_type' => 'solo',
            'subscription_status' => 'active',
            'country' => 'US',
            'billing_email' => 'billing@demo.com',
            'timezone' => 'America/New_York',
            'branding' => ['primary_color' => '#3498DB', 'secondary_color' => '#2C3E50'],
        ]);
    }
}
