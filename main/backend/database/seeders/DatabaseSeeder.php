<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AnimalSeeder::class);
        $this->call(TeamUserSeeder::class);

        /** Parole: "password" (User modelis to automātiski hashē). */
        User::query()->updateOrCreate(
            ['email' => 'demo@laimigas.lv'],
            [
                'name' => 'Demo lietotājs',
                'password' => 'password',
                'is_blocked' => false,
                'is_admin' => false,
            ]
        );

        User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'is_blocked' => false,
                'is_admin' => false,
            ]
        );
    }
}
