<?php

namespace App\Auth\Notifications;

use Filament\Auth\Notifications\ResetPassword as FilamentResetPassword;
use Illuminate\Notifications\Messages\MailMessage;

class ResetPassword extends FilamentResetPassword
{
    public function toMail($notifiable): MailMessage
    {
        $expireMinutes = (int) config('auth.passwords.'.config('auth.defaults.passwords').'.expire');
        $expireText = $expireMinutes >= 60 && $expireMinutes % 60 === 0
            ? ($expireMinutes / 60).' jam'
            : $expireMinutes.' menit';

        return (new MailMessage)
            ->subject('Buat Password Akun Panel Penulis — Koding Indonesia')
            ->line('Kami mengirim email ini karena akun panel penulis kamu di Koding Indonesia perlu dibuat atau diatur ulang password-nya.')
            ->action('Buat Password', $this->url)
            ->line("Link ini berlaku selama {$expireText}.")
            ->line('Kalau kamu tidak meminta ini, abaikan email ini.');
    }
}
