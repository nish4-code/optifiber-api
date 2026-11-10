<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class ApiEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    public function test_fiber_colors_endpoint_returns_12_tia_colors(): void
    {
        $response = $this->getJson('/api/fiber-colors');

        $response->assertStatus(200)
            ->assertJsonCount(12)
            ->assertJsonFragment(['position' => 1, 'name' => 'Azul', 'hex' => '#0066CC'])
            ->assertJsonFragment(['position' => 12, 'name' => 'Turquesa', 'hex' => '#00CED1']);
    }

    public function test_auth_login_and_dashboard_summary(): void
    {
        $user = User::where('email', 'admin@fiberplus.pe')->first();

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@fiberplus.pe',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure(['token', 'user' => ['id', 'name', 'email', 'role']]);

        $token = $loginResponse->json('token');

        $dashboardResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard/summary');

        $dashboardResponse->assertStatus(200)
            ->assertJsonStructure([
                'telemetry' => ['system_online', 'average_attenuation_dbm', 'olt_bandwidth_gbps'],
                'kpis' => ['naps', 'closures', 'customers', 'network_traffic', 'active_alerts'],
            ]);
    }

    public function test_map_elements_endpoint(): void
    {
        $user = User::where('email', 'admin@fiberplus.pe')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/map/elements');

        $response->assertStatus(200)
            ->assertJsonStructure(['trunks', 'naps', 'closures', 'customers']);
    }

    public function test_smartolt_onus_endpoint(): void
    {
        $user = User::where('email', 'admin@fiberplus.pe')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/smartolt/onus');

        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'current_page']);
    }
}
