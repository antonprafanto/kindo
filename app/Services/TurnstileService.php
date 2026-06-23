<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TurnstileService
{
    public function isConfigured(): bool
    {
        return filled(config('services.turnstile.secret_key'))
            && filled(config('services.turnstile.site_key'));
    }

    public function siteKey(): ?string
    {
        $key = config('services.turnstile.site_key');

        return filled($key) ? $key : null;
    }

    public function verify(?string $token, ?string $ip = null): bool
    {
        if (! $this->isConfigured()) {
            return true;
        }

        if (blank($token)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->asForm()->post(
                'https://challenges.cloudflare.com/turnstile/v0/siteverify',
                array_filter([
                    'secret'   => config('services.turnstile.secret_key'),
                    'response' => $token,
                    'remoteip' => $ip,
                ])
            );

            if (! $response->successful()) {
                Log::warning('Turnstile verify HTTP error', ['status' => $response->status()]);

                return false;
            }

            return $response->json('success') === true;
        } catch (\Throwable $e) {
            Log::warning('Turnstile verify failed', ['error' => $e->getMessage()]);

            return false;
        }
    }
}
