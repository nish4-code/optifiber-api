<?php

namespace Database\Seeders;

use App\Models\User;
use App\Services\SmartOlt\SmartOltService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(SmartOltService $smartOltService): void
    {
        User::updateOrCreate(
            ['email' => 'admin@fiberplus.pe'],
            [
                'name' => 'Jerzy León (Admin)',
                'role' => 'admin',
                'password' => Hash::make('password'),
            ]
        );

        $this->call([
            FiberColorSeeder::class,
            ZoneSeeder::class,
            DemoNetworkSeeder::class,
        ]);

        $smartOltService->syncToDatabase();
    }
}
