<?php

namespace App\Filament\Auth\Pages;

use App\Filament\Concerns\VerifiesTurnstile;
use App\Models\User;
use App\Services\FilamentPasswordResetService;
use App\Services\TurnstileService;
use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\View;
use Illuminate\Support\Facades\Password;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    use VerifiesTurnstile;

    public function request(): void
    {
        if (! $this->verifyTurnstile()) {
            return;
        }

        try {
            $this->rateLimit(2);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return;
        }

        $data = $this->form->getState();
        $user = User::where('email', $data['email'])->first();

        if (! $user) {
            $this->getFailureNotification(Password::INVALID_USER)?->send();

            return;
        }

        try {
            app(FilamentPasswordResetService::class)->sendResetLink($user);
        } catch (\RuntimeException $exception) {
            $this->getFailureNotification($exception->getMessage())?->send();

            return;
        }

        $this->getSentNotification(Password::RESET_LINK_SENT)?->send();
        $this->form->fill();
    }

    public function getFormContentComponent(): Component
    {
        $formComponents = [EmbeddedSchema::make('form')];

        if (app(TurnstileService::class)->isConfigured()) {
            $formComponents[] = View::make('filament.components.turnstile');
        }

        return Form::make($formComponents)
            ->id('form')
            ->livewireSubmitHandler('request')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ]);
    }
}
