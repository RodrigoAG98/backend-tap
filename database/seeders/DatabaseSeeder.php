<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::create([
            'user_code' => bin2hex(random_bytes(5)),
            'user' => 'admin@tap.com',
            'name' => 'Administrador',
            'password' => Hash::make('password'),
            'profiles' => ['users','products','profiles']
        ]);
    }
}
