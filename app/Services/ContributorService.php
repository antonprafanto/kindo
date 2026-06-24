<?php

namespace App\Services;

use App\Models\ContributorApplication;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ContributorService
{
    public function __construct(
        private FilamentPasswordResetService $passwordReset,
    ) {}

    public function approve(ContributorApplication $application): User
    {
        if ($application->status !== 'pending') {
            throw new \InvalidArgumentException('Aplikasi ini sudah diproses.');
        }

        $existingUser = User::where('email', $application->email)->first();

        if ($existingUser?->isAdmin()) {
            throw new \InvalidArgumentException('Email ini terdaftar sebagai admin.');
        }

        return DB::transaction(function () use ($application, $existingUser) {
            if (! $existingUser) {
                $user = User::create([
                    'name'              => $application->name,
                    'email'             => $application->email,
                    'password'          => str()->password(16),
                    'role'              => 'author',
                    'email_verified_at' => now(),
                ]);
            } else {
                $user = $existingUser;

                if (! $user->isAuthor()) {
                    $user->update(['role' => 'author']);
                }
            }

            $application->update([
                'status'      => 'approved',
                'user_id'     => $user->id,
                'reviewed_at' => now(),
            ]);

            $this->passwordReset->sendResetLink($user);

            Mail::send('emails.contributor-approved', [
                'applicantName' => $application->name,
                'loginUrl'      => url('/admin/login'),
            ], function ($message) use ($application) {
                $message->to($application->email)
                    ->subject('Selamat! Aplikasi Kontributor Koding Indonesia Disetujui');
            });

            return $user->fresh();
        });
    }

    public function reject(ContributorApplication $application, ?string $reason = null): void
    {
        if ($application->status !== 'pending') {
            throw new \InvalidArgumentException('Aplikasi ini sudah diproses.');
        }

        $application->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
            'reviewed_at'      => now(),
        ]);

        Mail::send('emails.contributor-rejected', [
            'applicantName'   => $application->name,
            'rejectionReason' => $reason,
            'reapplyUrl'      => route('contributor.apply'),
        ], function ($message) use ($application) {
            $message->to($application->email)
                ->subject('Update Aplikasi Kontributor Koding Indonesia');
        });
    }
}
