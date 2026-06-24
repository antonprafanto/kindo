<?php

namespace App\Filament\Auth\Pages;

use App\Filament\Concerns\VerifiesTurnstile;
use App\Services\TurnstileService;
use Filament\Auth\Pages\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\View;

class RequestPasswordReset extends BaseRequestPasswordReset
{
    use VerifiesTurnstile;

    public function request(): void
    {
        if (! $this->verifyTurnstile()) {
            return;
        }

        parent::request();
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
