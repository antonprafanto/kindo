<?php

namespace Database\Seeders;

use App\Models\User;
use App\Support\EmailNormalizer;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $email = EmailNormalizer::normalize(config('app.admin_email', 'admin@example.com'));

        User::updateOrCreate(
            ['email' => $email],
            [
                'name'              => config('app.admin_name', 'Admin'),
                'email'             => $email,
                'password'          => Hash::make(config('app.admin_password', 'changeme-set-in-env')),
                'role'              => 'admin',
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
