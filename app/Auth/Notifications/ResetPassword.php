<?php

namespace App\Auth\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as BaseResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends BaseResetPassword
{
    public string $url;

    protected function resetUrl($notifiable): string
    {
        return $this->url;
    }

    public function toMail($notifiable): MailMessage
    {
        $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        $expireText = $expireMinutes >= 60 && $expireMinutes % 60 === 0
            ? ($expireMinutes / 60).' jam'
            : $expireMinutes.' menit';

        $userName = method_exists($notifiable, 'getAttribute')
            ? ($notifiable->name ?? null)
            : null;

        return (new MailMessage)
            ->subject('Buat Password Akun Panel Penulis — Koding Indonesia')
            ->view('emails.reset-password', [
                'url' => $this->url,
                'expireText' => $expireText,
                'userName' => $userName,
            ]);
    }
}
