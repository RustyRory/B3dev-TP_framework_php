<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([AdminSeeder::class]);

        User::factory()->create([
            'name' => 'Test User',
            'email' => dotenv('USER_TEST_EMAIL'),
            'password' => Hash::make(dotenv('USER_TEST_PASSWORD')),
        ]);

        $this->call([FilmSeeder::class]);
        $this->call([LocalisationSeeder::class]);
    }
}
