<?php

namespace Tests\Unit;

use App\Services\FiberTopologyService;
use PHPUnit\Framework\TestCase;

class FiberTopologyServiceTest extends TestCase
{
    public function test_calculate_global_strand_number_for_144_fibers(): void
    {
        $service = new FiberTopologyService;

        // Primer hilo del primer tubo (Azul / Azul)
        $this->assertEquals(1, $service->calculateGlobalStrandNumber(1, 1));

        // Último hilo del primer tubo (Azul / Turquesa)
        $this->assertEquals(12, $service->calculateGlobalStrandNumber(1, 12));

        // Primer hilo del segundo tubo (Naranja / Azul)
        $this->assertEquals(13, $service->calculateGlobalStrandNumber(2, 1));

        // Hilo 45 (Línea 45 en Figma: Tubo 4 Marrón, Hilo 9 Amarillo) -> ((4-1)*12) + 9 = 36 + 9 = 45
        $this->assertEquals(45, $service->calculateGlobalStrandNumber(4, 9));

        // Último hilo del cable de 144 (Turquesa / Turquesa) -> ((12-1)*12) + 12 = 144
        $this->assertEquals(144, $service->calculateGlobalStrandNumber(12, 12));
    }
}
