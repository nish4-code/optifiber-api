<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'admin@optifiber.pe'],
            [
                'name' => 'Optifiber Admin',
                'password' => bcrypt('password123'),
            ]
        );

        $this->call(SmartOltSeeder::class);
    }
}
