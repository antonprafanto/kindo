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
            ['email' => env('ADMIN_EMAIL', 'admin@example.com')],
            [
                'name'              => env('ADMIN_NAME', 'Admin'),
                'email'             => env('ADMIN_EMAIL', 'admin@example.com'),
                'password'          => Hash::make(env('ADMIN_PASSWORD', 'changeme-set-in-env')),
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
