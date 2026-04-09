<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Komandas konti — pieteikšanās ar e-pastu un paroli "password".
 * Ja vajag citus e-pastus, maini šeit un palaid: php artisan db:seed --class=TeamUserSeeder
 */
class TeamUserSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            ['name' => 'Rudolfs Liepiņš', 'email' => 'rudolfs.liepins@mapon.com', 'is_admin' => true, 'is_owner' => true],
            ['name' => 'Eduards', 'email' => 'eduards@laimigas.lv', 'is_admin' => false, 'is_owner' => false],
            ['name' => 'Kristers', 'email' => 'kristers@laimigas.lv', 'is_admin' => false, 'is_owner' => false],
        ];

        foreach ($accounts as $row) {
            User::query()->updateOrCreate(
                ['email' => $row['email']],
                [
                    'name' => $row['name'],
                    'password' => 'password',
                    'is_blocked' => false,
                    'is_admin' => $row['is_admin'],
                    'is_owner' => $row['is_owner'],
                ]
            );
        }
    }
}
