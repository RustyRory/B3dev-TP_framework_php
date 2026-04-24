<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->create([
            'name' => 'Admin',
            'email' => dotenv('USER_ADMIN_EMAIL'),
            'password' => Hash::make(dotenv('USER_ADMIN_PASSWORD')),
        ]);
    }
}
