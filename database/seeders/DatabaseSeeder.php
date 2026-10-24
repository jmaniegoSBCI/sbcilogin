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
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if ($email && $password) {
            User::updateOrCreate(
                ['email' => mb_strtolower($email)],
                [
                    'name' => 'SBCI Administrator',
                    'first_name' => 'SBCI',
                    'last_name' => 'Administrator',
                    'password' => $password,
                    'role' => User::ROLE_ADMIN,
                    'archived_at' => null,
                ],
            );
        }
    }
}
