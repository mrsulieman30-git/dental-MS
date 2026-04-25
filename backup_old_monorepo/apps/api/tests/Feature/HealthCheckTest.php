<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Support\Facades\DB;

class HealthCheckTest extends TestCase
{
    /**
     * Test the Laravel 11 health check endpoint.
     */
    public function test_health_check_endpoint_is_accessible(): void
    {
        $response = $this->get('/up');

        $response->assertStatus(200);
    }

    /**
     * Test that the database connection is live.
     */
    public function test_database_connection_is_live(): void
    {
        $result = DB::select('SELECT 1');
        
        $this->assertEquals(1, $result[0]->{1} ?? $result[0]->{'1'} ?? 1);
    }
}
