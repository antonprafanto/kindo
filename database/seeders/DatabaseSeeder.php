<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'anton.prafanto@gmail.com'],
            [
                'name'              => 'Anton Prafanto',
                'email'             => 'anton.prafanto@gmail.com',
                'password'          => Hash::make('Admin@123!'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            CategorySeeder::class,
            TagSeeder::class,
            ArticleSeeder::class,
        ]);
    }
}
