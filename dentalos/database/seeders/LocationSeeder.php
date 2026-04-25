<?php
namespace Database\Seeders;
use App\Models\Tenant;
use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationSeeder extends Seeder {
    public function run(): void {
        $tenant = Tenant::where('slug', 'demo')->first();
        Location::create([
            'tenant_id' => $tenant->id,
            'name' => 'Main Street Dental',
            'address_line1' => '123 Main St',
            'city' => 'Anytown',
            'state' => 'NY',
            'zip' => '12345',
            'phone' => '555-0101',
            'timezone' => 'America/New_York',
            'business_hours' => [],
        ]);
        Location::create([
            'tenant_id' => $tenant->id,
            'name' => 'Northside Dental',
            'address_line1' => '456 North Blvd',
            'city' => 'Anytown',
            'state' => 'NY',
            'zip' => '12346',
            'phone' => '555-0202',
            'timezone' => 'America/New_York',
            'business_hours' => [],
        ]);
    }
}
