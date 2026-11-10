<?php

namespace Database\Seeders;

use App\Models\Zone;
use Illuminate\Database\Seeder;

class ZoneSeeder extends Seeder
{
    public function run(): void
    {
        $zones = [
            [
                'code' => 'ZON-KISH',
                'name' => 'Red de Kishuara',
                'description' => 'Zona troncal principal y nodo central de distribución FTTH',
                'center_lat' => -13.6441000,
                'center_lng' => -73.0871000,
            ],
            [
                'code' => 'ZON-BOLOG',
                'name' => 'Jr. Bolognesi (Zona 1)',
                'description' => 'Sector urbano Centro - Chiclayo (NAP-01 a NAP-04)',
                'center_lat' => -6.7725000,
                'center_lng' => -79.8402000,
            ],
            [
                'code' => 'ZON-BALTA',
                'name' => 'Av. Balta (Zona 2)',
                'description' => 'Sector comercial Av. Balta (NAP-05 a NAP-08)',
                'center_lat' => -6.7760000,
                'center_lng' => -79.8440000,
            ],
            [
                'code' => 'ZON-SANJOSE',
                'name' => 'San José - Sáenz Peña (Zona 3)',
                'description' => 'Sector residencial San José (NAP-09 a NAP-12)',
                'center_lat' => -6.7690000,
                'center_lng' => -79.8370000,
            ],
        ];

        foreach ($zones as $zone) {
            Zone::updateOrCreate(
                ['code' => $zone['code']],
                $zone
            );
        }
    }
}
