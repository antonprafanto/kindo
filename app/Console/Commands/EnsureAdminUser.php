<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\EmailNormalizer;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class EnsureAdminUser extends Command
{
    protected $signature = 'kindo:ensure-admin
                            {--reset-password : Set password from ADMIN_PASSWORD in .env}
                            {--fix-gmail-dots : Normalize Gmail addresses for all users}';

    protected $description = 'Create or repair the admin user from ADMIN_EMAIL in .env (safe for production)';

    public function handle(): int
    {
        if ($this->option('fix-gmail-dots')) {
            $this->fixGmailDots();

            return self::SUCCESS;
        }

        $rawEmail = config('app.admin_email');
        $password = config('app.admin_password');
        $name = config('app.admin_name', 'Admin');

        if (blank($rawEmail)) {
            $this->error('ADMIN_EMAIL is not set in .env');

            return self::FAILURE;
        }

        $email = EmailNormalizer::normalize($rawEmail);

        if ($rawEmail !== $email) {
            $this->line("Normalized ADMIN_EMAIL: {$rawEmail} → {$email}");
        }

        $user = User::query()
            ->where('email', $email)
            ->orWhere('email', $rawEmail)
            ->first();

        if (! $user) {
            $user = User::query()
                ->where('role', 'admin')
                ->get()
                ->first(fn (User $candidate) => EmailNormalizer::normalize($candidate->email) === $email);
        }

        if ($user) {
            $updates = [
                'name'              => $name,
                'role'              => 'admin',
                'email_verified_at' => $user->email_verified_at ?? now(),
            ];

            if ($user->email !== $email) {
                $updates['email'] = $email;
                $this->line("Fixing email: {$user->email} → {$email}");
            }

            if ($this->option('reset-password')) {
                if (blank($password)) {
                    $this->error('ADMIN_PASSWORD is not set in .env (required with --reset-password)');

                    return self::FAILURE;
                }

                $updates['password'] = Hash::make($password);
            }

            $user->update($updates);
            $this->info("Admin user updated: {$user->fresh()->email}");

            return self::SUCCESS;
        }

        if (blank($password)) {
            $this->error('No admin user found. Set ADMIN_PASSWORD in .env to create one.');

            return self::FAILURE;
        }

        $user = User::create([
            'name'              => $name,
            'email'             => $email,
            'password'          => Hash::make($password),
            'role'              => 'admin',
            'email_verified_at' => now(),
        ]);

        $this->info("Admin user created: {$user->email}");

        return self::SUCCESS;
    }

    private function fixGmailDots(): void
    {
        $fixed = 0;

        User::query()->each(function (User $user) use (&$fixed) {
            $normalized = EmailNormalizer::normalize($user->email);

            if ($normalized === $user->email) {
                return;
            }

            if (User::query()->where('email', $normalized)->where('id', '!=', $user->id)->exists()) {
                $this->warn("Skip {$user->email}: {$normalized} already taken");

                return;
            }

            $this->line("{$user->email} → {$normalized}");
            $user->update(['email' => $normalized]);
            $fixed++;
        });

        $this->info("Fixed {$fixed} user email(s).");
    }
}
