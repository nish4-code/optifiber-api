<?php

namespace Tests\Unit;

use App\DTOs\SmartOlt\OnuData;
use App\DTOs\SmartOlt\OnuSignalData;
use App\Services\SmartOlt\Clients\SmartOltMockClient;
use PHPUnit\Framework\TestCase;

class SmartOltClientTest extends TestCase
{
    public function test_mock_client_returns_olts_zones_and_onus(): void
    {
        $client = new SmartOltMockClient;

        $olts = $client->getOlts();
        $zones = $client->getZones();
        $onus = $client->getAllOnus(1, 20);

        $this->assertNotEmpty($olts);
        $this->assertNotEmpty($zones);
        $this->assertNotEmpty($onus);
        $this->assertInstanceOf(OnuData::class, $onus->first());
    }

    public function test_mock_client_returns_onu_signal_dto(): void
    {
        $client = new SmartOltMockClient;
        $onus = $client->getAllOnus(1, 5);
        $first = $onus->first();

        $this->assertNotNull($first);

        $signal = $client->getOnuSignal($first->uniqueExternalId);

        $this->assertInstanceOf(OnuSignalData::class, $signal);
        $this->assertArrayHasKey('onu_signal_1490', $signal->toArray());
        $this->assertArrayHasKey('onu_signal_1310', $signal->toArray());
    }

    public function test_mock_client_remote_actions_succeed(): void
    {
        $client = new SmartOltMockClient;
        $onuId = $client->getAllOnus(1, 5)->first()->uniqueExternalId;

        $this->assertTrue($client->rebootOnu($onuId));
        $this->assertTrue($client->enableOnu($onuId));
        $this->assertTrue($client->disableOnu($onuId));
    }
}
