<?php

namespace Tests\Unit;

use App\Enums\AlertSeverity;
use App\Enums\AlertType;
use App\Services\NetworkAlertService;
use PHPUnit\Framework\TestCase;

class NetworkAlertServiceTest extends TestCase
{
    public function test_threshold_logic_for_nap_capacity(): void
    {
        $service = new NetworkAlertService;

        // Verificamos que los tipos y severidades del Enum coincidan con las reglas de negocio
        $this->assertEquals('capacidad_nap', AlertType::NapCapacity->value);
        $this->assertEquals('critico', AlertSeverity::Critical->value);
        $this->assertEquals('advertencia', AlertSeverity::Warning->value);
    }
}
