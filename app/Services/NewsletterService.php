<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterService
{
    public function subscribe(string $email, ?string $ip = null): string
    {
        $email = strtolower(trim($email));
        $existing = NewsletterSubscriber::where('email', $email)->first();

        if ($existing?->isActive()) {
            return 'already_active';
        }

        if ($existing?->status === 'pending') {
            $existing->regenerateConfirmationToken();
            $this->sendConfirmationEmail($existing);

            return 'resent';
        }

        $subscriber = NewsletterSubscriber::updateOrCreate(
            ['email' => $email],
            [
                'status'             => 'pending',
                'confirmation_token' => Str::random(64),
                'unsubscribe_token'  => null,
                'confirmed_at'       => null,
                'unsubscribed_at'    => null,
                'ip_address'         => $ip,
            ]
        );

        $this->sendConfirmationEmail($subscriber);

        return 'pending';
    }

    public function confirm(string $token): ?NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::where('confirmation_token', $token)
            ->where('status', 'pending')
            ->first();

        if (!$subscriber) {
            return null;
        }

        $subscriber->activate();
        $this->sendWelcomeEmail($subscriber);

        return $subscriber;
    }

    public function unsubscribe(string $token): ?NewsletterSubscriber
    {
        $subscriber = NewsletterSubscriber::where('unsubscribe_token', $token)
            ->where('status', 'active')
            ->first();

        if (!$subscriber) {
            return null;
        }

        $subscriber->unsubscribe();

        return $subscriber;
    }

    public function sendConfirmationEmail(NewsletterSubscriber $subscriber): void
    {
        $url = route('newsletter.confirm', $subscriber->confirmation_token);

        Mail::send('emails.newsletter.confirm', [
            'confirmUrl' => $url,
            'email'      => $subscriber->email,
        ], function ($message) use ($subscriber) {
            $message->to($subscriber->email)
                ->subject('Konfirmasi langganan newsletter Koding Indonesia');
        });
    }

    public function sendWelcomeEmail(NewsletterSubscriber $subscriber): void
    {
        Mail::send('emails.newsletter.welcome', [
            'unsubscribeUrl' => route('newsletter.unsubscribe', $subscriber->unsubscribe_token),
        ], function ($message) use ($subscriber) {
            $message->to($subscriber->email)
                ->subject('Selamat datang di newsletter Koding Indonesia! 🎉');
        });
    }

    public function sendNewArticleEmail(NewsletterSubscriber $subscriber, $article): void
    {
        try {
            Mail::send('emails.newsletter.new-article', [
                'article'        => $article,
                'unsubscribeUrl' => route('newsletter.unsubscribe', $subscriber->unsubscribe_token),
            ], function ($message) use ($subscriber, $article) {
                $message->to($subscriber->email)
                    ->subject('Artikel baru: ' . $article->title);
            });
        } catch (\Throwable $e) {
            Log::warning('Newsletter send failed', [
                'email'      => $subscriber->email,
                'article_id' => $article->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }
}
