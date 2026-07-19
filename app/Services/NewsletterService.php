<?php

namespace App\Services;

use App\Models\NewsletterSubscriber;
use App\Support\MultipartMail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class NewsletterService
{
    public function subscribe(string $email, ?string $ip = null): string
    {
        $email = $this->normalizeEmail($email);
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

        MultipartMail::send('emails.newsletter.confirm', [
            'confirmUrl' => $url,
            'email'      => $subscriber->email,
        ], function ($message) use ($subscriber) {
            $message->to($subscriber->email)
                ->subject('Konfirmasi langganan newsletter Koding Indonesia');
        });
    }

    public function sendWelcomeEmail(NewsletterSubscriber $subscriber): void
    {
        $unsubscribeUrl = route('newsletter.unsubscribe', $subscriber->unsubscribe_token);

        MultipartMail::send('emails.newsletter.welcome', [
            'unsubscribeUrl' => $unsubscribeUrl,
        ], function ($message) use ($subscriber, $unsubscribeUrl) {
            $message->to($subscriber->email)
                ->subject('Selamat datang di newsletter Koding Indonesia! 🎉');

            $message->getHeaders()->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>');
            $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
        });
    }

    public function sendNewArticleEmail(NewsletterSubscriber $subscriber, $article): bool
    {
        try {
            $unsubscribeUrl = route('newsletter.unsubscribe', $subscriber->unsubscribe_token);

            MultipartMail::send('emails.newsletter.new-article', [
                'article'        => $article,
                'unsubscribeUrl' => $unsubscribeUrl,
            ], function ($message) use ($subscriber, $article, $unsubscribeUrl) {
                $message->to($subscriber->email)
                    ->subject('Artikel baru: ' . $article->title);

                $message->getHeaders()->addTextHeader('List-Unsubscribe', '<' . $unsubscribeUrl . '>');
                $message->getHeaders()->addTextHeader('List-Unsubscribe-Post', 'List-Unsubscribe=One-Click');
            });

            return true;
        } catch (\Throwable $e) {
            Log::warning('Newsletter send failed', [
                'email'      => $subscriber->email,
                'article_id' => $article->id,
                'error'      => $e->getMessage(),
            ]);

            return false;
        }
    }

    private function normalizeEmail(string $email): string
    {
        $email = strtolower(trim($email));

        if (! str_contains($email, '@')) {
            return $email;
        }

        [$local, $domain] = explode('@', $email, 2);

        if (in_array($domain, ['gmail.com', 'googlemail.com'], true)) {
            $local = str_replace('.', '', $local);
            $domain = 'gmail.com';
        }

        return $local . '@' . $domain;
    }
}
