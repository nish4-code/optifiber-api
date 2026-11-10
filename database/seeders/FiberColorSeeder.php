<?php

namespace Database\Seeders;

use App\Models\FiberColor;
use Illuminate\Database\Seeder;

class FiberColorSeeder extends Seeder
{
    public function run(): void
    {
        $colors = [
            ['position' => 1, 'name' => 'Azul', 'tia_name' => 'Blue', 'hex' => '#0066CC'],
            ['position' => 2, 'name' => 'Naranja', 'tia_name' => 'Orange', 'hex' => '#FF6600'],
            ['position' => 3, 'name' => 'Verde', 'tia_name' => 'Green', 'hex' => '#009933'],
            ['position' => 4, 'name' => 'Marrón', 'tia_name' => 'Brown', 'hex' => '#8B4513'],
            ['position' => 5, 'name' => 'Gris', 'tia_name' => 'Slate', 'hex' => '#808080'],
            ['position' => 6, 'name' => 'Blanco', 'tia_name' => 'White', 'hex' => '#F8FAFC'],
            ['position' => 7, 'name' => 'Rojo', 'tia_name' => 'Red', 'hex' => '#E60000'],
            ['position' => 8, 'name' => 'Negro', 'tia_name' => 'Black', 'hex' => '#1E293B'],
            ['position' => 9, 'name' => 'Amarillo', 'tia_name' => 'Yellow', 'hex' => '#FFD700'],
            ['position' => 10, 'name' => 'Violeta', 'tia_name' => 'Violet', 'hex' => '#8A2BE2'],
            ['position' => 11, 'name' => 'Rosa', 'tia_name' => 'Rose', 'hex' => '#FF69B4'],
            ['position' => 12, 'name' => 'Turquesa', 'tia_name' => 'Aqua', 'hex' => '#00CED1'],
        ];

        foreach ($colors as $color) {
            FiberColor::updateOrCreate(
                ['position' => $color['position']],
                $color
            );
        }
    }
}
