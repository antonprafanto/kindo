<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use Illuminate\Http\Request;
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
        // Honeypot check — bots fill this hidden field, humans leave it empty
        if ($request->filled('website')) {
            return back()->with('success', 'Pesan kamu sudah terkirim! Kami akan merespons dalam 1–2 hari kerja.');
        }

        // Rate limiting: max 3 requests per IP per 10 minutes
        $key = 'contact-form:' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, 3)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.",
            ])->withInput();
        }
        RateLimiter::hit($key, 600); // 10 minutes window

        $validated = $request->validate([
            'name'    => 'required|string|max:100',
            'email'   => 'required|email|max:200',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:20|max:2000',
        ]);

        $contactEmail = config('mail.contact_email', config('mail.from.address'));

        Mail::to($contactEmail)->send(new ContactMail(
            senderName:  $validated['name'],
            senderEmail: $validated['email'],
            subject:     $validated['subject'],
            message:     $validated['message'],
        ));

        return back()->with('success', 'Pesan kamu sudah terkirim! Kami akan merespons dalam 1–2 hari kerja. 🙏');
    }
}
