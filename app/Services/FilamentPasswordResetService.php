<?php

namespace App\Services;

use Filament\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Support\Facades\Password;

class FilamentPasswordResetService
{
    public function sendResetLink(CanResetPassword $user): void
    {
        $status = Password::broker(Filament::getAuthPasswordBroker())->sendResetLink(
            ['email' => $user->getEmailForPasswordReset()],
            function (CanResetPassword $user, string $token): void {
                if (
                    $user instanceof FilamentUser
                    && ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())
                ) {
                    return;
                }

                $notification = app(ResetPasswordNotification::class, ['token' => $token]);
                $notification->url = Filament::getResetPasswordUrl($token, $user);

                // Kirim sinkron — production pakai QUEUE_CONNECTION=database tanpa worker
                $user->notifyNow($notification);
            },
        );

        if ($status !== Password::RESET_LINK_SENT) {
            throw new \RuntimeException(__($status));
        }
    }
}
