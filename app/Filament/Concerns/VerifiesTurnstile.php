<?php

namespace App\Filament\Concerns;

use App\Services\TurnstileService;
use Filament\Notifications\Notification;

trait VerifiesTurnstile
{
    public string $turnstileToken = '';

    protected function verifyTurnstile(): bool
    {
        $turnstile = app(TurnstileService::class);

        if (! $turnstile->isConfigured()) {
            return true;
        }

        if ($turnstile->verify($this->turnstileToken, request()->ip())) {
            return true;
        }

        $this->turnstileToken = '';
        $this->dispatch('reset-turnstile');

        Notification::make()
            ->title('Verifikasi keamanan gagal')
            ->body('Silakan centang kotak verifikasi Cloudflare dan coba lagi.')
            ->danger()
            ->send();

        return false;
    }
}
