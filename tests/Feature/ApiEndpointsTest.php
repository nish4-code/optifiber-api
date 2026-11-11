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
            ->assertJsonStructure([
                'mo' => ['listMessage', 'type'],
                'dto',
            ]);

        $dto = $response->json('dto');
        $this->assertCount(12, $dto);
        $this->assertEquals('Azul', $dto[0]['name']);
        $this->assertEquals('Turquesa', $dto[11]['name']);
    }

    public function test_auth_login_and_dashboard_summary(): void
    {
        $user = User::where('email', 'admin@fiberplus.pe')->first();

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'admin@fiberplus.pe',
            'password' => 'password',
        ]);

        $loginResponse->assertStatus(200)
            ->assertJsonStructure([
                'mo' => ['listMessage', 'type'],
                'dto' => [
                    'token',
                    'user' => ['id', 'name', 'email', 'role'],
                ],
            ]);

        $token = $loginResponse->json('dto.token');

        $dashboardResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/dashboard/summary');

        $dashboardResponse->assertStatus(200)
            ->assertJsonStructure([
                'mo' => ['listMessage', 'type'],
                'dto' => [
                    'telemetry' => ['system_online', 'average_attenuation_dbm', 'olt_bandwidth_gbps'],
                    'kpis' => ['naps', 'closures', 'customers', 'network_traffic', 'active_alerts'],
                ],
            ]);
    }

    public function test_map_elements_endpoint(): void
    {
        $user = User::where('email', 'admin@fiberplus.pe')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/map/elements');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'mo' => ['listMessage', 'type'],
                'dto' => ['trunks', 'naps', 'closures', 'customers'],
            ]);
    }

    public function test_smartolt_onus_endpoint(): void
    {
        $user = User::where('email', 'admin@fiberplus.pe')->first();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/smartolt/onus');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'mo' => ['listMessage', 'type'],
                'dto' => ['items', 'total', 'perPage', 'currentPage', 'lastPage', 'from', 'to'],
            ]);
    }

    public function test_naps_and_customers_pagination_with_per_page_parameter(): void
    {
        $user = User::where('email', 'admin@fiberplus.pe')->first();
        $token = $user->createToken('test')->plainTextToken;

        $napsResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/naps?per_page=5&page=1');

        $napsResponse->assertStatus(200)
            ->assertJsonStructure([
                'mo' => ['listMessage', 'type'],
                'dto' => [
                    'items',
                    'total',
                    'perPage',
                    'currentPage',
                    'lastPage',
                    'from',
                    'to',
                ],
            ]);

        $dto = $napsResponse->json('dto');
        $this->assertEquals(5, $dto['perPage']);
        $this->assertEquals(1, $dto['currentPage']);
        $this->assertIsArray($dto['items']);
        $this->assertLessThanOrEqual(5, count($dto['items']));

        $customersResponse = $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/customers?per_page=10&page=1');

        $customersResponse->assertStatus(200)
            ->assertJsonStructure([
                'mo' => ['listMessage', 'type'],
                'dto' => [
                    'items',
                    'total',
                    'perPage',
                    'currentPage',
                    'lastPage',
                    'from',
                    'to',
                ],
            ]);

        $custDto = $customersResponse->json('dto');
        $this->assertEquals(10, $custDto['perPage']);
        $this->assertEquals(1, $custDto['currentPage']);
        $this->assertIsArray($custDto['items']);
    }
}
