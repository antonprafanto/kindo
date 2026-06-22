<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContactController extends Controller
{
    public function show()
    {
        return view('contact');
    }

    public function store(Request $request)
    {
        try {
            if ($request->filled('website')) {
                return back()->with('success', 'Pesan kamu sudah terkirim! Kami akan merespons dalam 1–2 hari kerja.');
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

            $contactEmail = config('mail.contact_email', config('mail.from.address'));

            Mail::send('emails.contact', [
                'senderName'     => $validated['name'],
                'senderEmail'    => $validated['email'],
                'contactSubject' => $validated['subject'],
                'messageBody'    => $validated['message'],
            ], function ($message) use ($contactEmail, $validated) {
                $message->to($contactEmail)
                    ->replyTo($validated['email'])
                    ->subject('[Koding Indonesia] ' . $validated['subject']);
            });

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
}
