<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use App\Services\TurnstileService;
use App\Support\MultipartMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request, TurnstileService $turnstile)
    {
        try {
            if ($request->filled('hp_fax')) {
                return back();
            }

            if ($turnstile->isConfigured() && ! $turnstile->verify($request->input('cf-turnstile-response'), $request->ip())) {
                return back()->withErrors([
                    'turnstile' => 'Verifikasi keamanan gagal. Silakan centang kotak verifikasi dan coba lagi.',
                ])->withInput();
            }

            $key = 'contact-form:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 3)) {
                $seconds = RateLimiter::availableIn($key);

                return back()->withErrors([
                    'email' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.",
                ])->withInput();
            }
            RateLimiter::hit($key, 600);

            $validated = $request->validate([
                'name'    => 'required|string|max:100',
                'email'   => 'required|email|max:200',
                'subject' => 'required|string|max:200',
                'message' => 'required|string|min:20|max:2000',
            ]);

            $isContributorInquiry = ContactMessage::looksLikeContributorInquiry(
                $validated['subject'],
                $validated['message'],
            );

            $contactMessage = ContactMessage::create([
                ...$validated,
                'status'                  => 'unread',
                'is_contributor_inquiry'  => $isContributorInquiry,
                'ip_address'              => $request->ip(),
            ]);

            $contactEmail = config('mail.contact_email', config('mail.from.address'));

            $panelUrl = URL::temporarySignedRoute(
                'contact.open-panel',
                now()->addDays(14),
                ['contactMessage' => $contactMessage->id],
            );

            try {
                MultipartMail::send('emails.contact', [
                    'senderName'     => $validated['name'],
                    'senderEmail'    => $validated['email'],
                    'contactSubject' => $validated['subject'],
                    'messageBody'    => $validated['message'],
                    'panelUrl'       => $panelUrl,
                ], function ($message) use ($contactEmail, $validated) {
                    $message->to($contactEmail)
                        ->replyTo($validated['email'])
                        ->subject('[Koding Indonesia] ' . $validated['subject']);
                });
            } catch (\Throwable $mailError) {
                Log::warning('Contact form admin email failed', [
                    'contact_message_id' => $contactMessage->id,
                    'error'              => $mailError->getMessage(),
                ]);
            }

            if ($isContributorInquiry) {
                try {
                    MultipartMail::send('emails.contact-contributor-redirect', [
                        'senderName'     => $validated['name'],
                        'contributorUrl' => route('contributor.apply'),
                    ], function ($message) use ($validated) {
                        $message->to($validated['email'])
                            ->subject('Formulir Aplikasi Kontributor — Koding Indonesia');
                    });

                    $contactMessage->update(['auto_reply_sent_at' => now()]);
                } catch (\Throwable $mailError) {
                    Log::warning('Contact contributor auto-reply failed', [
                        'contact_message_id' => $contactMessage->id,
                        'error'              => $mailError->getMessage(),
                    ]);
                }
            }

            return back()->with('success', 'Pesan kamu sudah terkirim! Kami akan merespons dalam 1–2 hari kerja.');
        } catch (\Throwable $e) {
            Log::error('Contact form failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
            ]);

            return back()->withErrors([
                'email' => 'Gagal mengirim pesan. Silakan coba lagi beberapa saat atau hubungi kami langsung via email.',
            ])->withInput();
        }
    }

    public function openInPanel(ContactMessage $contactMessage)
    {
        if ($contactMessage->status === 'unread') {
            $contactMessage->update(['status' => 'read']);
        }

        return redirect('/admin/contact-messages');
    }
}
