<?php

namespace App\Filament\Auth\Pages;

use App\Filament\Concerns\VerifiesTurnstile;
use App\Services\TurnstileService;
use App\Support\EmailNormalizer;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\View;

class Login extends BaseLogin
{
    use VerifiesTurnstile;

    public function authenticate(): ?LoginResponse
    {
        if (! $this->verifyTurnstile()) {
            return null;
        }

        return parent::authenticate();
    }

    protected function getCredentialsFromFormData(array $data): array
    {
        return [
            'email'    => EmailNormalizer::normalize($data['email']),
            'password' => $data['password'],
        ];
    }

    public function getFormContentComponent(): Component
    {
        $formComponents = [EmbeddedSchema::make('form')];

        if (app(TurnstileService::class)->isConfigured()) {
            $formComponents[] = View::make('filament.components.turnstile');
        }

        return Form::make($formComponents)
            ->id('form')
            ->livewireSubmitHandler('authenticate')
            ->footer([
                Actions::make($this->getFormActions())
                    ->alignment($this->getFormActionsAlignment())
                    ->fullWidth($this->hasFullWidthFormActions())
                    ->key('form-actions'),
            ])
            ->visible(fn (): bool => blank($this->userUndertakingMultiFactorAuthentication));
    }
}
