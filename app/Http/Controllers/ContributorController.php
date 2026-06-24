<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\ContributorApplication;
use App\Models\Tag;
use App\Models\User;
use App\Services\TurnstileService;
use App\Support\EmailNormalizer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;

class ContributorController extends Controller
{
    public function show()
    {
        $categories = Category::orderBy('sort_order')->get();
        $tags = Tag::orderBy('name')->get();

        return view('menjadi-kontributor', compact('categories', 'tags'));
    }

    public function store(Request $request, TurnstileService $turnstile)
    {
        try {
            if ($request->filled('website')) {
                return back()->with('success', 'Aplikasi kamu sudah terkirim! Kami akan meninjau dalam 3–5 hari kerja.');
            }

            if ($turnstile->isConfigured() && ! $turnstile->verify($request->input('cf-turnstile-response'), $request->ip())) {
                return back()->withErrors([
                    'turnstile' => 'Verifikasi keamanan gagal. Silakan centang kotak verifikasi dan coba lagi.',
                ])->withInput();
            }

            $key = 'contributor-apply:' . $request->ip();
            if (RateLimiter::tooManyAttempts($key, 3)) {
                $seconds = RateLimiter::availableIn($key);

                return back()->withErrors([
                    'email' => "Terlalu banyak percobaan. Silakan coba lagi dalam {$seconds} detik.",
                ])->withInput();
            }
            RateLimiter::hit($key, 600);

            $validated = $request->validate([
                'name'            => 'required|string|max:100',
                'email'           => 'required|email|max:200',
                'topic_expertise' => 'required|string|max:200',
                'sample_url'      => 'nullable|url|max:500',
                'motivation'      => 'required|string|min:50|max:2000',
            ]);

            $validated['email'] = EmailNormalizer::normalize($validated['email']);

            $existingUser = User::where('email', $validated['email'])->first();
            if ($existingUser?->isAuthor() || $existingUser?->isAdmin()) {
                return back()->withErrors([
                    'email' => 'Email ini sudah terdaftar sebagai kontributor. Silakan login ke panel admin.',
                ])->withInput();
            }

            if (ContributorApplication::pending()->where('email', $validated['email'])->exists()) {
                return back()->withErrors([
                    'email' => 'Kami sudah menerima aplikasi dari email ini dan sedang meninjaunya. Mohon tunggu kabar dari kami.',
                ])->withInput();
            }

            $application = ContributorApplication::create([
                ...$validated,
                'status'     => 'pending',
                'ip_address' => $request->ip(),
            ]);

            $contactEmail = config('mail.contact_email', config('mail.from.address'));

            Mail::send('emails.contributor-application-admin', [
                'application' => $application,
                'adminUrl'    => url('/admin/contributor-applications'),
            ], function ($message) use ($contactEmail, $application) {
                $message->to($contactEmail)
                    ->replyTo($application->email)
                    ->subject('[Koding Indonesia] Aplikasi Kontributor Baru — ' . $application->name);
            });

            Mail::send('emails.contributor-application-received', [
                'applicantName' => $application->name,
            ], function ($message) use ($application) {
                $message->to($application->email)
                    ->subject('Aplikasi Kontributor Koding Indonesia Diterima');
            });

            return back()->with('success', 'Aplikasi kamu sudah terkirim! Kami akan meninjau dalam 3–5 hari kerja dan menghubungi via email.');
        } catch (\Throwable $e) {
            Log::error('Contributor application failed', [
                'error' => $e->getMessage(),
                'file'  => $e->getFile() . ':' . $e->getLine(),
            ]);

            return back()->withErrors([
                'email' => 'Gagal mengirim aplikasi. Silakan coba lagi beberapa saat.',
            ])->withInput();
        }
    }
}
