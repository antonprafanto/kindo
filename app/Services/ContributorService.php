<?php

namespace App\Services;

use App\Models\ContributorApplication;
use App\Models\User;
use App\Support\MultipartMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ContributorService
{
    public function __construct(
        private FilamentPasswordResetService $passwordReset,
    ) {}

    /**
     * @return array{user: User, email_warnings: list<string>}
     */
    public function approve(ContributorApplication $application): array
    {
        if ($application->status !== 'pending') {
            throw new \InvalidArgumentException('Aplikasi ini sudah diproses.');
        }

        $existingUser = User::where('email', $application->email)->first();

        if ($existingUser?->isAdmin()) {
            throw new \InvalidArgumentException('Email ini terdaftar sebagai admin.');
        }

        $user = DB::transaction(function () use ($application, $existingUser) {
            if (! $existingUser) {
                $user = User::create([
                    'name'              => $application->name,
                    'email'             => $application->email,
                    'password'          => str()->password(16),
                    'role'              => 'author',
                    'slug'              => User::generateUniqueSlug($application->name),
                    'expertise'         => $application->topic_expertise,
                    'email_verified_at' => now(),
                ]);
            } else {
                $user = $existingUser;

                $updates = [];

                if (! $user->isAuthor()) {
                    $updates['role'] = 'author';
                }

                if (blank($user->slug)) {
                    $updates['slug'] = User::generateUniqueSlug($user->name, $user->id);
                }

                if (blank($user->expertise) && filled($application->topic_expertise)) {
                    $updates['expertise'] = $application->topic_expertise;
                }

                if ($updates !== []) {
                    $user->update($updates);
                }
            }

            $application->update([
                'status'      => 'approved',
                'user_id'     => $user->id,
                'reviewed_at' => now(),
            ]);

            return $user->fresh();
        });

        $emailWarnings = [];

        try {
            $this->passwordReset->sendResetLink($user);
        } catch (\Throwable $e) {
            Log::warning('Contributor approve: password reset email failed', [
                'application_id' => $application->id,
                'user_id'        => $user->id,
                'error'          => $e->getMessage(),
            ]);
            $emailWarnings[] = 'email reset password';
        }

        try {
            MultipartMail::send('emails.contributor-approved', [
                'applicantName' => $application->name,
                'loginUrl'      => url('/admin/login'),
            ], function ($message) use ($application) {
                $message->to($application->email)
                    ->subject('Selamat! Aplikasi Kontributor Koding Indonesia Disetujui');
            });
        } catch (\Throwable $e) {
            Log::warning('Contributor approve: approval email failed', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);
            $emailWarnings[] = 'email pemberitahuan disetujui';
        }

        return [
            'user'           => $user,
            'email_warnings' => $emailWarnings,
        ];
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

        try {
            MultipartMail::send('emails.contributor-rejected', [
                'applicantName'   => $application->name,
                'rejectionReason' => $reason,
                'reapplyUrl'      => route('contributor.apply'),
            ], function ($message) use ($application) {
                $message->to($application->email)
                    ->subject('Update Aplikasi Kontributor Koding Indonesia');
            });
        } catch (\Throwable $e) {
            Log::warning('Contributor reject: notification email failed', [
                'application_id' => $application->id,
                'error'          => $e->getMessage(),
            ]);

            throw new \RuntimeException(
                'Aplikasi sudah ditolak di sistem, tetapi email notifikasi gagal dikirim. '
                . 'Balas manual ke pelamar atau coba lagi nanti. (' . $e->getMessage() . ')'
            );
        }
    }

    public function sendOnboardingEmail(
        ContributorApplication $application,
        string $subject,
        ?string $personalNote = null,
        bool $includeTopicIdeas = true,
    ): void {
        if ($application->status !== 'approved') {
            throw new \InvalidArgumentException('Email onboarding hanya untuk kontributor yang sudah disetujui.');
        }

        $topicIdeas = $includeTopicIdeas
            ? $this->topicIdeasFor($application->topic_expertise)
            : [];

        MultipartMail::send('emails.contributor-onboarding', [
            'applicantName'  => $application->name,
            'firstName'      => $this->firstName($application->name),
            'topicExpertise' => $application->topic_expertise,
            'personalNote'   => $personalNote,
            'topicIdeas'     => $topicIdeas,
            'loginUrl'       => url('/admin/login'),
            'guidelinesUrl'  => route('contributor.apply'),
            'contactUrl'     => route('contact'),
            'senderName'     => config('mail.from.name', 'Tim Koding Indonesia'),
        ], function ($message) use ($application, $subject) {
            $message->to($application->email)
                ->subject($subject);
        });

        $application->update(['onboarding_email_sent_at' => now()]);
    }

    public function defaultOnboardingSubject(): string
    {
        return 'Selamat bergabung sebagai kontributor Koding Indonesia';
    }

    /**
     * @return list<string>
     */
    public function topicIdeasFor(string $expertise): array
    {
        $e = strtolower($expertise);
        $ideas = [];

        if (str_contains($e, 'esp32') || str_contains($e, 'iot') || str_contains($e, 'embedded')) {
            $ideas[] = 'Proyek IoT sederhana: sensor + kirim data ke server';
            $ideas[] = 'Membaca sensor dengan mikrokontroler — tutorial step-by-step';
        }

        if (preg_match('/\b(ai|ml)\b|artificial intelligence|machine learning|deep learning/i', $e)) {
            $ideas[] = 'Pengenalan Machine Learning untuk pemula (tanpa jargon berlebihan)';
            $ideas[] = 'Perbedaan AI, ML, dan Deep Learning — penjelasan sederhana';
        }

        if (str_contains($e, 'frontend') || str_contains($e, 'ui/ux') || str_contains($e, 'ui ux') || str_contains($e, 'design')) {
            $ideas[] = 'Prinsip UI/UX dasar untuk developer';
            $ideas[] = 'Cara membuat layout responsif dengan CSS modern (Flexbox/Grid)';
        }

        if (str_contains($e, 'laravel') || str_contains($e, 'next') || str_contains($e, 'vue') || str_contains($e, 'typescript') || str_contains($e, 'react')) {
            $ideas[] = 'Tutorial step-by-step dengan cuplikan kode yang bisa langsung dicoba';
            $ideas[] = 'Struktur folder proyek web modern yang rapi dan mudah dirawat';
        }

        if (str_contains($e, 'web') || str_contains($e, 'programming')) {
            $ideas[] = 'Tutorial dasar untuk pemula di bidang web development';
            $ideas[] = 'Perbedaan frontend vs backend — penjelasan sederhana';
        }

        if (str_contains($e, 'software engineer') || str_contains($e, 'engineer')) {
            $ideas[] = 'Git workflow dasar untuk tim kecil (branch, commit, pull request)';
            $ideas[] = 'Roadmap belajar software engineering untuk pemula di Indonesia';
        }

        $ideas = array_values(array_unique($ideas));

        if (count($ideas) < 3) {
            $ideas = array_merge($ideas, [
                'Tutorial step-by-step di bidang keahlianmu',
                'Tips praktis untuk pemula di topik yang kamu kuasai',
                'Kesalahan umum yang sering terjadi dan cara mengatasinya',
            ]);
        }

        return array_slice($ideas, 0, 4);
    }

    private function firstName(string $fullName): string
    {
        return str($fullName)->before(' ')->toString() ?: $fullName;
    }
}
